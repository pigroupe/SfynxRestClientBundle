<?php

/**
 * This file is part of the Da Project.
 *
 * (c) Thomas Prelot <tprelot@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sfynx\RestClientBundle\Http\Rest\Generalisation\Interfaces;

use Sfynx\RestClientBundle\Http\Response;
use Sfynx\RestClientBundle\Exception\ApiHttpResponseException;

/**
 * RestApiClientInterface
 *
 * @author Thomas Prelot
 * @author Gabriel Bondaz <gabriel.bondaz@idci-consulting.fr>
 */
interface RestApiClientInterface
{
    /**
     * Get the api endpoint root
     *
     * @return string
     */
    public function getEndpointRoot();

    /**
     * Get
     *
     * @param string       $path         The relative path to the webservice.
     * @param string|array $queryString  The specific queryString to the webservice.
     * @param array        $headers      The optionnal headers.
     * @param boolean      $noCache      To force the request without check if a cache response exist.
     * @param boolean      $absolutePath To use absolute path instead of build it with api endpoint.
     *
     * @return Response
     *
     * @throws ApiHttpResponseException
     */
    public function get($path, $queryString = null, array $headers = [], $noCache = false, $absolutePath = false);

    /**
     * Post
     *
     * @param string       $path        The relative path to the webservice.
     * @param string|array $queryString The specific queryString to the webservice.
     * @param array        $headers     The optionnal headers.
     *
     * @return Response
     *
     * @throws ApiHttpResponseException
     */
    public function post($path, $queryString = null, array $headers = []);

    /**
     * Put
     *
     * @param string       $path        The relative path to the webservice.
     * @param string|array $queryString The specific queryString to the webservice.
     * @param array        $headers     The optionnal headers.
     *
     * @return Response
     *
     * @throws ApiHttpResponseException
     */
    public function put($path, $queryString = null, array $headers = []);

    /**
     * Patch
     *
     * @param string       $path        The relative path to the webservice.
     * @param string|array $queryString The specific queryString to the webservice.
     * @param array        $headers     The optionnal headers.
     *
     * @return Response
     *
     * @throws ApiHttpResponseException
     */
    public function patch($path, $queryString = null, array $headers = []);

    /**
     * Delete
     *
     * @param string       $path        The relative path to the webservice.
     * @param string|array $queryString The specific queryString to the webservice.
     * @param array        $headers     The optionnal headers.
     *
     * @return Response
     *
     * @throws ApiHttpResponseException
     */
    public function delete($path, $queryString = null, array $headers = []);

    /**
     * Link
     *
     * @param string $path        The relative path to the webservice.
     * @param array  $links       Array of resources to link.
     * @param array  $headers     The optionnal headers.
     *
     * @return Response
     *
     * @throws ApiHttpResponseException
     */
    public function link($path, array $links, array $headers = []);

    /**
     * Unlink
     *
     * @param string $path        The relative path to the webservice.
     * @param array  $links       Array of resources to unlink.
     * @param array  $headers     The optionnal headers.
     *
     * @return Response
     *
     * @throws ApiHttpResponseException
     */
    public function unlink($path, array $links, array $headers = []);
}
