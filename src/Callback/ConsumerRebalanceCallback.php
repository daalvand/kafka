<?php

declare(strict_types=1);

namespace Daalvand\Kafka\Callback;

use RdKafka\KafkaConsumer as RdKafkaConsumer;
use Daalvand\Kafka\Exceptions\RebalanceException;
use RdKafka\Exception as RdKafkaException;
use RdKafka\TopicPartition as RdKafkaTopicPartition;

final class ConsumerRebalanceCallback
{

    /**
     * @param RdKafkaConsumer $consumer
     * @param integer         $errorCode
     * @param array|RdKafkaTopicPartition[]|null      $partitions
     * @return void
     * @throws RebalanceException
     */
    public function __invoke(RdKafkaConsumer $consumer, int $errorCode, array $partitions = null)
    {
        try {
            if($errorCode === RD_KAFKA_RESP_ERR__ASSIGN_PARTITIONS){
                $consumer->assign($partitions);
            }else{
                $consumer->assign(null);
            }

        } catch (RdKafkaException $e) {
            throw new RebalanceException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
