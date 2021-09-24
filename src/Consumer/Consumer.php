<?php

declare(strict_types=1);

namespace Daalvand\Kafka\Consumer;

use Daalvand\Kafka\Exceptions\ConsumerAssignmentException;
use Daalvand\Kafka\Exceptions\ConsumerCommitException;
use Daalvand\Kafka\Exceptions\ConsumerEndOfPartitionException;
use Daalvand\Kafka\Exceptions\ConsumerRequestException;
use Daalvand\Kafka\Exceptions\ConsumerSubscriptionException;
use Daalvand\Kafka\Exceptions\ConsumerTimeoutException;
use Daalvand\Kafka\Message\ConsumerMessageInterface;
use Daalvand\Kafka\Conf\Config;
use Daalvand\Kafka\Exceptions\ConsumerConsumeException;
use Daalvand\Kafka\Message\ConsumerMessage;
use RdKafka\Exception as RdKafkaException;
use RdKafka\KafkaConsumer as RdKafkaConsumer;
use RdKafka\Metadata\Topic as RdKafkaMetadataTopic;
use RdKafka\Message as RdKafkaMessage;
use RdKafka\TopicPartition as RdKafkaTopicPartition;

class Consumer implements ConsumerInterface
{
    protected Config $kafkaConfiguration;
    protected bool $subscribed = false;
    protected RdKafkaConsumer $consumer;
    protected int $timeoutMs = -1;


    /**
     * @param RdKafkaConsumer $consumer
     * @param Config $config
     */
    public function __construct(
        RdKafkaConsumer $consumer,
        Config $config
    ) {
        $this->consumer = $consumer;
        $this->kafkaConfiguration = $config;
    }

    /**
     * @inheritDoc
     */
    public function isSubscribed(): bool
    {
        return $this->subscribed;
    }

    /**
     * @inheritDoc
     */
    public function getConfiguration(): array
    {
        return $this->kafkaConfiguration->dump();
    }

    /**
     * @inheritDoc
     */
    public function consume(): ConsumerMessageInterface
    {
        if (!$this->isSubscribed()) {
            throw new ConsumerSubscriptionException('consumer topics not be subscribed!!!');
        }

        $rdKafkaMessage = $this->kafkaConsume();
        if ($rdKafkaMessage === null) {
            throw new ConsumerEndOfPartitionException(
                rd_kafka_err2str(RD_KAFKA_RESP_ERR__PARTITION_EOF),
                RD_KAFKA_RESP_ERR__PARTITION_EOF
            );
        }

        switch ($rdKafkaMessage->err) {
            case RD_KAFKA_RESP_ERR_NO_ERROR:
                return $this->getConsumerMessage($rdKafkaMessage);
            case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                throw new ConsumerEndOfPartitionException($rdKafkaMessage->errstr(), $rdKafkaMessage->err);
            case RD_KAFKA_RESP_ERR__TIMED_OUT:
                throw new ConsumerTimeoutException($rdKafkaMessage->errstr(), $rdKafkaMessage->err);
            default:
                throw new ConsumerConsumeException($rdKafkaMessage->errstr(), $rdKafkaMessage->err);
        }
    }


    /**
     * @inheritDoc
     */
    public function getTimeoutMs(): int
    {
        return $this->timeoutMs;
    }

