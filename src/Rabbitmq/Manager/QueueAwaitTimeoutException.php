<?php

namespace Maleficarum\Rabbitmq\Manager;

use Exception;

class QueueAwaitTimeoutException extends Exception
{
    public function __construct(string $queueName, $code = 0, \Throwable $previous = null)
    {
        parent::__construct(\sprintf('Awaiting on queue %s timed out.', $queueName), $code, $previous);
    }
}
