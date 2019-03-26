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
        $this->setStreamFromSource($source);

        $this->key = $key;
    }

    /**
     * Set the JSON stream from the given source
     *
     * @param mixed $source
     * @return void
     *
     * @throws JsonObjectsException
     */
    protected function setStreamFromSource($source) : void
    {
        if (is_resource($source)) {
            $this->stream = $source;
            return;
        }

        if (!is_string($source)) {
            throw new JsonObjectsException('Unable to create a stream from the given source.');
        }

        $this->stream = extension_loaded('zlib') ? @gzopen($source, 'rb') : @fopen($source, 'rb');

        if ($this->stream === false) {
            throw new JsonObjectsException("Failed to open stream from: {$source}");
        }
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
