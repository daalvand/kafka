<?php

declare(strict_types=1);

namespace Daalvand\Kafka\Conf;
use Daalvand\Kafka\Consumer\TopicSubscription;
use RdKafka\Conf as RdKafkaConf;

class Config extends RdKafkaConf
{

    /**
     * @var string[]
     */
    protected array $brokers;

    /**
     * @var array|TopicSubscription[]
     */
    protected array $topicSubscriptions;
    /**
     * @param string[] $brokers
     * @param array|TopicSubscription[] $topicSubscriptions
     * @param mixed[] $config
     */
    public function __construct(array $brokers, array $topicSubscriptions, array $config = [])
    {
        parent::__construct();
        $this->brokers = $brokers;
        $this->topicSubscriptions = $topicSubscriptions;
        $this->initializeConfig($config);
    }

    /**
     * @return string[]
     */
    public function getBrokers(): array
    {
        return $this->brokers;
    }

    /**
     * @return array|TopicSubscription[]
     */
    public function getTopicSubscriptions(): array
    {
        return $this->topicSubscriptions;
    }

    /**
     * @return string[]
     */
    public function getConfiguration(): array
    {
        return $this->dump();
    }

    /**
     * @param mixed[] $config
     * @return void
     */
    protected function initializeConfig(array $config = []): void
    {
        foreach ($config as $name => $value) {
            if (is_scalar($value) === false) {
                continue;
            }

            if (is_bool($value) === true) {
                $value = true === $value ? 'true' : 'false';
            }

            $this->set($name, (string) $value);
        }

        $this->set('metadata.broker.list', implode(',', $this->getBrokers()));
    }
}
