<?php
namespace Sfynx\RestClientBundle\Http\Asynchronous;

use Sfynx\RestClientBundle\Http\Asynchronous\Generalisation\Interfaces\RequestInterface;

/**
 * AsyncRequest class
 *
 * @category   Sfynx\RestClientBundle
 * @package    Http
 * @subpackage Asynchronous
 */
class AsyncRequest
{
    const DEFAULT_PRIORITY = 1;

    /** @var resource */
    protected $handle;
    /** @var RequestCallback[] */
    protected $requests = [];
    /** @var \SplPriorityQueue */
    protected $queue;
    /** @var int */
    protected $runningCount = 0;
    /** @var ?int */
    protected $parallelLimit = NULL;

    /**
     * AsyncRequest constructor.
     */
    public function __construct()
    {
        $this->handle = curl_multi_init();
        $this->queue = new \SplPriorityQueue();
    }

    /**
     * Sets number of requests that can be sent in parallel.
     * Null means no limit (default value).
     */
    public function setParallelLimit(?int $parallelLimit): void
    {
        $this->parallelLimit = $parallelLimit;
    }

    /**
     * Adds new request to downloading.
     */
    public function enqueue(RequestInterface $request, ?callable $callback = null): void
    {
        $this->enqueueWithPriority(static::DEFAULT_PRIORITY, $request, $callback);
    }

    /**
     * Adds new request to downloading and sets its priority.
     * Requests with higher priority will be send first.
     */
    public function enqueueWithPriority(int $priority, RequestInterface $request, ?callable $callback = null): void
    {
        $uuid = (int) $request->getHandle();
        $this->requests[$uuid] = new RequestCallback($request, $callback);
        $this->queue->insert($uuid, $priority);
        $this->startFromQueue();
    }

    /**
     * Returns number of requests that are running or waiting.
     */
    public function count(): int
    {
        return \count($this->requests);
    }

    /**
     * Download all pages.
     * This is blocking call so this method ends when all pages are downloaded.
     *
     * @param float $timeout [optional] Time, in seconds, to wait for a response.
     */
    public function run(float $timeout = 0.05): void
    {
        while ($this->count()) {
            $this->waitForData($timeout);
            $this->processCompleted();
        }
    }

    /**
     * Waits for next request to complete but maximum $timeout seconds.
     *
     * @param float $timeout [optional] Time, in seconds, to wait for a response.
     */
    public function waitForData(float $timeout = 0.05): void
    {
        if ($this->count() == 0) {
            throw new Exception('No requests are running.');
        }

        while (\curl_multi_exec($this->handle, $runningCount) === CURLM_CALL_MULTI_PERFORM);
        \curl_multi_select($this->handle, $timeout);
    }

    /**
     * Process downloaded requests.
     */
    public function processCompleted(): void
    {
        while ($info = \curl_multi_info_read($this->handle)) {
            $this->callCallback($info);
        }
    }

    /**
     * Creates response object and calls callback.
     */
    protected function callCallback(array $info): void
    {
        $this->runningCount--;
        $uuid = (int) $info['handle'];

        $requestCallback = $this->requests[$uuid];
        $request = $requestCallback->getRequest();
        $handle = $request->getHandle();

        \curl_multi_remove_handle($this->handle, $handle);
        unset($this->requests[$uuid]);

        $callback = $requestCallback->getCallback();
        if ($callback !== null) {
            $curlResponse = \curl_multi_getcontent($handle);
            $callback($request->createResponse($curlResponse), $this);
        }

        $this->startFromQueue();
    }

    /**
     * Starts new request from queue if there is free space in parallel limit.
     */
    protected function startFromQueue(): void
    {
        $freeSlots = $this->parallelLimit === NULL || $this->runningCount < $this->parallelLimit;

        if (!$this->queue->isEmpty() && $freeSlots) {
            $uuid = $this->queue->extract();
            $request = $this->requests[$uuid]->getRequest();
            \curl_multi_add_handle($this->handle, $request->getHandle());
            $this->runningCount++;
        }
    }
}
