<?php

/**
 * This file is part of the Da Project.
 *
 * (c) Thomas Prelot <tprelot@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sfynx\RestClientBundle\Http\Rest;

use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

use Sfynx\CircuitBreakerBundle\Exception\UnavailableServiceException;
use Sfynx\CircuitBreakerBundle\Generalisation\CircuitBreakerInterface;
use Sfynx\RestClientBundle\Http\Transport\HttpTransportFactory;

/**
 * RestApiClientBasicImplementor is a basic implementation for a REST API client.
 *
 * @author Gabriel Bondaz <gabriel.bondaz@idci-consulting.fr>
 * @author Etienne de Longeaux <etienne.delongeaux@gmail.com>
 */
class RestApiClientBasicImplementor extends AbstractRestApiClientImplementor
{
    /** @var CircuitBreakerInterface */
    protected $circuitBreaker;
    /** @var ContainerInterface */
    protected $container;

    /**
     * Constructor.
     * @param CircuitBreakerInterface $circuitBreaker
     */
    public function __construct(CircuitBreakerInterface $circuitBreaker, ContainerInterface $container)
    {
        $this->circuitBreaker = $circuitBreaker;
        // Use container to remove annoying circular dependencies.
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    protected function getAccessToken()
    {
        $accessToken = null;

        // Use container to remove annoying circular dependencies.
        if ($this->container->has('security.context')) {
            $securityContext = $this->container->get('security.context');
            if (($token = $securityContext->getToken())) {
                $class = new \ReflectionClass($token);

                if ($class->hasMethod('getAccessToken')) {
                    $accessToken = $token->getAccessToken();
                }
            }
        }

        return $accessToken;
    }

    /**
     * Add query string
     *
     * @param string       $path
     * @param string|array $queryString
     *
     * @return string
     */
    public static function addQueryString($path, $queryString = null)
    {
        if(null === $queryString) {
            return $path;
        }

        return sprintf("%s%s%s",
            $path,
            preg_match("#\?#", $path) ? (
                preg_match("#\?$#", $path) ?
                '' : '&'
            ) : '?',
            is_array($queryString) ? http_build_query($queryString) : $queryString
        );
    }

    /**
     * {@inheritdoc}
     */
    public function get($path, $queryString = null, array $headers = [], $noCache = false, $absolutePath = false)
    {
        $path = self::addQueryString($path, $queryString);

        return $this->send('GET', $path, $headers, $queryString, null, $noCache, $absolutePath);
    }

    /**
     * {@inheritdoc}
     */
    public function post($path, $queryString = null, array $headers = [])
    {
        return $this->send('POST', $path, $headers, $queryString);
    }

    /**
     * {@inheritdoc}
     */
    public function put($path, $queryString = null, array $headers = [])
    {
        return $this->send('PUT', $path, $headers, $queryString);
    }

    /**
     * {@inheritdoc}
     */
    public function patch($path, $queryString = null, array $headers = [])
    {
        return $this->send('PATCH', $path, $headers, $queryString);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($path, $queryString = null, array $headers = [])
    {
        return $this->send('DELETE', $path, $headers, $queryString);
    }

    /**
     * {@inheritdoc}
     */
    public function link($path, array $links, array $headers = [])
    {
        return $this->send('LINK', $path, $headers, null, $links);
    }

    /**
     * {@inheritdoc}
     */
    public function unlink($path, array $links, array $headers = [])
    {
        return $this->send('UNLINK', $path, $headers, null, $links);
    }

    /**
     * Send a request to an API.
     *
     * @param string       $method       The HTTP method.
     * @param string       $path         The relative path to the webservice.
     * @param array        $headers      The optionnal headers.
     * @param string|array $queryString  The specific queryString to the webservice.
     * @param array        $links        Array of resources to link.
     * @param boolean      $noCache      To force the request without check if a cache response exist.
     * @param boolean      $absolutePath To use absolute path instead of build it with api endpoint.
     *
     * @return Sfynx\RestClientBundle\Http\Response
     *
     * @throws ApiHttpResponseException
     */
    protected function send(
        $method,
        $path,
        array $headers = [],
        $queryString = null,
        array $links = null,
        $noCache = false,
        $absolutePath = false
    ) {
        $headers = $this->initHeaders($headers);
        $transport = HttpTransportFactory::build(
            'curl',
            $this->getCacher(),
            $this->getLogger()
        );

        if (!$absolutePath) {
            $path = $this->getApiEndpointPath($path);
        }

        $transport
            ->setMethod($method)
            ->setPath($path)
            ->setHeaders($headers)
        ;

        if (null !== $queryString) {
            $transport->setQueryString($queryString);
        }
        if (null !== $links) {
            $transport->setLinks($links);
        }

        if (!is_null($this->getCircuitBreakerName())) {
            $this->circuitBreaker->checkAvailable($this->getCircuitBreakerName());
        }

        try {
            $response = $transport->send();

            if (!is_null($this->getCircuitBreakerName())) {
                $this->circuitBreaker->reportSuccess($this->getCircuitBreakerName());
            }
        } /** @noinspection BadExceptionsProcessingInspection */ catch (Exception $exception) {
            // This Exception is dependent of CircuitBreaker project.

            if (!is_null($this->getCircuitBreakerName())) {
                $this->circuitBreaker->reportFailure($this->getCircuitBreakerName());
                throw UnavailableServiceException::serviceCallFailure($this->getCircuitBreakerName());
            }
            if (
                401 === $exception->getStatusCode() &&
                $this->container->hasParameter('sfynx_rest_client.authorization_refresher.jwt')
            ) {
                // Try to refresh the access token.
                // TODO

                $response = $transport->send($noCache);
            } else {
                throw $exception;
            }
        }

        return $response;
    }

    /**
     * Get the api endpoint path
     *
     * @param string $path
     * @return string The api path (url)
     */
    protected function getApiEndpointPath($path)
    {
        return sprintf('%s%s',
            $this->getEndpointRoot(),
            $path
        );
    }
}
