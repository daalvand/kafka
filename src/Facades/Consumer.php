<?php
declare(strict_types = 1);

namespace Daalvand\Kafka\Facades;

use Daalvand\Kafka\Consumer\ConsumerBuilderInterface;
use Daalvand\Kafka\Consumer\ConsumerInterface;
use Illuminate\Support\Facades\Facade;

/**
 * Class Consumer
 * @method static array getBrokers();
 * @method static ConsumerBuilderInterface setBrokers(array $brokers);
 * @method static ConsumerBuilderInterface withAdditionalBroker(string $broker);
 * @method static ConsumerBuilderInterface withAdditionalSubscription(string $topicName, array $partitions = [], int $offset = ConsumerBuilderInterface::OFFSET_STORED)
 * @method static ConsumerBuilderInterface withSubscription(string $topicName, array $partitions = [], int $offset = ConsumerBuilderInterface::OFFSET_STORED)
 * @method static ConsumerBuilderInterface withAdditionalConfig(array $config);
 * @method static ConsumerBuilderInterface withConsumerGroup(string $consumerGroup);
 * @method static ConsumerBuilderInterface withConsumerType(string $consumerType);
 * @method static ConsumerBuilderInterface withErrorCallback(callable $errorCallback);
 * @method static ConsumerBuilderInterface withRebalanceCallback(callable $rebalanceCallback);
 * @method static ConsumerBuilderInterface withConsumeCallback(callable $consumeCallback);
 * @method static ConsumerBuilderInterface withOffsetCommitCallback(callable $offsetCommitCallback);
 * @method static ConsumerBuilderInterface withLogCallback(callable $logCallback);
 * @method static ConsumerInterface build();
 * @package Daalvand\Kafka\Facades
 */

class Consumer extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'kafka-consumer';
    }
}
