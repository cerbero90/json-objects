<?php

declare(strict_types=1);

namespace Cerbero\JsonObjects;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\MessageInterface;
use Mockery as m;
use Psr\Http\Message\StreamInterface;

class DataStreamingTest extends TestCase
{
    /**
     * The data streaming instance.
     *
     * @var DataStreaming
     */
    protected $dataStreaming;

    /**
     * Set the tests up
     *
     * @return void
     */
    public function setUp() : void
    {
        $this->dataStreaming = new DataStreaming;
    }

    /**
     * @test
     */
    public function canStreamDataDependingOnType()
    {
        $resource = $this->dataStreaming->streamData(STDIN);

        $this->assertIsResource($resource);
    }

    /**
     * @test
     */
    public function cannotStreamInvalidData()
    {
        $this->expectException(JsonObjectsException::class);
        $this->expectExceptionMessage('Unable to create a stream from the given data.');

        $this->dataStreaming->streamData(100);
    }

    /**
     * @test
     */
    public function canStreamResources()
    {
        $resource = $this->dataStreaming->streamResource(STDIN);

        $this->assertIsResource($resource);
    }

    /**
     * @test
     */
    public function cannotStreamInvalidResources()
    {
        $this->expectException(JsonObjectsException::class);
        $this->expectExceptionMessage('Unable to create a stream from an invalid resource.');

        $this->dataStreaming->streamResource(10);
    }

    /**
     * @test
     */
    public function canStreamFile()
    {
        $resource = $this->dataStreaming->streamString(__DIR__ . '/array_of_objects.json');

        $this->assertIsResource($resource);
    }

    /**
     * @test
     */
    public function canStreamEndpoint()
    {
        $resource = $this->dataStreaming->streamString('https://httpbin.org/get');

        $this->assertIsResource($resource);
    }

    /**
     * @test
     */
    public function cannotStreamInvalidString()
    {
        $this->expectException(JsonObjectsException::class);
        $this->expectExceptionMessage('Failed to open stream from: /inaccessible');

        $this->dataStreaming->streamString('/inaccessible');
    }

    /**
     * @test
     */
    public function canStreamMessage()
    {
        $double = m::mock(MessageInterface::class)
            ->shouldReceive('getBody')
            ->once()
            ->andReturnUsing(function () {
                return m::mock(StreamInterface::class)
                    ->shouldReceive('isReadable', 'isWritable')
                    ->once()
                    ->andReturn(true)
                    ->getMock();
            })
            ->getMock();

        try {
            $this->dataStreaming->streamObject($double);
        } catch (\Exception $e) {
            // Test only the mock assertions
            $this->assertInstanceOf(JsonObjectsException::class, $e);
        }
    }

    /**
     * @test
     */
    public function canStreamWrapper()
    {
        $double = m::mock(StreamInterface::class)
            ->shouldReceive('isReadable', 'isWritable')
            ->once()
            ->andReturn(true)
            ->getMock();

        try {
            $this->dataStreaming->streamObject($double);
        } catch (\Exception $e) {
            // Test only the mock assertions
            $this->assertInstanceOf(JsonObjectsException::class, $e);
        }
    }

    /**
     * @test
     */
    public function cannotStreamInvalidWrapper()
    {
        $this->expectException(JsonObjectsException::class);
        $this->expectExceptionMessage('Failed to open stream from the given stream wrapper.');

        $double = m::mock(StreamInterface::class)
            ->shouldReceive('isReadable', 'isWritable')
            ->once()
            ->andReturn(true)
            ->getMock();

        $this->dataStreaming->streamObject($double);
    }

    /**
     * @test
     */
    public function cannotStreamInvalidObject()
    {
        $this->expectException(JsonObjectsException::class);
        $this->expectExceptionMessage('Unable to create a stream from stdClass');

        $this->dataStreaming->streamObject(new \stdClass);
    }

    /**
     * @test
     */
    public function cannotStreamWrapperUnlessReadableOrWritable()
    {
        $this->expectException(JsonObjectsException::class);
        $this->expectExceptionMessage('The stream is not readable or writable.');

        $double = m::mock(StreamInterface::class)
            ->shouldReceive('isReadable', 'isWritable')
            ->once()
            ->andReturn(false)
            ->getMock();

        $this->dataStreaming->streamObject($double);
    }
}
