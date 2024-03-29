<?php

declare(strict_types=1);

namespace Daalvand\Kafka\Exceptions;

class ProducerException extends \Exception
{
    public const TIMEOUT_EXCEPTION_MESSAGE = 'Error message timed out: %s';
    public const UNEXPECTED_EXCEPTION_MESSAGE = 'Unexpected error during production: %s';
    public const NO_BROKER_EXCEPTION_MESSAGE = 'You must define at least one broker';
    public const UNSUPPORTED_MESSAGE_EXCEPTION_MESSAGE = 'Produce only supports messages of type %s';
}
