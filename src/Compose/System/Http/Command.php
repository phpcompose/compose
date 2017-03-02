<?php
/**
 * Created by PhpStorm.
 * User: Alamin
 */

namespace Compose\System\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class Command
 *
 * Implements command pattern and provides simple class life cycle for organizing code
 * @package Compose\System\Http
 */
abstract class Command implements CommandInterface
{
    protected
        /**
         * @var ServerRequestInterface
         */
        $request;

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