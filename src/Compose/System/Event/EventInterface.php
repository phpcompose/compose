<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2016-11-02
 * Time: 9:28 PM
 */

namespace Compose\System\Event;


interface EventInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return mixed
     */
    public function getTarget();

    /**
     * @return array
     */
    public function getParameters(): array;

    /**
     * @return mixed
     */
    public function stopPropagation();

    /**
     * @return mixed
     */
    public function isPropagationStopped();
}