<?php

declare(strict_types=1);

namespace Daalvand\Kafka\Producer;

use Daalvand\Kafka\Callback\ErrorCallback;
use Daalvand\Kafka\Callback\ProducerDeliveryReportCallback;
use Daalvand\Kafka\Conf\Config;
use Daalvand\Kafka\Exceptions\ProducerException;
use RdKafka\Producer as RdKafkaProducer;

final class ProducerBuilder implements ProducerBuilderInterface
{
    /**
     * @var string[]
     */
    private array $brokers = [];

    /**
     * @var string[]
     */
    private array $config = [];

    /**
     * @var callable
     */
    private $deliverReportCallback;

    /**
     * @var callable
     */
    private $errorCallback;

    /**
     * KafkaProducerBuilder constructor.
     */
    public function __construct()
    {
        $this->deliverReportCallback = new ProducerDeliveryReportCallback();
        $this->errorCallback = new ErrorCallback();
    }


    /**
     * @return string[]
     */
    public function getBrokers(): array
    {
        return $this->brokers;
    }

    /**
     * @param string[] $brokers
     * @return ProducerBuilder
     */
    public function setBrokers(array $brokers): self
    {
        $this->brokers = $brokers;
        return $this;
    }

    /**
     * Adds a broker to which you want to produce
     *
     * @param string $broker
     * @return $this
     */
    public function withAdditionalBroker(string $broker): self
    {
        $this->brokers[] = $broker;

        return $this;
    }

    /**
     * Add configuration settings, otherwise the kafka defaults apply
     *
     * @param string[] $config
     * @return $this
     */
    public function withAdditionalConfig(array $config): self
    {
        $this->config = $config + $this->config;

        return $this;
    }

    /**
     * Sets callback for the delivery report. The broker will send a delivery
     * report for every message which describes if the delivery was successful or not
     *
     * @param callable $deliveryReportCallback
     * @return $this
     */
    public function withDeliveryReportCallback(callable $deliveryReportCallback): self
    {
        $this->deliverReportCallback = $deliveryReportCallback;

        return $this;
    }

    /**
     * Set a callback to be called on errors.
     * The default callback will throw an exception for every error
     *
     * @param callable $errorCallback
     * @return $this
     */
    public function withErrorCallback(callable $errorCallback): self
    {
        $this->errorCallback = $errorCallback;

        return $this;
    }

    /**
     * Returns your producer instance
     *
     * @return ProducerInterface
     * @throws ProducerException
     */
    public function build(): ProducerInterface
    {
        if ([] === $this->brokers) {
            throw new ProducerException(ProducerException::NO_BROKER_EXCEPTION_MESSAGE);
        }

        //Thread termination improvement (https://github.com/arnaud-lb/php-rdkafka#performance--low-latency-settings)
        $this->config['socket.timeout.ms'] = '50';
        $this->config['queue.buffering.max.ms'] = '1';

        if (function_exists('pcntl_sigprocmask')) {
            pcntl_sigprocmask(SIG_BLOCK, array(SIGIO));
            $this->config['internal.termination.signal'] = (string) SIGIO;
            unset($this->config['queue.buffering.max.ms']);
        }

        $kafkaConfig = new Config($this->brokers, [], $this->config);

        //set producer callbacks
        $this->registerCallbacks($kafkaConfig);

        $rdKafkaProducer = new RdKafkaProducer($kafkaConfig);

        return new Producer($rdKafkaProducer, $kafkaConfig);
    }

    /**
     * @param Config $conf
     * @return void
     */
    private function registerCallbacks(Config $conf): void
    {
        $conf->setDrMsgCb($this->deliverReportCallback);
        $conf->setErrorCb($this->errorCallback);
    }
}
