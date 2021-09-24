<?php

declare(strict_types=1);

namespace Daalvand\Kafka\Exceptions;

class RebalanceException extends \Exception
{
    public const REBALANCE_EXCEPTION_MESSAGE = 'Error during rebalance of consumer';
}
