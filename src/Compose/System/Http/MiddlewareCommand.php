<?php
/**
 * Created by PhpStorm.
 * User: Alamin
 */

namespace Compose\System\Http;


use Interop\Http\Middleware\DelegateInterface;
use Interop\Http\Middleware\ServerMiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class MiddlewareCommand implements CommandInterface, ServerMiddlewareInterface
{
    protected
        /**
         * @var ServerRequestInterface
         */
        $request;

    /**
     * Implementing new psr middleware
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        return $this->execute($request);
    }

    /**
     * Implementing legacy middleware interface
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable|null $next
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        return $this->execute($request);
    }

    /**
     * Implementing command interface
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    final public function execute(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;

        try {
            $this->onInit();

            $response = $this->onExecute($request);

            $this->onExit();
        }

        catch(\Exception $exception) {
            $response = $this->onException($exception);
        }

        return $response;
    }

    /**
     * Command initialize method
     */
    protected function onInit() {}

    /**
     * Perform actual command execution
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    abstract protected function onExecute(ServerRequestInterface $request) : ResponseInterface;

    /**
     * Exit method
     */
    protected function onExit() {}

    /**
     * Capture Exceptions in one central function
     *
     * @param \Exception $e
     * @return null|ResponseInterface
     * @throws \Exception
     */
    protected function onException(\Exception $e) : ?ResponseInterface
    {
        throw $e;
    }
}