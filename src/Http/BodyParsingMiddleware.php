<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2018-04-11
 * Time: 10:18 AM
 */

namespace Compose\Http;


use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Body parsing for PUT/PATCH request
 *
 * codes inspired from Slim\BodyParsingMiddleware.
 * Class BodyParsingMiddleware
 * @package Compose\Http
 */
class BodyParsingMiddleware implements MiddlewareInterface
{
    protected array $methods = ['PUT', 'PATCH'];
    protected array $parsers = [];

    /**
     *
     */
    public function __construct()
    {
        // registering some initial parsers
        $this->addParser('application/x-www-form-urlencoded', function(ServerRequestInterface $request) {
            return mb_parse_str((string) $request->getBody(), $parsedBody);
        });

        $this->addParser('application/json', function (ServerRequestInterface $request) {
            return json_decode((string) $request->getBody(), true);
        });
    }

    /**
     * @param string|array $contentType
     * @param callable $parser
     */
    public function addParser(string|array $contentType, callable $parser) : void
    {
        if(is_array($contentType)) {
            foreach ($contentType as $type) {
                $this->addParser($type, $parser);
            }
        } else {
            $this->parsers[$contentType] = $parser;
        }
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $parsedBody = $request->getParsedBody();
        if(!empty($parsedBody)) {
            return $handler->handle($request);
        }

        $contentType = $this->contentTypeFromRequest($request);
        $parser = $this->parsers[$contentType] ?? null;
        if(!$parser) {
            return $handler->handle($request);
        }

        $parsedContent = $parser($request);
        if($parsedContent) {
            $request->withParsedBody($parsedContent);
        }

        return $handler->handle($request);
    }

    /**
     * @param MessageInterface $request
     * @return string|null
     */
    public function contentTypeFromRequest(MessageInterface $request) : ?string
    {
        $contentTypeLine = $request->getHeaderLine('Content-Type');
        $contentType = null;
        if($contentTypeLine) {
            $contentTypeParts = explode(';', $contentTypeLine);
            $contentType = strtolower(trim($contentTypeParts[0]));

            $contentTypeParts = explode('+', $contentType); // structured syntax suffix
            if (count($contentTypeParts) >= 2) {
                $contentType = 'application/' . $contentTypeParts[count($contentTypeParts) - 1];
            }
        }

        return $contentType;
    }
}