<?php

namespace Cerbero\JsonObjects\Listeners;

/**
 * The chunk listener.
 *
 */
class ChunkListener extends AbstractListener
{
    /**
     * The size of the chunk.
     *
     * @var int
     */
    protected $size;

    /**
     * The callback processing the chunk.
     *
     * @var callable
     */
    protected $callback;

    /**
     * The chunk of objects.
     *
     * @var array
     */
    protected $chunk = [];

    /**
     * Set the dependencies.
     *
     * @param int $size
     * @param callable $callback
     */
    public function __construct(int $size, callable $callback)
    {
        $this->size = $size;
        $this->callback = $callback;
    }

    /**
     * Listen to the end of the object.
     *
     * @return void
     */
    public function endObject() : void
    {
        parent::endObject();

        if (count($this->chunk) === $this->size) {
            call_user_func($this->callback, $this->chunk);
            $this->chunk = [];
        }
    }

    /**
     * Process the given extracted object.
     *
     * @param array $object
     * @return void
     */
    protected function processExtractedObject(array $object) : void
    {
        $this->chunk[] = $object;
    }

    /**
     * Listen to the end of the document.
     *
     * @return void
     */
    public function endDocument() : void
    {
        if (!empty($this->chunk)) {
            call_user_func($this->callback, $this->chunk);
            $this->chunk = [];
        }

        parent::endDocument();
    }
}
