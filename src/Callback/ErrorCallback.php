<?php

declare(strict_types=1);

namespace Daalvand\Kafka\Callback;

use Daalvand\Kafka\Exceptions\BrokerException;

final class ErrorCallback
{

    /**
     * @param mixed   $kafka
     * @param integer $errorCode
     * @param string  $reason
     * @return void
     * @throws BrokerException
     */
    public function __invoke($kafka, int $errorCode, string $reason)
    {
        // non fatal errors are retried by lib-rdkafka
        if (RD_KAFKA_RESP_ERR__FAIL !== $errorCode) {
            return;
        }

        throw new BrokerException($reason, $errorCode);
    }
}
