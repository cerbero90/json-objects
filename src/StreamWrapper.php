<?php

namespace Cerbero\JsonObjects;

use Psr\Http\Message\StreamInterface;

/**
 * The stream wrapper.
 *
 * @phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */
class StreamWrapper
{
    /**
     * The name of the stream wrapper.
     *
     * @var string
     */
    public const NAME = 'cerbero-json-objects';

    /**
     * The stream context.
     *
     * @var resource
     */
    public $context;

    /**
     * The stream.
     *
     * @var \Psr\Http\Message\StreamInterface
     */
    protected $stream;

    /**
     * Open the stream
     *
     * @param string $path
     * @param string $mode
     * @param int $options
     * @param mixed $opened_path
     * @return bool
     */
    public function stream_open(string $path, string $mode, int $options, &$opened_path): bool
    {
        $options = stream_context_get_options($this->context);
        $stream = $options[static::NAME]['stream'] ?? null;

        if (!$stream instanceof StreamInterface || !$stream->isReadable()) {
            return false;
        }

        $this->stream = $stream;

        return true;
    }

    /**
     * Determine whether the pointer is at the end of the stream
     *
     * @return bool
     */
    public function stream_eof(): bool
    {
        return $this->stream->eof();
    }

    /**
     * Read from the stream
     *
     * @param int $count
     * @return string
     */
    public function stream_read(int $count): string
    {
        return $this->stream->read($count);
    }
}
