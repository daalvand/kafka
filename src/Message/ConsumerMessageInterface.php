<?php

declare(strict_types=1);

namespace Daalvand\Kafka\Message;

interface ConsumerMessageInterface extends MessageInterface
{

    /**
     * @return integer
     */
    public function getOffset(): int;

    /**
     * @return integer
     */
    public function getTimestamp(): int;
}
