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
     * @var Statsd\Client
     */
    protected $client;


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $connection   = new Statsd\Connection\UdpSocket($this->host, $this->port);
        $this->client = new Statsd\Client($connection, $this->namespace);
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
     * Call a statsd method in Statsd\Client using $this->client as configured object
     *
     * @param string $methodName
     * @param array  $arguments
     */
    public function __call($methodName, $arguments)
    {
        if ($this->client && method_exists($this->client, $methodName)) {
            call_user_func_array([$this->client, $methodName], $arguments);
        }
    }
}
