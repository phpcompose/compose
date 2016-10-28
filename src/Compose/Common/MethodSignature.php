<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2016-10-27
 * Time: 11:16 PM
 */

namespace Compose\Common;


class MethodSignature
{
    protected
        /**
         * @var string
         */
        $name,

        /**
         * @var array
         */
        $params,

        /**
         * @var
         */
        $target;

    /**
     * MethodSignature constructor.
     * @param $name
     * @param $params
     * @param $target
     */
    public function __construct(string $name, array $params = [], $target = null)
    {
        $this->name = $name;
        $this->params = $params;
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
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return array
     */
    public function getParams() : array
    {
        return $this->params;
    }

    /**
     * @param array $params
     */
    public function setParams(array $params)
    {
        $this->params = $params;
    }

    /**
     * @return mixed
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @param mixed $target
     */
    public function setTarget($target)
    {
        $this->target = $target;
    }
}