<?php

declare(strict_types=1);

namespace Daalvand\Kafka\Consumer;

interface ConsumerBuilderInterface
{
    public const OFFSET_BEGINNING = RD_KAFKA_OFFSET_BEGINNING;
    public const OFFSET_END = RD_KAFKA_OFFSET_END;
    public const OFFSET_STORED = RD_KAFKA_OFFSET_STORED;

    /**
     * @return array
     */
    public function getBrokers(): array;

    /**
     * @param string[] $brokers
     * @return ConsumerBuilderInterface
     */
    public function setBrokers(array $brokers): self;

    /**
     * Adds a broker from which you want to consume
     *
     * @param string $broker
     * @return ConsumerBuilderInterface
     */
    public function withAdditionalBroker(string $broker): self;

    /**
     * Add topic name(s) (and additionally partition(s) and offset(s)) to subscribe to
     *
     * @param string  $topicName
     * @param int[]   $partitions
     * @param integer $offset
     * @return ConsumerBuilderInterface
     */
    public function withAdditionalSubscription(
        string $topicName,
        array $partitions = [],
        int $offset = self::OFFSET_STORED
    ): self;

    /**
     * Replaces all topic names previously configured with a topic and additionally partitions and an offset to
     * subscribe to
     *
     * @param string  $topicName
     * @param int[]   $partitions
     * @param integer $offset
     * @return ConsumerBuilderInterface
     */
    public function withSubscription(
        string $topicName,
        array $partitions = [],
        int $offset = self::OFFSET_STORED
    ): self;

    /**
     * Add configuration settings, otherwise the kafka defaults apply
     *
     * @param string[] $config
     * @return ConsumerBuilderInterface
     */
    public function withAdditionalConfig(array $config): self;

    /**
     * Set the consumer group
     *
     * @param string $consumerGroup
     * @return ConsumerBuilderInterface
     */
    public function withConsumerGroup(string $consumerGroup): self;

    /**
     * Set a callback to be called on errors.
     * The default callback will throw an exception for every error
     *
     * @param callable $errorCallback
     * @return ConsumerBuilderInterface
     */
    public function withErrorCallback(callable $errorCallback): self;

    /**
     * Set a callback to be called on consumer rebalance
     *
     * @param callable $rebalanceCallback
     * @return ConsumerBuilderInterface
     */
    public function withRebalanceCallback(callable $rebalanceCallback): self;

    /**
     * Only applicable for the high level consumer
     * Callback that is going to be called when you call consume
     *
     * @param callable $consumeCallback
     * @return ConsumerBuilderInterface
     */
    public function withConsumeCallback(callable $consumeCallback): self;

    /**
     * Set callback that is being called on offset commits
     *
     * @param callable $offsetCommitCallback
     * @return ConsumerBuilderInterface
     */
    public function withOffsetCommitCallback(callable $offsetCommitCallback): self;

    /**
     * Callback for log related events
     *
     * @param callable $logCallback
     * @return ConsumerBuilderInterface
     */
    public function withLogCallback(callable $logCallback): ConsumerBuilderInterface;

    /**
     * Returns your consumer instance
     *
     * @return ConsumerInterface
     */
    public function build(): ConsumerInterface;
}
