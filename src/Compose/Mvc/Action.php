<?php
/**
 * Created by PhpStorm.
 * User: Alamin
 * Date: 2016-10-16
 * Time: 9:22 PM
 */

namespace Compose\Mvc;

use Compose\System\Http\Command;
use Compose\System\Invocation;
use Compose\System\Container\ContainerAwareInterface;
use Compose\System\Container\ContainerAwareTrait;
use Compose\System\Container\ServiceAwareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class Action
 *
 * Action handler for MVC application
 * Supports Restful SCRUD Actions:
 * @package Compose\Mvc
 */
abstract class Action extends Command implements ServiceAwareInterface , ContainerAwareInterface
{
    use ActionHandlerResolverTrait, ResponseHelperTrait, ContainerAwareTrait;

    protected
        /**
         * Action prefix
         *
         * @var string
         */
        $actionNamePrefix = 'on',

        /**
         * @var string
         */
        $actionNameSuffix = '',

        /**
         * Default action name.
         *
         * This name is usually used for index method (GET method + no param passed)
         * @var string
         */
        $defaultAction = 'index';


    /**
     * @param ServerRequestInterface $request
     * @return mixed
     */
    protected function onExecute(ServerRequestInterface $request) : ResponseInterface
    {
        /** @var Invocation $invocation */
        $invocation = $this->resolveActionHandler($request);
        return $invocation();
    }

    /**
     * Implementing the abstract action resolve method
     *
     * @param string $httpMethod
     * @param array $httpParams
     * @return string
     */
    protected function resolveActionName(string $httpMethod, array &$httpParams = []) : string
    {
        // handle default case, ie. path/ (GET request + no param passed)
        if(!count($httpParams) && $httpMethod == 'get') {
            $action = $this->defaultAction;
        } else {
            $action = $httpMethod;
        }

        return $this->buildActionName($action);
    }


    /**
     * Adds the prefix + suffix to given names and camelcase
     *
     * @param $names
     * @return string
     */
    protected function buildActionName(...$names) : string
    {
        array_unshift($names, $this->actionNamePrefix);
        array_push($names, $this->actionNameSuffix);

        // camelCase array values
        return lcfirst(implode('', array_map('ucfirst', $names)));
    }
}
