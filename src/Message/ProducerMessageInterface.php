<?php

declare(strict_types=1);

namespace Daalvand\Kafka\Message;

interface ProducerMessageInterface extends MessageInterface
{
    /**
     * @param string|null $key
     * @return ProducerMessageInterface
     */
    public function withKey(?string $key): ProducerMessageInterface;

    /**
     * @param mixed $body
     * @return ProducerMessageInterface
     */
    public function withBody($body): ProducerMessageInterface;

    /**
     * @param string[]|null $headers
     * @return ProducerMessageInterface
     */
    public function withHeaders(?array $headers): ProducerMessageInterface;

    /**
     * @param string         $key
     * @param string|integer $value
     * @return ProducerMessageInterface
     */
    public function withHeader(string $key, $value): ProducerMessageInterface;
}
