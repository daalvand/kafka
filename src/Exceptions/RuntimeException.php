<?php


namespace Daalvand\Kafka\Exceptions;

use Exception;

class RuntimeException extends Exception
{
    public const UNABLE_FLUSH = 'Producer was unable to flush, messages might be lost!';
}
