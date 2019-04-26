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
        // Register the stream wrapper if not already registered
        if (!in_array(StreamWrapper::NAME, stream_get_wrappers())) {
            stream_wrapper_register(StreamWrapper::NAME, StreamWrapper::class);
        }

        // Retrieve a handler of the opened stream
        $resource = @fopen(StreamWrapper::NAME . '://stream', 'rb', false, stream_context_create([
            StreamWrapper::NAME => compact('stream'),
        ]));

        if ($resource === false) {
            throw new JsonObjectsException('Failed to open stream from ' . get_class($stream));
        }

        return $resource;
    }
}
