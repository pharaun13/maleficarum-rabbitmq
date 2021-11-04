<?php


namespace Maleficarum\Rabbitmq\Injector;


interface HeaderInjector
{
    public function inject(array $commandHeaders): array;
}