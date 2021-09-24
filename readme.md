# preview

* This package is for kafka consume and produce in laravel

# installation

## Install Kafka

Apache Kafka is need for many sections of our ecosystem

1. install librdkafka from [The Apache Kafka C/C++ client library](https://github.com/edenhill/librdkafka)

   for the ubuntu: `apt install librdkafka-dev`.

   for the centos: `yum install librdkafka-devel`

2. Then build php extension
   from [Manually Installing the extension](https://arnaud.le-blanc.net/php-rdkafka-doc/phpdoc/rdkafka.installation.manual.html)
     ```bash
     git clone https://github.com/arnaud-lb/php-rdkafka.git
     cd php-rdkafka
     phpize
     ./configure
     make all -j 5
     sudo make install
     ```
3. Then extension to `php.ini`

   `extension=rdkafka.so`

4. Then restart php-fpm service `service php-fpm restart`

## install package

1. run `composer require daalvand/kafka`

2 . publish provider:

### Laravel

* `php artisan vendor:publish --provider="Daalvand\Kafka\KafkaServiceProvider"`

### Lumen

* Add the service provider to bootstrap/app.php file:

```php
<?php
 $app->register(Daalvand\Kafka\KafkaServiceProvider::class);
```

* Copy the config file from `/vendor/daalvand/kafka/src/config` to `/config` directory. Then configure it
  in `/bootstrap/app.php` file:

```php
<?php

$app->configure("kafka");
```

# Usage

## Producer

```php
<?php
use Daalvand\Kafka\Message\ProducerMessage;
use Daalvand\Kafka\Facades\Producer;

$producer = Producer::withAdditionalBroker('localhost:9092')
    ->build();

$message = (new ProducerMessage('topic-name', 0))
            ->withKey('test-key')
            ->withBody('some test message payload')
            ->withHeaders(['header' => 'value']);

$producer->produce($message);
$producer->flush(-1);
```

## Consumer

```php
<?php

use Daalvand\Kafka\Facades\Consumer;
use Daalvand\Kafka\Exceptions\ConsumerConsumeException;
use Daalvand\Kafka\Exceptions\ConsumerEndOfPartitionException;
use Daalvand\Kafka\Exceptions\ConsumerTimeoutException;

$consumer = Consumer::withAdditionalConfig([
            'compression.codec'       => 'lz4',
            'auto.commit.interval.ms' => 500
    ])
    ->withAdditionalBroker('kafka:9092')
    ->withConsumerGroup('testGroup')
    ->withAdditionalSubscription('test-topic')
    ->build();

$consumer->subscribe();

while (true) {
    try {
        $message = $consumer->consume();
        // your business logic
        $consumer->commit($message);
    } catch (ConsumerTimeoutException $e) {
        //no messages were read in a given time
    } catch (ConsumerEndOfPartitionException $e) {
        //only occurs if enable.partition.eof is true (default: false)
    } catch (ConsumerConsumeException $e) {
        // Failed
    }
}
```

* `auto.offset.reset` option is (largest, smallest) valid values for kafka by older version than 0.9 and for after .9
  is (earliest, latest)