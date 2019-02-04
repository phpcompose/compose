<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2016-10-25
 * Time: 9:55 PM
 */

namespace Compose\Mvc;
use Compose\Http\HttpException;
use Compose\Http\RequestHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Compose\Container\ContainerAwareInterface;
use Compose\Support\Invocation;
use Compose\Container\ContainerAwareTrait;

/**
 * Class Controller
 *
 * MVC Controller.  Supports multiple actions and SCRUD for each actions
 * @package Compose\Mvc
 */
abstract class Controller extends RequestHandler implements ContainerAwareInterface
{
    use ContainerAwareTrait;
    
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
        ],

        /**
         * Action prefix
         *
         * @var string
         */
        $actionNamePrefix = 'do',

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
     * @return ResponseInterface
     * @throws HttpException
     * @throws \ReflectionException
     */
    protected function onHandle(ServerRequestInterface $request) : ResponseInterface
    {
        /** @var Invocation $invocation */
        $invocation = $this->resolveActionHandler($request);
        return $invocation();
    }

    /**
     * Resolving action handler
     *
     * @param ServerRequestInterface $request
     * @return Invocation|null
     * @throws HttpException
     */
    protected function resolveActionHandler(ServerRequestInterface $request) : Invocation
    {
        /** @var RouteInfo $route */
        $route = $request->getAttribute(RouteInfo::class);
        if(!$route) {
            $path = $request->getUri()->getPath();
            $route = new \ArrayObject([
                'method' => $request->getMethod(),
                'path' => $path,
                'params' => array_values(array_filter(explode('/', $path)))
            ], \ArrayObject::ARRAY_AS_PROPS);
        }

        $action = $this->resolveActionName($route);
        $params = $route->params;

        

        $invocation = Invocation::fromCallable([$this, $action]);
        if(!$invocation) {
            throw new HttpException("Unable to find action for request: {$route->method}: {$route->path} in " . get_class($this), 404);
        }
        if($invocation->getArgumentTypeAtIndex(0) == ServerRequestInterface::class) {
            array_unshift($params, $request); // add the request as the first param
        }

        $invocation->setParameters($params);
        return $invocation;
    }

    /**
     * Implementing the abstract action resolve method
     *
     * @param RouteInfo $route
     * @return string
     * @throws HttpException
     */
    protected function resolveActionName(\ArrayObject $route) : string
    {
        // map http method
        $httpMethod = strtolower($route->method);
        if(isset($this->httpMethodMapper[$httpMethod])) {
            $httpMethod = $this->httpMethodMapper[$httpMethod];
        }

        // handle default case, ie. path/ (GET request + no param passed)
        if(!count($route->params) && $httpMethod == 'get') {
            return $this->buildActionName($this->defaultAction);
        }

        // now check for standard restful methods
        $restMethod = $this->buildActionName($httpMethod);
        if(method_exists($this, $restMethod)) {
            return $restMethod;
        }

        // restful action method not found.
        // will attempt individual action method
        $action = $this->filterActionName(array_shift($route->params));
        $actionMethod = $restMethod . ucfirst($action);

        return $actionMethod;
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

    /**
     * Validate action name
     *
     * @note regex from php doc for function name: http://php.net/manual/en/functions.user-defined.php
     * @param string $action
     * @return bool
     */
    protected function filterActionName(string $action = null)
    {
        if(!$action) return null;
        $allowedChars = ['-'];

        // if allowed chars are provided,
        // then we will need to remove them first
        if(count($allowedChars)) {
            $action = str_replace(' ', '', str_replace($allowedChars, ' ', $action));
        }

        $regex = "/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/"; // see comment ^
        if(!preg_match($regex, $action)) {
            return null;
        }

        return $action;
    }


    /**
     * View rendering helper
     *
     * Right now relies on ContainerAwareInterface to get the container and get the View renderer
     * @param View $view
     * @param int $status
     * @param array $headers
     * @return ResponseInterface
     * @throws \Exception
     */
    protected function view(View $view, int $status = 200, array $headers = []): ResponseInterface
    {
        /** @var ViewRenderer $renderer */
        $renderer = $this->getContainer()->get(ViewRendererInterface::class);
        if (!$renderer) {
            throw new \Exception("ViewRenderer not found in the container.");
        }

        return $this->html($renderer->render($view), $status, $headers);
    }

    /**
     * @param string $script
     * @param array|null $data
     * @param int $status
     * @param array $headers
     * @return ResponseInterface
     * @throws \Exception
     */
    protected function render(string $script, array $data = null, int $status = 200, array $headers = []) : ResponseInterface
    {
        return $this->view(new View($script, $data), $status, $headers);
    }
}