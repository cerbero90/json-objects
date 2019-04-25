<?php

namespace Cerbero\JsonObjects;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

/**
 * The data streaming.
 *
 */
class DataStreaming
{
    /**
     * Stream content from the given data.
     *
     * @param mixed $data
     * @return resource
     *
     * @throws JsonObjectsException
     */
    public function streamData($data)
    {
        $method = 'stream' . ucfirst(gettype($data));

        if (method_exists($this, $method)) {
            return $this->{$method}($data);
        }

        throw new JsonObjectsException('Unable to create a stream from the given data.');
    }

    /**
     * Stream content from the given resource
     *
     * @param mixed $resource
     * @return resource
     *
     * @throws JsonObjectsException
     */
    public function streamResource($resource)
    {
        if (is_resource($resource)) {
            return $resource;
        }

        throw new JsonObjectsException('Unable to create a stream from an invalid resource.');
    }

    /**
     * Stream content from the given string
     *
     * @param string $string
     * @return resource
     *
     * @throws JsonObjectsException
     */
    public function streamString(string $string)
    {
        $stream = extension_loaded('zlib') ? @gzopen($string, 'rb') : @fopen($string, 'rb');

        if ($stream === false) {
            throw new JsonObjectsException("Failed to open stream from: {$string}");
        }

        return $stream;
    }

    /**
     * Stream content from the given object
     *
     * @param object $object
     * @return resource
     *
     * @throws JsonObjectsException
     */
    public function streamObject(object $object)
    {
        if ($object instanceof MessageInterface) {
            $object = $object->getBody();
        }

        if ($object instanceof StreamInterface) {
            return $this->streamWrapper($object);
        }

        throw new JsonObjectsException('Unable to create a stream from ' . get_class($object));
    }

    /**
     * Stream content from the given stream wrapper
     *
     * @param \Psr\Http\Message\StreamInterface $stream
     * @return resource
     *
     * @throws JsonObjectsException
     */
    public function streamWrapper(StreamInterface $stream)
    {
        // Register this class as stream wrapper if not already registered
        if (!in_array('cerbero-json-objects', stream_get_wrappers())) {
            stream_wrapper_register('cerbero-json-objects', static::class);
        }

        $mode = $this->getModeByStream($stream);

        // Retrieve a handler of the opened stream
        $resource = @fopen('cerbero-json-objects://stream', $mode, false, stream_context_create([
            'cerbero-json-objects' => compact('stream'),
        ]));

        if ($resource === false) {
            throw new JsonObjectsException('Failed to open stream from the given stream wrapper.');
        }

        return $resource;
    }

    /**
     * Retrieve the mode to open the given stream
     *
     * @param \Psr\Http\Message\StreamInterface $stream
     * @return string
     *
     * @throws JsonObjectsException
     */
    private function getModeByStream(StreamInterface $stream) : string
    {
        if ($stream->isReadable()) {
            return $stream->isWritable() ? 'r+b' : 'rb';
        }

        if ($stream->isWritable()) {
            return 'wb';
        }

        throw new JsonObjectsException('The stream is not readable or writable.');
    }
}
