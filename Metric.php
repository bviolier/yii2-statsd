<?php

namespace bobviolier\yii2\statsd;

use \yii\base\Component;
use \Domnikl\Statsd;
use yii\web;

/**
 * Yii2 Statsd component
 * ===================
 *
 * This component allows to add a component to use StatD (https://github.com/domnikl/statsd-php)
 * It supports all the features of StatsD
 *
 * _Component usage example:_
 * ```
 * return [
 *      ...
 *      'components' => [
 *          ...
 *          'metric' => [
 *              'class' => '\bobviolier\yii2\statsd\Metric',
 *          ],
 *          ...
 *      ],
 *      ...
 * ];
 * ```
 */
class Metric extends Component
{

    public $host = '127.0.0.1';
    public $port = 8125;
    public $namespace = 'messagebird';
    public $applicationName = 'messagebird';

    /**
     * @var Statsd\Connection\UdpSocket
     */
    protected $connection;

    /**
     * @var Statsd\Client
     */
    protected $client;


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->connection = new Statsd\Connection\UdpSocket($this->host, $this->port);
        $this->client     = new Statsd\Client($this->connection, $this->namespace);
    }

    /**
     * @param      $httpCode
     * @param null $applicationName
     */
    public function logHttpResponse($httpCode, $applicationName = null)
    {
        $this->increment($this->getApplicationName($applicationName) . '.http_response.' . $httpCode);
    }

    /**
     * @param      $ms
     * @param null $applicationName
     */
    public function logResponseTimeMs($ms, $applicationName = null)
    {
        $this->timing($this->getApplicationName($applicationName).'.response_time_ms', $ms);
    }

    /**
     * @param                 $ms - Milliseconds
     * @param \Exception|null $exception
     * @param null            $applicationName
     */
    public function logError($ms, \Exception $exception = null, $applicationName = null)
    {
        if ($exception and $exception instanceof web\HttpException) {
            $httpCode = $exception->statusCode;
        } elseif (\Yii::$app instanceof web\Application) {
            $httpCode = \Yii::$app->response->getStatusCode();
        } else {
            $httpCode = 0;
        }

        $this->logHttpResponse($httpCode, $applicationName);
        $this->logResponseTimeMs($ms, $applicationName);
    }


    protected function getApplicationName($overwrite = null)
    {
        if ($overwrite) {
            return $overwrite;
        }
        return $this->applicationName;
    }

    /**
     * @inheritdoc
     */
    public function increment($key, $sampleRate = 1)
    {
        $this->client->increment($key, $sampleRate);
    }

    /**
     * @inheritdoc
     */
    public function decrement($key, $sampleRate = 1)
    {
        $this->client->decrement($key, $sampleRate);
    }

    /**
     * @inheritdoc
     */
    public function count($key, $value, $sampleRate = 1)
    {
        $this->client->count($key, $value, $sampleRate);
    }

    /**
     * @inheritdoc
     */
    public function timing($key, $value, $sampleRate = 1)
    {
        $this->client->timing($key, $value, $sampleRate);
    }

    /**
     * @inheritdoc
     */
    public function startTiming($key)
    {
        $this->client->startTiming($key);
    }

    /**
     * @inheritdoc
     */
    public function endTiming($key, $sampleRate = 1)
    {
        return $this->client->endTiming($key, $sampleRate);
    }

    /**
     * @inheritdoc
     */
    public function startMemoryProfile($key)
    {
        $this->client->startMemoryProfile($key);
    }

    /**
     * @inheritdoc
     */
    public function endMemoryProfile($key, $sampleRate = 1)
    {
        $this->client->endMemoryProfile($key, $sampleRate);
    }

    /**
     * @inheritdoc
     */
    public function memory($key, $memory = null, $sampleRate = 1)
    {
        $this->client->memory($key, $memory, $sampleRate);
    }

    /**
     * @inheritdoc
     */
    public function time($key, \Closure $_block, $sampleRate = 1)
    {
        return $this->client->time($key, $_block, $sampleRate);
    }

    /**
     * @inheritdoc
     */
    public function gauge($key, $value)
    {
        $this->client->gauge($key, $value);
    }

    /**
     * @inheritdoc
     */
    public function set($key, $value)
    {
        $this->client->set($key, $value);
    }

    /**
     * @inheritdoc
     */
    public function setNamespace($namespace)
    {
        $this->client->setNamespace($namespace);
    }

    /**
     * @inheritdoc
     */
    public function getNamespace()
    {
        return $this->client->getNamespace();
    }

    /**
     * @inheritdoc
     */
    public function isBatch()
    {
        return $this->client->isBatch();
    }

    /**
     * @inheritdoc
     */
    public function startBatch()
    {
        $this->client->startBatch();
    }

    /**
     * @inheritdoc
     */
    public function endBatch()
    {
        $this->client->endBatch();
    }

    /**
     * @inheritdoc
     */
    public function cancelBatch()
    {
        $this->client->cancelBatch();
    }
}