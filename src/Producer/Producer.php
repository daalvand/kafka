<?php

declare(strict_types=1);

namespace Daalvand\Kafka\Producer;

use Daalvand\Kafka\Message\ProducerMessageInterface;
use Daalvand\Kafka\Conf\Config;
use RdKafka\Producer as RdKafkaProducer;
use RdKafka\ProducerTopic as RdKafkaProducerTopic;
use RdKafka\Metadata\Topic as RdKafkaMetadataTopic;

final class Producer implements ProducerInterface
{
    protected RdKafkaProducer $producer;

    protected Config $kafkaConfiguration;

    /**
     * @var RdKafkaProducerTopic[]
     */
    protected array $producerTopics = [];

    /**
     * KafkaProducer constructor.
     * @param RdKafkaProducer    $producer
     * @param Config $kafkaConfiguration
     */
    public function __construct(
        RdKafkaProducer $producer,
        Config $kafkaConfiguration
    ) {
        $this->producer = $producer;
        $this->kafkaConfiguration = $kafkaConfiguration;
    }

    /**
     * @inheritDoc
     */
    public function produce(ProducerMessageInterface $message, bool $autoPoll = true, int $pollTimeoutMs = -1): void
    {
        $topicProducer = $this->getProducerTopicForTopic($message->getTopicName());
        $topicProducer->producev(
            $message->getPartition(),
            RD_KAFKA_MSG_F_BLOCK,
            $message->getBody(),
            $message->getKey(),
            $message->getHeaders()
        );

        if ($autoPoll) {
            $this->producer->poll($pollTimeoutMs);
        }
    }

    /**
     * @inheritDoc
     */
    public function syncProduce(ProducerMessageInterface $message): void
    {
        $this->produce($message, true, -1);
    }

    /**
     * @inheritDoc
     */
    public function poll(int $timeoutMs = -1): void
    {
        $this->producer->poll($timeoutMs);
    }

    /**
     * @inheritDoc
     */
    public function pollUntilQueueSizeReached(int $timeoutMs = 0, int $queueSize = 0): void
    {
        while ($this->producer->getOutQLen() > $queueSize) {
            $this->producer->poll($timeoutMs);
        }
    }

    /**
     * @inheritDoc
     */
    public function purge(int $purgeFlags): int
    {
        return $this->producer->purge($purgeFlags);
    }

    /**
     * @inheritDoc
     */
    public function flush(int $timeoutMs = -1): int
    {
        return $this->producer->flush($timeoutMs);
    }

    /**
     * @inheritDoc
     */
    public function getMetadataForTopic(string $topicName, int $timeoutMs = 10000): RdKafkaMetadataTopic
    {
        $topic = $this->producer->newTopic($topicName);
        return $this->producer
            ->getMetadata(false, $topic, $timeoutMs)
            ->getTopics()
            ->current();
    }

    /**
     * @param string $topic
     * @return RdKafkaProducerTopic
     */
    private function getProducerTopicForTopic(string $topic): RdKafkaProducerTopic
    {
        if (!isset($this->producerTopics[$topic])) {
            $this->producerTopics[$topic] = $this->producer->newTopic($topic);
        }

        return $this->producerTopics[$topic];
    }
}
