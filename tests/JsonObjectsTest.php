<?php

declare(strict_types=1);

namespace Cerbero\JsonObjects;

use PHPUnit\Framework\TestCase;

class JsonObjectsTest extends TestCase
{
    /**
     * @test
     */
    public function cannotInitialiseIfSourceIsInvalid()
    {
        $this->expectException(JsonObjectsException::class);
        $this->expectExceptionMessage('Unable to create a stream from the given data.');

        new JsonObjects(100);
    }

    /**
     * @test
     */
    public function canBeInitialisedStatically()
    {
        $instance = JsonObjects::from(STDIN);

        $this->assertInstanceOf(JsonObjects::class, $instance);
    }

    /**
     * @test
     */
    public function throwsInternalExceptionIfParsingFails()
    {
        $this->expectException(JsonObjectsException::class);
        $this->expectExceptionMessage('Parsing error in [1:1]. Document must start with object or array.');

        JsonObjects::from(__DIR__ . '/invalid')->each(function () {});
    }

    /**
     * @test
     */
    public function canProcessOneItemAtATime()
    {
        $expected = ['number' => 0];
        $source = __DIR__ . '/array_of_objects.json';
        $key = 'some.nested.values.columns.*.rows.*.items.*';

        $this->assertOneItemIsProcessed($expected, $source, $key);
    }

    /**
     * Assert that the given item is processed
     *
     * @param array $expected
     * @param string $source
     * @param string|null $key
     * @return void
     */
    protected function assertOneItemIsProcessed(array $expected, string $source, string $key = null) : void
    {
        $item = null;

        try {
            JsonObjects::from($source, $key)->each(function ($object) use (&$item) {
                $item = $object;
                // Prevent the next item to be extracted as we want to test the extraction only once
                throw new JsonObjectsException;
            });
        } catch (JsonObjectsException $e) {
            $this->assertSame($expected, $item);
            // Once we assert that the item has been extracted, we can conclude the test
            return;
        }

        // This will fail if we could not process one item at a time
        $this->assertNotNull($item);
    }

    /**
     * @test
     */
    public function canProcessOneItemAtATimeFromAFlatJson()
    {
        $expected = ['letter' => 'a'];
        $source = __DIR__ . '/flat_array_of_objects.json';

        $this->assertOneItemIsProcessed($expected, $source);
    }

    /**
     * @test
     */
    public function canProcessOneItemAtATimeFromAJsonContainingOnlyOneObject()
    {
        $expected = ['value' => 0];
        $source = __DIR__ . '/object.json';
        $key = 'nested.*.property';

        $this->assertOneItemIsProcessed($expected, $source, $key);
    }

    /**
     * @test
     */
    public function canProcessManyItemsAtATime()
    {
        $source = __DIR__ . '/array_of_objects.json';
        $key = 'some.nested.values.columns.*.rows.*.items.*';
        $expected = [
            ['number' => 0],
            ['number' => 1],
            ['number' => 2],
        ];

        $this->assertManyItemsAreProcessed($expected, $source, $key);
    }

    /**
     * Assert that the given items are processed
     *
     * @param array $expected
     * @param string $source
     * @param string|null $key
     * @return void
     */
    protected function assertManyItemsAreProcessed(array $expected, string $source, string $key = null) : void
    {
        $items = null;

        try {
            JsonObjects::from($source, $key)->chunk(3, function ($objects) use (&$items) {
                $items = $objects;
                // Prevent the next chunk to be extracted as we want to test the extraction only once
                throw new JsonObjectsException;
            });
        } catch (JsonObjectsException $e) {
            $this->assertSame($expected, $items);
            // Once we assert that the items have been extracted, we can conclude the test
            return;
        }

        // This will fail if we could not process many items at a time
        $this->assertNotNull($items);
    }

    /**
     * @test
     */
    public function canProcessManyItemsAtATimeFromAFlatJson()
    {
        $source = __DIR__ . '/flat_array_of_objects.json';
        $expected = [
            ['letter' => 'a'],
            ['letter' => 'b'],
            ['letter' => 'c'],
        ];

        $this->assertManyItemsAreProcessed($expected, $source);
    }

    /**
     * @test
     */
    public function canProcessManyItemsAtATimeFromAJsonContainingOnlyOneObject()
    {
        $source = __DIR__ . '/object.json';
        $key = 'nested.*.property';
        $expected = [
            ['value' => 0],
            ['value' => 1],
            ['value' => 2],
        ];

        $this->assertManyItemsAreProcessed($expected, $source, $key);
    }

    /**
     * @test
     */
    public function canProcessRemainingItems()
    {
        $source = __DIR__ . '/array_of_objects.json';
        $key = 'some.nested.values.columns.*.rows.*.items.*';
        $expected = [
            ['number' => 0],
            ['number' => 1],
            ['number' => 2],
            ['number' => 3],
            ['number' => 4],
            ['number' => 5],
            ['number' => 6],
            ['number' => 7],
            ['number' => 8],
            ['number' => 9],
        ];

        $items = null;

        JsonObjects::from($source, $key)->chunk(4, function ($objects) use (&$items) {
            static $result = [];
            $result = array_merge($result, $objects);
            $items = $result;
        });

        // This will fail if we could not process many items at a time
        $this->assertSame($expected, $items);
    }
}
