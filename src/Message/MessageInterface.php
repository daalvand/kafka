<?php

declare(strict_types=1);

namespace Daalvand\Kafka\Message;

interface MessageInterface
{

    /**
     * Returns the message key or null if the message doesn't have a body
     * @return mixed
     */
    public function getKey();

    /**
     * @return string
     */
    public function getTopicName(): string;

    /**
     * @return integer
     */
    public function getPartition(): int;

    /**
     * @return string[]|null
     */
    public function getHeaders(): ?array;

    /**
     * Returns the message body or null if the message doesn't have a body
     * @return mixed
     */
    public function getBody();

    /**
     * @param string $key
     * @return string|null
     */
    public function getHeader(string $key): ?string;
}
