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
     * Internal storege for a AMPQ connection object.
     *
     * @var \PhpAmqpLib\Connection\AMQPConnection|null
     */
    private $connection = null;

    /**
     * Send a worker command to the broker.
     *
     * @param \Maleficarum\Worker\Command\AbstractCommand $cmd
     *
     * @return \Maleficarum\Rabbitmq\Connection
     */
    public function addCommand(\Maleficarum\Worker\Command\AbstractCommand $cmd) {
        is_null($this->getConnection()) and $this->init();

        $channel = $this->getChannel();
        $channel->basic_publish(
            \Maleficarum\Ioc\Container::get('PhpAmqpLib\Message\AMQPMessage', [$cmd->toJSON(), ['delivery_mode' => 2]]),
            '',
            $this->getConfig()['queue']['commands']['queue-name']
        );
        $channel->close();

        return $this;
    }


    /**
     * Send a batch of worker commands (much better performance when sending multiple commands)
     *
     * @param array $cmds
     *
     * @return \Maleficarum\Rabbitmq\Connection
     * @throws \InvalidArgumentException
     */
    public function addCommands(array $cmds) {
        is_null($this->getConnection()) and $this->init();

        // validate commands
        if (count($cmds) < 1) {
            throw new \InvalidArgumentException('Expected a nonempty array of commands. \Maleficarum\Rabbitmq\Connection::addCommands()');
        }

        foreach ($cmds as $cmd) {
            if (!$cmd instanceof \Maleficarum\Worker\Command\AbstractCommand) {
                throw new \InvalidArgumentException('Not a valid command. \Maleficarum\Rabbitmq\Connection::addCommands()');
            }
        }

        // send commands		
        $channel = $this->getChannel();
        foreach ($cmds as $cmd) {
            $channel->batch_basic_publish(
                \Maleficarum\Ioc\Container::get('PhpAmqpLib\Message\AMQPMessage', [$cmd->toJSON(), ['delivery_mode' => 2]]),
                '',
                $this->getConfig()['queue']['commands']['queue-name']
            );
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
    public function init() {
        $this->setConnection(\Maleficarum\Ioc\Container::get('PhpAmqpLib\Connection\AMQPConnection'));

        return $this;
    }

    /**
     * Perform connection cleanup.
     */
    public function __destruct() {
        $this->close();
    }

    /**
     * Close connection and channel.
     *
     * @return \Maleficarum\Rabbitmq\Connection
     */
    public function close() {
        $this->getConnection() and $this->getConnection()->close();

        return $this;
    }

    /**
     * Fetch the communications channel. This will be useful when executing chitinous command fetching in worker scripts.
     *
     * @param mixed $id
     *
     * @return \PhpAmqpLib\Channel\AMQPChannel
     */
    public function getChannel($id = null) {
        return $this->getConnection()->channel($id);
    }

    /**
     * Set current amgp queue connection.
     *
     * @param \PhpAmqpLib\Connection\AMQPConnection $connection
     *
     * @return \Maleficarum\Rabbitmq\Connection
     */
    public function setConnection(\PhpAmqpLib\Connection\AMQPConnection $connection) {
        $this->connection = $connection;

        return $this;
    }

    /**
     * Fetch current amqp queue connection.
     *
     * @return \PhpAmqpLib\Connection\AMQPConnection
     */
    private function getConnection() {
        return $this->connection;
    }
}
