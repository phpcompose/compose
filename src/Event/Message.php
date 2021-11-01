<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2018-12-18
 * Time: 11:25
 */

namespace Compose\Event;


use ArrayObject;

class Message extends ArrayObject implements MessageInterface
{
    protected ?object $target;

    /**
     * Message constructor.
     * @param array $args
     * @param null $target
     */
    public function __construct(array $args = [], $target = null)
    {
        parent::__construct($args);
        $this->target = $target;
    }

    /**
     * @return object|null
     */
    public function getTarget() : ?object
    {
        return $this->target;
    }

    /**
     * @return array
     */
    public function getArguments() : array
    {
        return $this->getArrayCopy();
    }
}