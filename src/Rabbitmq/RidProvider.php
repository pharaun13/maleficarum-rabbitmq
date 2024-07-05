<?php

namespace Maleficarum\Rabbitmq;

interface RidProvider
{
    public function getRid(): string;
}
