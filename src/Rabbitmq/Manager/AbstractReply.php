<?php

namespace Maleficarum\Rabbitmq\Manager;

use Maleficarum\Command\AbstractCommand;

abstract class AbstractReply extends AbstractCommand
{
    public static function decode(string $json, array $messageHeaders = []): ?AbstractCommand
    {
        $data = \json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        // not a JSON structure
        if (!\is_array($data)) {
            throw new \InvalidArgumentException(sprintf('Incorrect command received - not a proper JSON. \%s::decode()', static::class));
        }

        // not a command
        if (!\array_key_exists('__type', $data)) {
            throw new \InvalidArgumentException(sprintf('Incorrect command received - missing type. \%s::decode()', static::class));
        }

        // not a supported command (no command object)
        $commandClass = '\\' . $data['__type'];
        if (!\class_exists($commandClass, true)) {
            throw new \InvalidArgumentException(sprintf('Incorrect command received - unsupported type. \%s::decode()', static::class));
        }

        $command = new $commandClass();
        $command->fromJSON($json);

        return $command;
    }
}
