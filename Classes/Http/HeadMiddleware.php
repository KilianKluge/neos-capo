<?php

namespace DMF\Capo\Http;

use DMF\Capo\Capo\Parser;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Http\ContentStream;
use Neos\Flow\Log\Utility\LogEnvironment;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class HeadMiddleware implements MiddlewareInterface
{
    /**
     * @Flow\InjectConfiguration(package="Capo", path="options")
     */
    protected array $options = [];

    /**
     * @Flow\Inject
     * @var LoggerInterface
     */
    protected $logger;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $usedOptions = $this->options ?? [];

        $response = $handler->handle($request);
        $body = $response->getBody();

        $body = Parser::reorder_head(strval($body), $usedOptions);

        $analysis = Parser::get_last_analysis();
        if (isset($usedOptions['debug'])) {
            $this->logger->debug('Capo: element_count'.$analysis['element_count'].', elapsed:'.$analysis['elapsed_ms'].', warnings'.count($analysis['warnings']).', tokens:'.count($analysis['tokens']), LogEnvironment::fromMethodName(__METHOD__));
        }
        if (isset($usedOptions['display_warnings'])) {
            $warnings = $analysis['warnings'];
            if (count($warnings)>0) {
                $this->logger->warning('Warnings: '.count($warnings), LogEnvironment::fromMethodName(__METHOD__));
            }
            foreach ($warnings as $warning) {
                $this->logger->warning($warning['severity'].': '.$warning['warning'].($warning['element_html'] ? ' in '.$warning['element_html'] : ''), LogEnvironment::fromMethodName(__METHOD__));
            }
        }
        if (isset($usedOptions['display_weights'])) {
            $tokens = $analysis['tokens'];
            $this->logger->debug('Tokens: '.count($tokens), LogEnvironment::fromMethodName(__METHOD__));
            foreach ($tokens as $token) {
                $this->logger->debug($token['tag_name'].': '.$token['weight'], LogEnvironment::fromMethodName(__METHOD__));
            }
        }

        $stream = ContentStream::fromContents($body);
        return $response->withBody($stream);
    }
}
