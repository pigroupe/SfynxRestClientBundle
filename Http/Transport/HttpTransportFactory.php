<?php

/**
 * This file is part of the Da Project.
 *
 * (c) Thomas Prelot <tprelot@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sfynx\RestClientBundle\Http\Transport;

use Doctrine\Common\Cache\Cache;
use Sfynx\RestClientBundle\Logger\HttpLoggerInterface;
use Sfynx\RestClientBundle\Exception\UndefinedTransportException;

/**
 * HttpTransportFactory
 *
 * @author Gabriel Bondaz <gabriel.bondaz@idci-consulting.fr>
 */
abstract class HttpTransportFactory
{
    /**
     * Build
     *
     * @param  string                 $transportName
     * @param  Cache                  $cache
     * @param  HttpLoggerInterface    $logger
     * @return HttpTransportInterface
     */
    public static function build($transportName, Cache $cache = null, HttpLoggerInterface $logger = null)
    {
        $className = sprintf(
            'Sfynx\RestClientBundle\Http\Transport\%sHttpTransport',
            ucfirst(strtolower($transportName))
        );

        if (!class_exists($className)) {
            throw new UndefinedTransportException($className);
        }

        return new $className($cache, $logger);
    }
}
