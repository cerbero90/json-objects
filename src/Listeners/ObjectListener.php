<?php

namespace Cerbero\JsonObjects\Listeners;

/**
 * The object listener.
 *
 */
class ObjectListener extends AbstractListener
{
    /**
     * The callback processing the object.
     *
     * @var callable
     */
    protected $callback;

    /**
     * Set the dependencies.
     *
     * @param callable $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * Process the given extracted object.
     *
     * @param array $object
     * @return void
     */
    protected function processExtractedObject(array $object) : void
    {
        call_user_func($this->callback, $object);
    }
}
