<?php

namespace Cerbero\JsonObjects;

use Exception;
use JsonStreamingParser\Parser;
use Cerbero\JsonObjects\Listeners\AbstractListener;
use Cerbero\JsonObjects\Listeners\ChunkListener;
use Cerbero\JsonObjects\Listeners\ObjectListener;

/**
 * The JSON objects main class.
 *
 */
class JsonObjects
{
    /**
     * The JSON stream.
     *
     * @var resource
     */
    protected $stream;

    /**
     * The key containing the JSON objects.
     *
     * @var string|null
     */
    protected $key;

    /**
     * Set the dependencies.
     *
     * @param resource|string $source
     * @param string|null $key
     *
     * @throws JsonObjectsException
     */
    public function __construct($source, string $key = null)
    {
        $this->stream = (new DataStreaming)->streamData($source);
        $this->key = $key;
    }

    /**
     * Create a new instance while easing method chaining
     *
     * @param resource|string $source
     * @param string|null $key
     * @return self
     *
     * @throws JsonObjectsException
     */
    public static function from($source, string $key = null) : self
    {
        return new static($source, $key);
    }

    /**
     * Process each JSON object separately
     *
     * @param callable $callback
     * @return void
     *
     * @throws JsonObjectsException
     */
    public function each(callable $callback) : void
    {
        $this->parseStreamWithListener(new ObjectListener($callback));
    }

    /**
     * Parse the JSON stream with the given listener
     *
     * @param AbstractListener $listener
     * @return void
     *
     * @throws JsonObjectsException
     */
    protected function parseStreamWithListener(AbstractListener $listener) : void
    {
        if ($this->key !== null) {
            $listener->setTargetFromKey($this->key);
        }

        try {
            (new Parser($this->stream, $listener))->parse();
        } catch (Exception $e) {
            throw new JsonObjectsException($e->getMessage());
        } finally {
            extension_loaded('zlib') ? gzclose($this->stream) : fclose($this->stream);
        }
    }

    /**
     * Process JSON objects in chunks
     *
     * @param int $size
     * @param callable $callback
     * @return void
     *
     * @throws JsonObjectsException
     */
    public function chunk(int $size, callable $callback) : void
    {
        $this->parseStreamWithListener(new ChunkListener($size, $callback));
    }
}
