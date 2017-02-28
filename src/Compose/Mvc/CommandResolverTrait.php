<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2016-11-13
 * Time: 9:43 AM
 */

namespace Compose\Mvc;


use Compose\System\Http\CallableCommand;
use Compose\System\Http\CommandInterface;
use Interop\Container\ContainerInterface;

trait CommandResolverTrait
{
    /**
     * @param $mixed
     * @param ContainerInterface $container
     * @return CommandInterface
     * @throws \Exception
     */
    protected function resolveCommand($mixed, ContainerInterface $container) : CommandInterface
    {
        $command = null;
        if(is_string($mixed)) { // for string, we will assume class name and will try to resolve by container
            if($container->has($mixed)) {
                $command = $container->get($mixed);
            }
        } else if(is_callable($mixed)) {
            $command = new CallableCommand($mixed);
        } else {
            $command = $mixed;
        }

        if(!$command) {
            throw new \Exception(sprintf("Unable to resolve Command %s",
                is_object($mixed) ? get_class($mixed) : $mixed));
        }

        if(!$command instanceof CommandInterface) {
            throw new \Exception("Command must be instance of CommandInstance.");
        }

        return $command;
    }
}
