<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2016-10-25
 * Time: 7:03 PM
 */

namespace Compose\Support;


use Interop\Container\ContainerInterface;

/**
 * Class ArrayContainer
 * @package Compose\Support
 */
class ArrayContainer extends \ArrayObject  implements ContainerInterface
{
    protected
        /**
         * @var array
         */
        $data = [];

    /**
     * ArrayContainer constructor.
     * @param array|null $data
     */
    public function __construct(array $data = null)
    {
        parent::__construct($data);
    }

    /**
     * @inheritdoc
     * @param string $id
     * @return bool
     */
    public function has($id) : bool
    {
        return isset($this[$id]);
    }

    /**
     * @inheritdoc
     * @param string $id
     * @return mixed
     */
    public function get($id)
    {
        return $this[$id];
    }
}