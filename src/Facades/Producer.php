<?php
declare(strict_types = 1);

namespace Daalvand\Kafka\Facades;

use Daalvand\Kafka\Producer\ProducerBuilderInterface;
use Daalvand\Kafka\Producer\ProducerInterface;
use Illuminate\Support\Facades\Facade;

/**
 * Class Producer
 * @method static ProducerInterface build();
 * @method static array getBrokers();
 * @method static ProducerBuilderInterface setBrokers(array $brokers);
 * @method static ProducerBuilderInterface withAdditionalBroker(string $broker);
 * @method static ProducerBuilderInterface withAdditionalConfig(array $config);
 * @method static ProducerBuilderInterface withDeliveryReportCallback(callable $deliveryReportCallback);
 * @method static ProducerBuilderInterface withErrorCallback(callable $errorCallback);
 */
class Producer extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'kafka-producer';
    }
}
