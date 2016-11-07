<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2016-11-04
 * Time: 11:26 PM
 */

namespace Compose\Bridge\Symfony;



use Compose\System\Event\EventInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class Event extends \Symfony\Component\EventDispatcher\GenericEvent  implements EventInterface
{
    protected
        /**
         * @var string
         */
        $name;

    /**
     * Event constructor.
     * @param string $name
     * @param array $target
     * @param array $args
     */
    public function __construct(string $name, $target, array $args = [])
    {
        parent::__construct($target, $args);
        $this->name = $name;

    }

    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getTarget()
    {
        return $this->getSubject();
    }

    /**
     * @note redeclared with return type
     * @return array
     */
    public function getArguments() : array
    {
        return parent::getArguments();
    }
}