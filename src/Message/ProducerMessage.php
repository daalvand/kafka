<?php

declare(strict_types=1);

namespace Daalvand\Kafka\Message;

final class ProducerMessage extends AbstractMessage implements ProducerMessageInterface
{

    /**
     * @param string  $topicName
     * @param integer $partition
     */
    public function __construct(string $topicName, int $partition)
    {
        $this->topicName    = $topicName;
        $this->partition    = $partition;
    }


    /**
     * @param string|null $key
     * @return ProducerMessageInterface
     */
    public function withKey(?string $key): ProducerMessageInterface
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @param mixed $body
     * @return ProducerMessageInterface
     */
    public function withBody($body): ProducerMessageInterface
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @param string[]|null $headers
     * @return ProducerMessageInterface
     */
    public function withHeaders(?array $headers): ProducerMessageInterface
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * @param string         $key
     * @param string|integer $value
     * @return ProducerMessageInterface
     */
    public function withHeader(string $key, $value): ProducerMessageInterface
    {
        $this->headers[$key] = $value;
        return $this;
    }
}
