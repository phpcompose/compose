<?php


use Laminas\Diactoros\Request;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

class BodyParsingMiddlewareTest extends TestCase
{

    public function testCanParseContentTypeFromRequest()
    {
        $parser = new \Compose\Http\BodyParsingMiddleware();
        $request = new Request(
            'https://example.com',
            'PATCH',
            'php://memory',
            [
                'Authorization' => 'Bearer ABCD',
                'Content-Type'  => 'application/json',
            ]
        );
        $request2 = new Request(
            'https://example.com',
            'PATCH',
            'php://memory',
            [
                'Authorization' => 'Bearer ABCD',
                'Content-Type'  => 'application/svg+xml',
            ]
        );
        $this->assertEquals('application/json', $parser->contentTypeFromRequest($request));
        $this->assertEquals('application/xml', $parser->contentTypeFromRequest($request2));
    }


}
