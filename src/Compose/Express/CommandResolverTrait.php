<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2016-11-13
 * Time: 9:43 AM
 */

namespace Compose\Express;


use Compose\Core\Http\CommandInterface;
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
        if(is_string($mixed)) {
            if($container->has($mixed)) {
                $command = $container->get($mixed);
            }
        } else {
            $command = $mixed;
        }

        if(!$command) {
            throw new \Exception("Unable to resolve Command.");
        }

        if(!$command instanceof CommandInterface) {
            throw new \Exception("Command must be instance of CommandInstance.");
        }

        return $command;
    }
}
