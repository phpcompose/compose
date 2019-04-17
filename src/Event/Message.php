<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2018-12-18
 * Time: 11:25
 */

namespace Compose\Event;


use Psr\EventDispatcher\MessageInterface;

class Message extends \ArrayObject implements EventInterface
{
    protected
        $target,
        /**
         * @var string
         */
        $name;

    public function __construct(string $name, array $args = [], $target = null)
    {
        parent::__construct($args);
        $this->name = $name;
        $this->target = $target;
    }

    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @return object
     */
    public function getTarget()
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