<?php

namespace DMF\Capo\Http;

use Capo\Parser;
use Neos\Flow\Annotations as Flow;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class HeadMiddleware implements MiddlewareInterface
{
    /**
     * @Flow\Inject
     * @var LoggerInterface
     */
    protected $logger;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // TODO: Implement process() method.
        $response = $handler->handle($request);
        $body = $response->getBody();
        $this->logger->info("Head Middleware Response : " . $body);
        $body = Parser::reorder_head($body);
        $this->logger->info("Head Middleware Response : " . $body);
        return $response->withBody($body);
    }
}
