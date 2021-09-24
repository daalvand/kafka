<?php

namespace Daalvand\Kafka\Producer;

use Daalvand\Kafka\Exceptions\RuntimeException;
use Daalvand\Kafka\Message\ProducerMessageInterface;
use RdKafka\Exception as RdKafkaException;
use RdKafka\Metadata\Topic as RdKafkaMetadataTopic;

interface ProducerInterface
{

    /**
     * Produces a message to the topic and partition defined in the message
     * @param ProducerMessageInterface $message
     * @param boolean $autoPoll
     * @param integer $pollTimeoutMs
     * @return void
     * @throws RuntimeException
     */
    public function produce(ProducerMessageInterface $message, bool $autoPoll = true, int $pollTimeoutMs = 0): void;

    /**
     * Produces a message to the topic and partition defined in the message
     * Wait for the message to event to arrive before continuing (blocking)
     * @param ProducerMessageInterface $message
     * @return void
     * @throws RuntimeException
     */
    public function syncProduce(ProducerMessageInterface $message): void;

    /**
     * Poll for producer event, pass 0 for non-blocking, pass -1 to block until an event arrives
     *
     * @param integer $timeoutMs
     * @return void
     */
    public function poll(int $timeoutMs = 0): void;

    /**
     * Poll for producer events until the number of $queueSize events remain
     *
     * @param integer $timeoutMs
     * @param integer $queueSize
     * @return void
     */
    public function pollUntilQueueSizeReached(int $timeoutMs = 0, int $queueSize = 0): void;

    /**
     * Purge producer messages that are in flight
     *
     * @param integer $purgeFlags
     * @return integer
     */
    public function purge(int $purgeFlags): int;

    /**
     * Wait until all outstanding produce requests are completed
     *
     * @param integer $timeoutMs
     * @return integer
     */
    public function flush(int $timeoutMs = -1): int;

    /**
     * Queries the broker for metadata on a certain topic
     *
     * @param string $topicName
     * @param integer $timeoutMs
     * @return RdKafkaMetadataTopic
     * @throws RdKafkaException
     */
    public function getMetadataForTopic(string $topicName, int $timeoutMs = 10000): RdKafkaMetadataTopic;
}
