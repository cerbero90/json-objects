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
    public function setUp(): void
    {
        $this->dataStreaming = new DataStreaming();
    }

    /**
     * Tear the tests down
     *
     * @return void
     */
    public function tearDown(): void
    {
        m::close();
    }

    /**
     * @test
     */
    public function canStreamDataDependingOnType()
    {
        $resource = $this->dataStreaming->streamData(STDIN);

        $this->assertTrue(is_resource($resource));
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

        $this->assertTrue(is_resource($resource));
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

        $this->assertTrue(is_resource($resource));
    }

    /**
     * @test
     */
    public function canStreamEndpoint()
    {
        $resource = $this->dataStreaming->streamString('https://httpbin.org/get');

        $this->assertTrue(is_resource($resource));
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
    public function cannotStreamInvalidObject()
    {
        $this->expectException(JsonObjectsException::class);
        $this->expectExceptionMessage('Unable to stream content from object while providing integer');

        $this->dataStreaming->streamObject(11);
    }

    /**
     * @test
     */
    public function canStreamMessage()
    {
        $double = m::mock(MessageInterface::class, [
            'getBody' => m::mock(StreamInterface::class, [
                'isReadable' => true,
            ]),
        ]);

        $resource = $this->dataStreaming->streamObject($double);

        $this->assertTrue(is_resource($resource));
    }

    /**
     * @test
     */
    public function canStreamWrapper()
    {
        $double = m::mock(StreamInterface::class, [
            'isReadable' => true,
        ]);

        $resource = $this->dataStreaming->streamObject($double);

        $this->assertTrue(is_resource($resource));
    }

    /**
     * @test
     */
    public function cannotStreamUnreadableWrapper()
    {
        $this->expectException(JsonObjectsException::class);

        $method = method_exists($this, 'expectExceptionMessageRegExp')
            ? 'expectExceptionMessageRegExp'
            : 'expectExceptionMessageMatches';

        $this->$method('/Failed to open stream from .*StreamInterface/');

        $double = m::mock(StreamInterface::class, [
            'isReadable' => false,
        ]);

        $this->dataStreaming->streamObject($double);
    }

    /**
     * @test
     */
    public function cannotStreamUnknownObject()
    {
        $this->expectException(JsonObjectsException::class);
        $this->expectExceptionMessage('Unable to create a stream from stdClass');

        $this->dataStreaming->streamObject(new \stdClass());
    }
}
