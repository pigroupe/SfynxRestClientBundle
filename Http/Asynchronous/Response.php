<?php
namespace Sfynx\RestClientBundle\Http\Asynchronous;

/**
 * Response class
 *
 * @category   Sfynx\RestClientBundle
 * @package    Http
 * @subpackage Asynchronous
 */
class Response
{
    /** @var string */
    protected $url;
    /** @var ?string */
    protected $error;
    /** @var int */
    protected $httpCode;
    /** @var array */
    protected $headers;
    /** @var string */
    protected $body;

    /**
     * Response constructor.
     * @param string $url
     * @param null|string $error
     * @param int $httpCode
     * @param array $headers
     * @param string $body
     */
    public function __construct(string $url, ?string $error, int $httpCode, array $headers, string $body)
    {
        $this->url = $url;
        $this->error = $error;
        $this->httpCode = $httpCode;
        $this->headers = $headers;
        $this->body = $body;
    }

    /**
     * Returns URL of request.
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Returns cURL error string or null if there were no error.
     */
    public function getError(): ?string
    {
        return $this->error;
    }

    /**
     * Returns HTTP status code.
     */
    public function getHttpCode(): int
    {
        return $this->httpCode;
    }

    /**
     * Returns array of headers.
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Returns string with HTML body.
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * Checks if there was cURL error or request was unsuccessful according to status code.
     */
    public function hasError(): bool
    {
        return $this->getError() !== null || $this->getHttpCode() >= 400;
    }
}
