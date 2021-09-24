<?php

declare(strict_types=1);

namespace Daalvand\Kafka\Consumer;

use Daalvand\Kafka\Exceptions\ConsumerAssignmentException;
use Daalvand\Kafka\Exceptions\ConsumerCommitException;
use Daalvand\Kafka\Exceptions\ConsumerConsumeException;
use Daalvand\Kafka\Exceptions\ConsumerEndOfPartitionException;
use Daalvand\Kafka\Exceptions\ConsumerRequestException;
use Daalvand\Kafka\Exceptions\ConsumerSubscriptionException;
use Daalvand\Kafka\Exceptions\ConsumerTimeoutException;
use Daalvand\Kafka\Message\ConsumerMessageInterface;
use RdKafka\Metadata\Topic as RdKafkaMetadataTopic;
use RdKafka\TopicPartition as RdKafkaTopicPartition;
use RdKafka\Exception as RdKafkaException;

interface ConsumerInterface
{
    /**
     * Subscribes to all defined topics, if no partitions were set, subscribes to all partitions.
     * If partition(s) (and optionally offset(s)) were set, subscribes accordingly
     *
     * @return void
     * @throws ConsumerSubscriptionException
     */
    public function subscribe(): void;

    /**
     * Unsubscribes from the current subscription / assignment
     *
     * @return void
     * @throws ConsumerSubscriptionException
     */
    public function unsubscribe(): void;

    /**
     * Returns true if the consumer has subscribed to its topics, otherwise false
     * It is mandatory to call `subscribe` before `consume`
     *
     * @return boolean
     */
    public function isSubscribed(): bool;

    /**
     * Consumes a message and returns it
     * In cases of errors / timeouts a KafkaConsumerConsumeException is thrown
     *
     * @return ConsumerMessageInterface
     * @throws ConsumerConsumeException|ConsumerEndOfPartitionException
     * @throws ConsumerTimeoutException|ConsumerSubscriptionException
     */
    public function consume(): ConsumerMessageInterface;

    /**
     * Commits the offset to the broker for the given message(s)
     * This is a blocking function, checkout out commitAsync if you want to commit in a non blocking manner
     *
     * @param ConsumerMessageInterface|ConsumerMessageInterface[] $messages
     * @return void
     * @throws ConsumerCommitException
     */
    public function commit($messages): void;

    /**
     * Returns the configuration settings for this consumer instance as array
     *
     * @return string[]
     */
    public function getConfiguration(): array;


    /**
     * Queries the broker for metadata on a certain topic
     *
     * @param string $topicName
     * @param integer $timeoutMs
     * @return RdKafkaMetadataTopic
     * @throws RdKafkaException
     */
    public function getMetadataForTopic(string $topicName, int $timeoutMs = 10000): RdKafkaMetadataTopic;

    /**
     * Get the earliest offset for a certain timestamp for topic partitions
     *
     * @param array|RdKafkaTopicPartition[] $topicPartitions
     * @param integer                       $timeoutMs
     * @return array|RdKafkaTopicPartition[]
     */
    public function offsetsForTimes(array $topicPartitions, int $timeoutMs): array;

    /**
     * Queries the broker for the first offset of a given topic and partition
     *
     * @param string  $topic
     * @param integer $partition
     * @param integer $timeoutMs
     * @return integer
     */
    public function getFirstOffsetForTopicPartition(string $topic, int $partition, int $timeoutMs): int;

    /**
     * Queries the broker for the last offset of a given topic and partition
     *
     * @param string  $topic
     * @param integer $partition
     * @param integer $timeoutMs
     * @return integer
     */
    public function getLastOffsetForTopicPartition(string $topic, int $partition, int $timeoutMs): int;

    /**
     * Assigns a consumer to the given TopicPartition(s)
     *
     * @param string[]|RdKafkaTopicPartition[] $topicPartitions
     * @return void
     * @throws ConsumerAssignmentException
     */
    public function assign(array $topicPartitions): void;

    /**
     * Asynchronous version of commit (non blocking)
     *
     * @param ConsumerMessageInterface|ConsumerMessageInterface[] $messages
     * @return void
     * @throws ConsumerCommitException
     */
    public function commitAsync($messages): void;

    /**
     * Gets the current assignment for the consumer
     *
     * @return array|RdKafkaTopicPartition[]
     * @throws ConsumerAssignmentException
     */
    public function getAssignment(): array;

    /**
     * Gets the committed offset for a TopicPartition for the configured consumer group
     *
     * @param array|RdKafkaTopicPartition[] $topicPartitions
     * @param integer                       $timeoutMs
     * @return array|RdKafkaTopicPartition[]
     * @throws ConsumerRequestException
     */
    public function getCommittedOffsets(array $topicPartitions, int $timeoutMs): array;

    /**
     * Get current offset positions of the consumer
     *
     * @param array|RdKafkaTopicPartition[] $topicPartitions
     * @return array|RdKafkaTopicPartition[]
     */
    public function getOffsetPositions(array $topicPartitions): array;

    /**
     * Close the consumer connection
     *
     * @return void;
     */
    public function close(): void;

    /**
     * @return int
     */
    public function getTimeoutMs(): int;

    /**
     * @param int $timeoutMs
     * @return Consumer
     */
    public function setTimeoutMs(int $timeoutMs): self;

}
