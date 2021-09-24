<?php

declare(strict_types=1);

namespace Daalvand\Kafka\Callback;

use Daalvand\Kafka\Exceptions\ProducerException;
use RdKafka\Producer as RdKafkaProducer;
use RdKafka\Message as RdKafkaMessage;

final class ProducerDeliveryReportCallback
{
    /**
     * @param RdKafkaProducer $producer
     * @param RdKafkaMessage  $message
     * @return void
     * @throws ProducerException
     */
    public function __invoke(RdKafkaProducer $producer, RdKafkaMessage $message)
    {
        if (RD_KAFKA_RESP_ERR_NO_ERROR === $message->err) {
            return;
        }

        throw new ProducerException($message->errstr(), $message->err);
    }
}
