<?php

namespace Mcustiel\Phiremock\Server\Utils;

use Mcustiel\Phiremock\Common\Utils\ArrayToExpectationConverter;
use Mcustiel\Phiremock\Domain\MockConfig;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class RequestToMockConfigMapper
{
    const CONTENT_ENCODING_HEADER = 'Content-Encoding';

    /** @var ArrayToExpectationConverter */
    private $converter;

    /** @var LoggerInterface */
    private $logger;

    /**
     * @param ArrayToExpectationConverter $converter
     * @param LoggerInterface             $logger
     */
    public function __construct(
        ArrayToExpectationConverter $converter,
        LoggerInterface $logger
    ) {
        $this->converter = $converter;
        $this->logger = $logger;
    }

    /** @return MockConfig */
    public function map(ServerRequestInterface $request)
    {
        $this->logger->debug('Adding Expectation->parseRequestObject');
        /** @var \Mcustiel\Phiremock\Domain\MockConfig $object */
        $object = $this->converter->convert($this->parseJsonBody($request));
        $this->logger->debug(var_export($object, true));

        return $object;
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @throws \Exception
     *
     * @return array
     */
    private function parseJsonBody(ServerRequestInterface $request)
    {
        $this->logger->debug('Adding Expectation->parseJsonBody');
        $body = $request->getBody()->__toString();
        $this->logger->debug($body);
        if ($this->hasBinaryBody($request)) {
            $body = base64_decode($body, true);
        }

        $bodyJson = @json_decode($body, true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \Exception(json_last_error_msg());
        }
        $this->logger->debug(var_export($bodyJson, true));

        return $bodyJson;
    }

    /** @return bool */
    private function hasBinaryBody(ServerRequestInterface $request)
    {
        return $request->hasHeader(self::CONTENT_ENCODING_HEADER)
            && 'base64' === $request->getHeader(self::CONTENT_ENCODING_HEADER);
    }
}