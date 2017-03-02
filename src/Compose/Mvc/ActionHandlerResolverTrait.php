<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 */

namespace Compose\Mvc;

use Psr\Http\Message\ServerRequestInterface;
use Compose\System\Invocation;

/**
 * Class ActionHandlerResolverTrait
 *
 * Helper trait to resolve Invocation for the requested action
 * @package Compose\Mvc
 */
trait ActionHandlerResolverTrait
{
    protected
        /**
         * Mapping HTTP method to internal method for action routing
         *
         * @var array
         */
        $httpMethodMapper = [
            'index' => 'index',
            'get'   => 'get',
            'post'  => 'post',
            'put'   => 'put',
            'patch' => 'patch',
            'delete'=> 'delete'
        ];

    /**
     * Resolving action handler
     *
     * @param ServerRequestInterface $request
     * @return Invocation|null
     */
    protected function resolveActionHandler(ServerRequestInterface $request) : Invocation
    {
        $method = strtolower($request->getMethod());
        $params = $this->extractRequestParams($request);

        // map http method
        if(isset($this->httpMethodMapper[$method])) {
            $method = $this->httpMethodMapper[$method];
        }

        $action = $this->resolveActionName($method, $params);

        $invocation = new Invocation(
            $this,
            $action
        );

        if($invocation->getArgumentTypeAtIndex(0) == ServerRequestInterface::class) {
            array_unshift($params, $request); // add the request as the first param
        }

        $invocation->setParameters($params);
        return $invocation;
    }

    /**
     * Resolve action name for given HTTP method and passed params
     *
     * @param string $httpMethod
     * @param array $httpParams
     * @return string
     */
    abstract protected function resolveActionName(string $httpMethod, array &$httpParams = []) : string;

    /**
     * @param ServerRequestInterface $request
     * @return array
     */
    protected function extractRequestParams(ServerRequestInterface $request) : array
    {
        $path = $request->getUri()->getPath();

        if(!empty($path)) {
            $params = array_values(array_filter(explode('/', $path)));
        } else {
            $params = [];
        }

        return $params;
    }
}