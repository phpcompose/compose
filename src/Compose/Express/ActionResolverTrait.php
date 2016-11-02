<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2016-10-30
 * Time: 5:53 PM
 */

namespace Compose\Express;

use Psr\Http\Message\ServerRequestInterface;
use Compose\Common\Invocation;
use Psr\Http\Message\UriInterface;
use Zend\Expressive\Router\RouteResult;

trait ActionResolverTrait
{
    protected
        /**
         * @var string
         */
        $defaultAction = 'index',

        /**
         * @var string
         */
        $actionPrefix = 'execute',

        /**
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
     * @param ServerRequestInterface $request
     * @return Invocation|null
     */
    protected function resolveActionHandler(ServerRequestInterface $request) : Invocation
    {
        $method = strtolower($request->getMethod());
        $params = $this->extractRequestParams($request);

        // handle special case
        if(!count($params) && $method == 'get') {
            $method = $this->defaultAction;
        }

        // map http method
        if(isset($this->httpMethodMapper[$method])) {
            $method = $this->httpMethodMapper[$method];
        }

        $action = $this->resolveActionName($method, $params);

        array_unshift($params, $request);
        return new Invocation(
            $action,
            $params,
            $this
        );
    }

    /**
     * @param string $httpMethod
     * @param array $httpParams
     * @return string
     */
    protected function resolveActionName(string $httpMethod, array &$httpParams = []) : string
    {
        return $this->actionPrefix .  ucfirst($httpMethod);
    }


    /**
     * @param ServerRequestInterface $request
     * @return array
     */
    protected function extractRequestParams(ServerRequestInterface $request) : array
    {
        /** @var UriInterface $uri */
        $uri = $request->getUri();
        $path = $uri->getPath();

        if(!empty($path)) {
            $params = explode('/', reset($path)); // get the first entry and
        } else {
            $params = [];
        }

        return $params;
    }
}