<?php


namespace Maleficarum\Rabbitmq\Injector;


use Maleficarum\ContextTracing\Carrier\Amqp\AmqpHeader;
use Maleficarum\ContextTracing\ContextTracker;

class ContextTrackerHeaderInjector implements HeaderInjector
{
    public function inject(array $commandHeaders): array
    {
        return (new AmqpHeader())->inject(ContextTracker::getTracer(), $commandHeaders);
    }

}