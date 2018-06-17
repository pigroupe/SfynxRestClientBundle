<?php
namespace Sfynx\RestClientBundle\Http\Asynchronous\Generalisation\Interfaces;

/**
 * RequestInterface class
 *
 * @category   Sfynx\RestClientBundle
 * @package    Http
 * @subpackage Asynchronous
 */
interface RequestInterface
{
    /**
     * Returns cURL handle
     * @return resource CURL handle
     */
    public function getHandle();

    /**
     * Creates own implementation of response
     * @param string $curlResponse
     * @return mixed Custom response object
     */
    public function createResponse(string $curlResponse);
}
