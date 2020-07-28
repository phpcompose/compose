<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2018-12-19
 * Time: 08:59
 */

namespace Compose\Event;


class ExceptionMessage extends Message
{
    /**
     * @var \Exception
     */
    protected $e;

    public function __construct(\Exception $e)
    {
        parent::__construct(get_class($e), ['exception' => $e]);
        $this->e = $e;
    }

    /**
     * @return \Exception
     */
    public function getException() : \Exception
    {
        return $this->e;
    }
}