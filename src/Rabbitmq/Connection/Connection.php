<?php
/**
 * This class represents a connection to a RabbitMQ broker.
 */
declare (strict_types=1);

namespace Maleficarum\Rabbitmq\Connection;

class Connection {
    
    /* ------------------------------------ Class Property START --------------------------------------- */
    
    /**
     * Internal storage for a AMQP connection object.
     *
     * @var \PhpAmqpLib\Connection\AMQPStreamConnection|null
     */
    private $connection = null;

    /**
     * Internal storage for queue name
     *
     * @var string
     */
    private $queueName;

    /**
     * Internal storage for host
     *
     * @var string
     */
    private $host;

    /**
     * Internal storage for port
     *
     * @var int
     */
    private $port;

    /**
     * Internal storage for username
     *
     * @var string
     */
    private $username;

    /**
     * Internal storage for password
     *
     * @var string
     */
    private $password;

    /* ------------------------------------ Class Property END ----------------------------------------- */

    /* ------------------------------------ Magic methods START ---------------------------------------- */
    /**
     * Connection constructor.
     *
     * @param string $queueName
     * @param string $host
     * @param int $port
     * @param string $username
     * @param string $password
     */
    public function __construct(string $queueName, string $host, int $port, string $username, string $password) {
        $this->queueName = $queueName;
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Perform connection cleanup.
     */
    public function __destruct() {
        $this->close();
    }
    /* ------------------------------------ Magic methods END ------------------------------------------ */

    /* ------------------------------------ Class Methods START ---------------------------------------- */
    
    /**
     * Send a worker command to the broker.
     *
     * @param \Maleficarum\Command\AbstractCommand $command
     * @return \Maleficarum\Rabbitmq\Connection\Connection
     */
    public function addCommand(\Maleficarum\Command\AbstractCommand $command) : \Maleficarum\Rabbitmq\Connection\Connection {
        is_null($this->getConnection()) and $this->init();

        $message = $this->getMessage($command);
        $channel = $this->getChannel();
        $channel->basic_publish($message, '', $this->queueName);
        $channel->close();

        return $this;
    }

    /**
     * Send a batch of worker commands (much better performance when sending multiple commands)
     *
     * @param array|\Maleficarum\Command\AbstractCommand[] $commands
     * @return \Maleficarum\Rabbitmq\Connection\Connection
     * @throws \InvalidArgumentException
     */
    public function addCommands(array $commands) : \Maleficarum\Rabbitmq\Connection\Connection {
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
            $channel->batch_basic_publish($message, '', $this->queueName);
        }

        $channel->publish_batch();
        $channel->close();

        return $this;
    }

    /**
     * Add raw message to the queue
     *
     * @param string $message
     * @return \Maleficarum\Rabbitmq\Connection\Connection
     */
    public function addRawMessage(string $message) : \Maleficarum\Rabbitmq\Connection\Connection {
        is_null($this->getConnection()) and $this->init();

        $message = \Maleficarum\Ioc\Container::get('PhpAmqpLib\Message\AMQPMessage', [$message, ['delivery_mode' => 2]]);
        $channel = $this->getChannel();
        $channel->basic_publish($message, '', $this->queueName);
        $channel->close();

        return $this;
    }

    /**
     * Initialize this object.
     *
     * @return \Maleficarum\Rabbitmq\Connection\Connection
     */
    public function init() : \Maleficarum\Rabbitmq\Connection\Connection {
        $connection = \Maleficarum\Ioc\Container::get('PhpAmqpLib\Connection\AMQPStreamConnection', [$this->host, $this->port, $this->username, $this->password]);
        $this->setConnection($connection);

        return $this;
    }

    /**
     * Close connection and channel.
     *
     * @return \Maleficarum\Rabbitmq\Connection\Connection
     */
    public function close() : \Maleficarum\Rabbitmq\Connection\Connection {
        $this->getConnection() and $this->getConnection()->close();

        return $this;
    }

    /**
     * Fetch the communications channel. This will be useful when executing chitinous command fetching in worker scripts.
     *
     * @param string $id
     * @return \PhpAmqpLib\Channel\AMQPChannel
     */
    public function getChannel(string $id = null) : \PhpAmqpLib\Channel\AMQPChannel {
        return $this->getConnection()->channel($id);
    }

    /**
     * Get message
     *
     * @param \Maleficarum\Command\AbstractCommand $command
     * @return \PhpAmqpLib\Message\AMQPMessage
     */
    private function getMessage(\Maleficarum\Command\AbstractCommand $command) : \PhpAmqpLib\Message\AMQPMessage {
        return \Maleficarum\Ioc\Container::get('PhpAmqpLib\Message\AMQPMessage', [$command->toJSON(), ['delivery_mode' => 2]]);
    }
    
    /* ------------------------------------ Class Methods END ------------------------------------------ */

    /* ------------------------------------ Setters & Getters START ------------------------------------ */
    
    /**
     * Set current AMQP queue connection.
     *
     * @param \PhpAmqpLib\Connection\AMQPStreamConnection $connection
     *
     * @return \Maleficarum\Rabbitmq\Connection
     */
    public function setConnection(\PhpAmqpLib\Connection\AMQPStreamConnection $connection) : \Maleficarum\Rabbitmq\Connection\Connection {
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
