<?php

declare(strict_types=1);

namespace Daalvand\Kafka\Consumer;

use Daalvand\Kafka\Callback\ErrorCallback;
use Daalvand\Kafka\Conf\Config;
use Daalvand\Kafka\Exceptions\ConsumerBuilderException;
use RdKafka\KafkaConsumer as RdKafkaConsumer;

final class ConsumerBuilder implements ConsumerBuilderInterface
{

    /**
     * @var string[]
     */
    private array $brokers = [];

    /**
     * auto.offset.reset option values for kafka by older version than 0.9 use (largest, smallest) after .9 must use (earliest, latest)
     * @var array<string, mixed>
     */
    private array $config = [
        'auto.offset.reset'        => 'latest',
        'enable.auto.commit'       => false,
        'enable.auto.offset.store' => false,
    ];

    /**
     * @var TopicSubscription[]
     */
    private array $topics = [];
    private ?string $consumerGroup = null;

    /**
     * @var callable
     */
    private $errorCallback;
    /**
     * @var callable
     */
    private $rebalanceCallback;

    /**
     * @var callable
     */
    private $consumeCallback;

    /**
     * @var callable
     */
    private $logCallback;

    /**
     * @var callable
     */
    private $offsetCommitCallback;

    /**
     * KafkaConsumerBuilder constructor.
     */
    public function __construct()
    {
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
     * @return $this
     */
    public function setBrokers(array $brokers): self
    {
        $this->brokers = $brokers;
        return $this;
    }

    /**
     * Adds a broker from which you want to consume
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
     * Add topic name(s) (and additionally partitions and offsets) to subscribe to
     *
     * @param string  $topicName
     * @param int[]   $partitions
     * @param integer $offset
     * @return $this
     */
    public function withAdditionalSubscription(
        string $topicName,
        array $partitions = [],
        int $offset = self::OFFSET_STORED
    ): self {
        $this->topics[] = new TopicSubscription($topicName, $partitions, $offset);

        return $this;
    }

    /**
     * Replaces all topic names previously configured with a topic and additionally partitions and an offset to
     * subscribe to
     *
     * @param string  $topicName
     * @param int[]   $partitions
     * @param integer $offset
     * @return $this
     */
    public function withSubscription(
        string $topicName, array $partitions = [], int $offset = self::OFFSET_STORED
    ): self
    {
        $this->topics = [new TopicSubscription($topicName, $partitions, $offset)];
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
        $this->config = array_replace($this->config, $config);
        return $this;
    }

    /**
     * Set the consumer group
     *
     * @param string $consumerGroup
     * @return $this
     */
    public function withConsumerGroup(string $consumerGroup): self
    {
        $this->consumerGroup = $consumerGroup;

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
     * Set a callback to be called on consumer rebalance
     *
     * @param callable $rebalanceCallback
     * @return $this
     */
    public function withRebalanceCallback(callable $rebalanceCallback): self
    {
        $this->rebalanceCallback = $rebalanceCallback;

        return $this;
    }

    /**
     * Only applicable for the high level consumer
     * Callback that is going to be called when you call consume
     *
     * @param callable $consumeCallback
     * @return $this
     */
    public function withConsumeCallback(callable $consumeCallback): self
    {
        $this->consumeCallback = $consumeCallback;

        return $this;
    }

    /**
     * Callback for log related events
     *
     * @param callable $logCallback
     * @return $this
     */
    public function withLogCallback(callable $logCallback): self
    {
        $this->logCallback = $logCallback;
        return $this;
    }

    /**
     * Set callback that is being called on offset commits
     *
     * @param callable $offsetCommitCallback
     * @return $this
     */
    public function withOffsetCommitCallback(callable $offsetCommitCallback): self
    {
        $this->offsetCommitCallback = $offsetCommitCallback;
        return $this;
    }


    /**
     * Returns your consumer instance
     *
     * @return ConsumerInterface
     * @throws ConsumerBuilderException
     */
    public function build(): ConsumerInterface
    {
        if ([] === $this->brokers) {
            throw new ConsumerBuilderException(ConsumerBuilderException::NO_BROKER_EXCEPTION_MESSAGE);
        }

        if ([] === $this->topics) {
            throw new ConsumerBuilderException(ConsumerBuilderException::NO_TOPICS_EXCEPTION_MESSAGE);
        }

        //set additional config
        if(!$this->consumerGroup){
            $this->consumerGroup = md5(serialize($this->topics));
        }
        $this->config['group.id'] = $this->consumerGroup;

        //create config
        $kafkaConfig = new Config(
            $this->brokers,
            $this->topics,
            $this->config
        );

        //set consumer callbacks
        $this->registerCallbacks($kafkaConfig);
        return new Consumer(new RdKafkaConsumer($kafkaConfig), $kafkaConfig);
    }

    /**
     * @param Config $conf
     * @return void
     */
    private function registerCallbacks(Config $conf): void
    {
        $conf->setErrorCb($this->errorCallback);

        if (null !== $this->rebalanceCallback) {
            $conf->setRebalanceCb($this->rebalanceCallback);
        }

        if (null !== $this->consumeCallback) {
            $conf->setConsumeCb($this->consumeCallback);
        }

        if (null !== $this->logCallback) {
            $conf->setLogCb($this->logCallback);
        }

        if (null !== $this->offsetCommitCallback) {
            $conf->setOffsetCommitCb($this->rebalanceCallback);
        }
    }
}
