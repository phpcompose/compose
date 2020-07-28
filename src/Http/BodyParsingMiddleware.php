<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2018-04-11
 * Time: 10:18 AM
 */

namespace Compose\Http;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Body parsing for PUT/PATCh request
 *
 * codes inspired from zend/express/helper... didn't use that library because it has too many dependencies like http, routers, etc.
 * Class BodyParsingMiddleware
 * @package Compose\Http
 */
class BodyParsingMiddleware implements MiddlewareInterface
{
    protected
        $methods = ['PUT', 'PATCH'];

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $parsedBody = $request->getParsedBody();
        if(!empty($parsedBody)) return $handler->handle($request);

        $contentType = $request->getHeaderLine('Content-Type');
        $rawBody = (string)$request->getBody();

        if (preg_match('#^application/x-www-form-urlencoded($|[ ;])#', $contentType)) {
            mb_parse_str($rawBody, $parsedBody);
            $request = $request->withParsedBody($parsedBody);
        } else {
            $parts = explode(';', $contentType);
            $mime = array_shift($parts);
            if(preg_match('#[/+]json$#', trim($mime))) {
                $parsedBody = json_decode($rawBody, true);
                if($parsedBody) $request = $request->withParsedBody($parsedBody);
            }
        }


        return $handler->handle($request);
    }
}