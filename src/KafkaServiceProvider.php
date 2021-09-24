<?php


namespace Daalvand\Kafka;

use Daalvand\Kafka\Producer\ProducerBuilder;
use Daalvand\Kafka\Consumer\ConsumerBuilder;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class KafkaServiceProvider extends BaseServiceProvider
{
    public function register()
    {
        $this->app->singleton('kafka-producer', function () {
            return new ProducerBuilder();
        });

        $this->app->singleton('kafka-consumer', function () {
            return new ConsumerBuilder();
        });
    }
}
