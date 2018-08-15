<?php
namespace Sfynx\RestClientBundle\Http\Asynchronous;

use Sfynx\RestClientBundle\Http\Asynchronous\Generalisation\Interfaces\RequestInterface;

/**
 * RequestCallback class
 *
 * @category   Sfynx\RestClientBundle
 * @package    Http
 * @subpackage Asynchronous
 */
class RequestCallback
{
    /** @var RequestInterface */
    protected $request;
    /** @var ?callable */
    protected $callback;

    /**
     * RequestCallback constructor.
     * @param RequestInterface $request
     * @param callable|null $callback
     */
    public function __construct(RequestInterface $request, ?callable $callback = NULL)
    {
        if ($callback !== null && !\is_callable($callback)) {
            throw new Exception('Invalid callback');
        }

        $this->request = $request;
        $this->callback = $callback;
    }

    /**
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    /**
     * @return callable|null
     */
    public function getCallback(): ?callable
    {
        return $this->callback;
    }
}
