<?php
/**
 * This class represents a connection to a RabbitMQ broker.
 */

namespace Maleficarum\Rabbitmq;

class Connection
{
    /**
     * Use \Maleficarum\Config\Dependant functionality.
     *
     * @trait
     */
    use \Maleficarum\Config\Dependant;

    /**
     * Internal storage for a AMQP connection object.
     *
     * @var \PhpAmqpLib\Connection\AMQPStreamConnection|null
     */
    private $connection = null;

    /* ------------------------------------ Magic methods START ---------------------------------------- */
    /**
     * Perform connection cleanup.
     */
    public function __destruct() {
        $this->close();
    }
    /* ------------------------------------ Magic methods END ------------------------------------------ */

    /* ------------------------------------ Connection methods START ----------------------------------- */
    /**
     * Send a worker command to the broker.
     *
     * @param \Maleficarum\Command\AbstractCommand $command
     *
     * @return \Maleficarum\Rabbitmq\Connection
     */
    public function addCommand(\Maleficarum\Command\AbstractCommand $command) : \Maleficarum\Rabbitmq\Connection {
        is_null($this->getConnection()) and $this->init();

        $message = $this->getMessage($command);
        $channel = $this->getChannel();
        $channel->basic_publish($message, '', $this->getConfig()['queue']['commands']['queue-name']);
        $channel->close();

        return $this;
    }

    /**
     * Send a batch of worker commands (much better performance when sending multiple commands)
     *
     * @param array|\Maleficarum\Command\AbstractCommand[] $commands
     *
     * @return \Maleficarum\Rabbitmq\Connection
     * @throws \InvalidArgumentException
     */
    public function addCommands(array $commands) : \Maleficarum\Rabbitmq\Connection {
        is_null($this->getConnection()) and $this->init();

        // validate commands
        if (count($commands) < 1) {
            throw new \InvalidArgumentException(sprintf('Expected a nonempty array of commands. \%s::addCommands()', static::class));
        }

        foreach ($commands as $command) {
            if (!$command instanceof \Maleficarum\Command\AbstractCommand) {
                throw new \InvalidArgumentException(sprintf('Not a valid command. \%s::addCommands()', static::class));
            }
        }

        // send commands
        $channel = $this->getChannel();
        foreach ($commands as $command) {
            $message = $this->getMessage($command);
            $channel->batch_basic_publish($message, '', $this->getConfig()['queue']['commands']['queue-name']);
        }

        $channel->publish_batch();
        $channel->close();

        return $this;
    }

    /**
     * Initialize this object.
     *
     * @return \Maleficarum\Rabbitmq\Connection
     */
    public function init() : \Maleficarum\Rabbitmq\Connection {
        $connection = \Maleficarum\Ioc\Container::get('PhpAmqpLib\Connection\AMQPConnection');
        $this->setConnection($connection);

        return $this;
    }

    /**
     * Close connection and channel.
     *
     * @return \Maleficarum\Rabbitmq\Connection
     */
    public function close() : \Maleficarum\Rabbitmq\Connection {
        $this->getConnection() and $this->getConnection()->close();

        return $this;
    }

    /**
     * Fetch the communications channel. This will be useful when executing chitinous command fetching in worker scripts.
     *
     * @param string|null $id
     *
     * @return \PhpAmqpLib\Channel\AMQPChannel
     */
    public function getChannel(string $id = null) : \PhpAmqpLib\Channel\AMQPChannel {
        return $this->getConnection()->channel($id);
    }

    /**
     * Get message
     *
     * @param \Maleficarum\Command\AbstractCommand $command
     *
     * @return \PhpAmqpLib\Message\AMQPMessage
     */
    private function getMessage(\Maleficarum\Command\AbstractCommand $command) : \PhpAmqpLib\Message\AMQPMessage {
        return \Maleficarum\Ioc\Container::get('PhpAmqpLib\Message\AMQPMessage', [$command->toJSON(), ['delivery_mode' => 2]]);
    }
    /* ------------------------------------ Connection methods END ------------------------------------- */

    /* ------------------------------------ Setters & Getters START ------------------------------------ */
    /**
     * Set current AMQP queue connection.
     *
     * @param \PhpAmqpLib\Connection\AMQPStreamConnection $connection
     *
     * @return \Maleficarum\Rabbitmq\Connection
     */
    public function setConnection(\PhpAmqpLib\Connection\AMQPStreamConnection $connection) : \Maleficarum\Rabbitmq\Connection {
        $this->connection = $connection;

        return $this;
    }

    /**
     * Fetch current AMQP queue connection.
     *
     * @return \PhpAmqpLib\Connection\AMQPStreamConnection|null
     */
    private function getConnection() {
        return $this->connection;
    }
    /* ------------------------------------ Setters & Getters END -------------------------------------- */
}