    /**
     * @inheritDoc
     */
    public function setTimeoutMs(int $timeoutMs): self
    {
        $this->timeoutMs = $timeoutMs;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getMetadataForTopic(string $topicName, int $timeoutMs = 10000): RdKafkaMetadataTopic
    {
        $topic = $this->consumer->newTopic($topicName);
        return $this->consumer
            ->getMetadata(
                false,
                $topic,
                $timeoutMs
            )
            ->getTopics()
            ->current();
    }

    /**
     * @inheritDoc
     */
    public function offsetsForTimes(array $topicPartitions, int $timeoutMs): array
    {
        return $this->consumer->offsetsForTimes($topicPartitions, $timeoutMs);
    }

    /**
     * @inheritDoc
     */
    public function getFirstOffsetForTopicPartition(string $topic, int $partition, int $timeoutMs): int
    {
        $lowOffset = 0;
        $highOffset = 0;

        $this->consumer->queryWatermarkOffsets($topic, $partition, $lowOffset, $highOffset, $timeoutMs);

        return $lowOffset;
    }

    /**
     * @inheritDoc
     */
    public function getLastOffsetForTopicPartition(string $topic, int $partition, int $timeoutMs): int
    {
        $lowOffset = 0;
        $highOffset = 0;

        $this->consumer->queryWatermarkOffsets($topic, $partition, $lowOffset, $highOffset, $timeoutMs);

        return $highOffset;
    }

    /**
     * @inheritDoc
     */
    public function subscribe(): void
    {
        $subscriptions = $this->getTopicSubscriptions();
        try {
            $assignments = $this->getTopicAssignments();
        }catch (RdKafkaException $e){
            throw new ConsumerSubscriptionException($e->getMessage(), $e->getCode(), $e);
        }

        if ($subscriptions !== [] && $assignments !== []) {
            throw new ConsumerSubscriptionException();
        }

        try {
            if ([] !== $subscriptions) {
                $this->consumer->subscribe($subscriptions);
            } else {
                $this->consumer->assign($assignments);
            }
            $this->subscribed = true;
        } catch (RdKafkaException $e) {
            throw new ConsumerSubscriptionException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function unsubscribe(): void
    {
        try {
            $this->consumer->unsubscribe();
            $this->subscribed = false;
        } catch (RdKafkaException $e) {
            throw new ConsumerSubscriptionException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function commit($messages): void
    {
        $this->commitMessages($messages);
    }

    /**
     * @inheritDoc
     */
    public function assign(array $topicPartitions): void
    {
        try {
            $this->consumer->assign($topicPartitions);
        } catch (RdKafkaException $e) {
            throw new ConsumerAssignmentException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @inheritDoc
     */
    public function commitAsync($messages): void
    {
        $this->commitMessages($messages, true);
    }

    /**
     * @inheritDoc
     */
    public function getAssignment(): array
    {
        try {
            return $this->consumer->getAssignment();
        } catch (RdKafkaException $e) {
            throw new ConsumerAssignmentException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @inheritDoc
     */
    public function getCommittedOffsets(array $topicPartitions, int $timeoutMs): array
    {
        try {
            return $this->consumer->getCommittedOffsets($topicPartitions, $timeoutMs);
        } catch (RdKafkaException $e) {
            throw new ConsumerRequestException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @inheritDoc
     */
    public function getOffsetPositions(array $topicPartitions): array
    {
        return $this->consumer->getOffsetPositions($topicPartitions);
    }

    /**
     * @inheritDoc
     */
    public function close(): void
    {
        $this->consumer->close();
    }

    /**
     * @return RdKafkaMessage|null
     * @throws ConsumerConsumeException
     */
    protected function kafkaConsume(): ?RdKafkaMessage
    {
        try {
            return $this->consumer->consume($this->timeoutMs);
        } catch (RdKafkaException $e) {
            throw new ConsumerConsumeException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param ConsumerMessageInterface|ConsumerMessageInterface[] $messages
     * @param boolean $asAsync
     * @return void
     * @throws ConsumerCommitException
     */
    private function commitMessages($messages, bool $asAsync = false): void
    {
        $messages = is_array($messages) ? $messages : [$messages];

        $offsetsToCommit = $this->getOffsetsToCommitForMessages($messages);

        try {
            if (true === $asAsync) {
                $this->consumer->commitAsync($offsetsToCommit);
            } else {
                $this->consumer->commit($offsetsToCommit);
            }
        } catch (RdKafkaException $e) {
            throw new ConsumerCommitException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param array|ConsumerMessageInterface[] $messages
     * @return array|RdKafkaTopicPartition[]
     */
    private function getOffsetsToCommitForMessages(array $messages): array
    {
        $offsetsToCommit = [];

        foreach ($messages as $message) {
            $topicPartition = sprintf('%s-%s', $message->getTopicName(), $message->getPartition());

            if (true === isset($offsetsToCommit[$topicPartition])) {
                if ($message->getOffset() + 1 > $offsetsToCommit[$topicPartition]) {
                    $offsetsToCommit[$topicPartition]->setOffset($message->getOffset() + 1);
                }
                continue;
            }

            $offsetsToCommit[$topicPartition] = new RdKafkaTopicPartition(
                $message->getTopicName(),
                $message->getPartition(),
                $message->getOffset() + 1
            );
        }

        return $offsetsToCommit;
    }

    /**
     * @return array|string[]
     */
    private function getTopicSubscriptions(): array
    {
        $subscriptions = [];

        foreach ($this->kafkaConfiguration->getTopicSubscriptions() as $topicSubscription) {
            if (
                $topicSubscription->getPartitions() !== [] ||
                $topicSubscription->getOffset() !== ConsumerBuilderInterface::OFFSET_STORED
            ) {
                continue;
            }
            $subscriptions[] = $topicSubscription->getTopicName();
        }

        return $subscriptions;
    }

    /**
     * @return array|RdKafkaTopicPartition[]
     * @throws RdKafkaException
     */
    private function getTopicAssignments(): array
    {
        $assignments = [];

        foreach ($this->kafkaConfiguration->getTopicSubscriptions() as $topicSubscription) {
            if (
                $topicSubscription->getPartitions() === [] &&
                $topicSubscription->getOffset() === ConsumerBuilderInterface::OFFSET_STORED
            ) {
                continue;
            }

            $offset = $topicSubscription->getOffset();
            $partitions = $topicSubscription->getPartitions();

            if ($partitions === []) {
                $partitions = $this->getAllTopicPartitions($topicSubscription->getTopicName());
            }

            foreach ($partitions as $partitionId) {
                $assignments[] = new RdKafkaTopicPartition($topicSubscription->getTopicName(), $partitionId, $offset);
            }
        }

        return $assignments;
    }

    /**
     * @param string $topic
     * @return int[]
     * @throws RdKafkaException
     */
    protected function getAllTopicPartitions(string $topic): array
    {
        $partitions = [];
        $topicMetadata = $this->getMetadataForTopic($topic);

        foreach ($topicMetadata->getPartitions() as $partition) {
            $partitions[] = $partition->getId();
        }

        return $partitions;
    }

    /**
     * @param RdKafkaMessage $message
     * @return ConsumerMessageInterface
     */
    protected function getConsumerMessage(RdKafkaMessage $message): ConsumerMessageInterface
    {
        return new ConsumerMessage(
            (string) $message->topic_name,
            (int) $message->partition,
            (int) $message->offset,
            (int) $message->timestamp,
            $message->payload,
            $message->key,
            (array) $message->headers,
        );
    }
}
