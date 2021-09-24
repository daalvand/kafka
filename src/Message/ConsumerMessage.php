<?php

declare(strict_types=1);

namespace Daalvand\Kafka\Message;

final class ConsumerMessage extends AbstractMessage implements ConsumerMessageInterface
{
    private int $offset;
    private int $timestamp;


    /**
     * @param string $topicName
     * @param integer $partition
     * @param integer $offset
     * @param integer $timestamp
     * @param mixed $body
     * @param string[] $headers,
     * @param string|null $key
     */
    public function __construct(
        string $topicName,
        int $partition,
        int $offset,
        int $timestamp,
        $body,
        ?string $key = null,
        ?array $headers = null
    ) {
        $this->topicName = $topicName;
        $this->partition = $partition;
        $this->offset = $offset;
        $this->timestamp = $timestamp;
        $this->key = $key;
        $this->body = $body;
        $this->headers = $headers;
    }

    /**
     * @return integer
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * @return integer
     */
    public function getTimestamp(): int
    {
        return $this->timestamp;
    }
}
