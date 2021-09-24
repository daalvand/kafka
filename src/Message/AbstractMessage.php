<?php

declare(strict_types=1);

namespace Daalvand\Kafka\Message;

abstract class AbstractMessage implements MessageInterface
{
    protected ?string $key = null;
    protected string $topicName;
    protected int $partition;

    /**
     * @var mixed
     */
    protected $body;

    /**
     * @var string[]|null
     */
    protected ?array $headers = null;

    /**
     * @return string|null
     */
    public function getKey(): ?string
    {
        return $this->key;
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return string
     */
    public function getTopicName(): string
    {
        return $this->topicName;
    }

    /**
     * @return integer
     */
    public function getPartition(): int
    {
        return $this->partition;
    }

    /**
     * @return string[]|null
     */
    public function getHeaders(): ?array
    {
        return $this->headers;
    }


    /**
     * @param string $key
     * @return string|null
     */
    public function getHeader(string $key): ?string
    {
        return $this->headers[$key] ?? null;
    }
}
