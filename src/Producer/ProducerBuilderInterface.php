<?php

declare(strict_types=1);

namespace Daalvand\Kafka\Producer;

interface ProducerBuilderInterface
{
    /**
     * @return ProducerInterface
     */
    public function build(): ProducerInterface;

    /**
     * @return array
     */
    public function getBrokers(): array;

    /**
     * @param string[] $brokers
     * @return self
     */
    public function setBrokers(array $brokers): self;

    /**
     * @param string $broker
     * @return self
     */
    public function withAdditionalBroker(string $broker): self;

    /**
     * @param string[] $config
     * @return self
     */
    public function withAdditionalConfig(array $config): self;

    /**
     * @param callable $deliveryReportCallback
     * @return self
     */
    public function withDeliveryReportCallback(callable $deliveryReportCallback): self;

    /**
     * @param callable $errorCallback
     * @return self
     */
    public function withErrorCallback(callable $errorCallback): self;
}
