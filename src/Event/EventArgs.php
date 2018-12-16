<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2017-12-04
 * Time: 2:05 PM
 */

namespace Compose\Event;

/**
 * Class EventArgs
 * @package Compose\Event
 */
class EventArgs extends \ArrayObject
{
    protected
        $name,
        $sender;

    /**
     * EventArgs constructor.
     * @param string $name
     * @param array|null $args
     * @param null $sender
     */
    public function __construct(string $name, array $args = [], $sender = null)
    {
        parent::__construct($args);
        $this->name = $name;
        $this->sender = $sender;
    }

    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * @return array
     */
    public function getArguments() : array
    {
        return $this->getArrayCopy();
    }
}