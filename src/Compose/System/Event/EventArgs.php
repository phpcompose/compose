<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2016-11-04
 * Time: 8:57 PM
 */

namespace Compose\System\Event;


class EventArgs implements EventArgsInterface
{
    protected
        /**
         * @var object|null
         */
        $subject,

        /**
         * @var array
         */
        $arguments;

    public function __construct($subject = null, array $arguments = [])
    {
        $this->subject = $subject;
        $this->arguments = $arguments;
    }

    /**
     * @return null
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @return array
     */
    public function getArguments() : array
    {
        return $this->arguments;
    }
}