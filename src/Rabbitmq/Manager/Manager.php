<?php
/**
 * This class is responsible for maintaining a registry of active rabbitmq connections that will be shared across multiple objects.
 */
declare (strict_types=1);

namespace Maleficarum\Rabbitmq\Manager;

class Manager {
    /* ------------------------------------ Class Property START --------------------------------------- */

    /**
     * Connection modes:
     *  - CON_MODE_PERSISTENT - the connection will be considered PERSISTENT - the first call that uses the connection will establish it and the connection will remain established indefinitely
     *  - CON_MODE_TRANSIENT - the connection will be considered TRANSIENT - each call that uses the connection will establish it and the connection will terminate after the call
     */
    const CON_MODE_PERSISTENT = 1;
    const CON_MODE_TRANSIENT = 2;

    /**
     * Test prefix used for test mode connectionIdentifier creation.
     */
    const TEST_PREFIX = 'test_';
    
    /**
     * Internal storage for available RabbitMQ connections. 
     * @var array 
     */
    private $connections = [];

    /**
     * A list of connection ids that allow for accepting incoming commands.
     * @var array 
     */
    private $sources = [];
    
    /* ------------------------------------ Class Property END ----------------------------------------- */
    
    /* ------------------------------------ Class Methods START ---------------------------------------- */

    /**
     * Add a new connection to the connection pool.
     * 
     * @param \Maleficarum\Rabbitmq\Connection\Connection $connection
     * @param string $identifier
     * @param int $mode
     * @param int $priority
     * @throws \InvalidArgumentException
     * @return \Maleficarum\Rabbitmq\Manager\Manager
     */
    public function addConnection(\Maleficarum\Rabbitmq\Connection\Connection $connection, string $identifier, int $mode, int $priority = 0) : \Maleficarum\Rabbitmq\Manager\Manager {
        // check if mode does not indicate both transient and persistent connection at the same time
        if (($mode & self::CON_MODE_PERSISTENT) && ($mode & self::CON_MODE_TRANSIENT)) throw new \InvalidArgumentException(sprintf('Invalid connection mode specified - the connection cannot be both transient and persistent. %s()', __METHOD__));
        
        // check if the connection is defined as either transient or persistent - at least one is obligatory
        if (!($mode & self::CON_MODE_PERSISTENT) && !($mode & self::CON_MODE_TRANSIENT)) throw new \InvalidArgumentException(sprintf('Invalid connection mode specified - the connection cannot be neither transient nor persistent. %s()', __METHOD__));
        
        // detect identifier duplication
        if (array_key_exists($identifier, $this->connections)) throw new \InvalidArgumentException(sprintf('Duplicate connection identifier. %s()', __METHOD__));
        
        // validate priority value
        if ($priority < 0) throw new \InvalidArgumentException(sprintf('Invalid connection priority - non-negative integer expected. %s', __METHOD__));
        
        // create the connection definition structure based on the input parameters.
        $this->connections[$identifier] = [
            'mode' => $mode & self::CON_MODE_PERSISTENT ? 'persistent' : 'transient',
            'connection' => $connection,
            'priority' => $priority
        ];
        
        // add connection identifier to the list of sources if the provided mode allows it
        ($mode & self::CON_MODE_PERSISTENT) and $this->sources[$identifier] = $identifier;
        
        return $this;
    }

    /**
     * Add a new command to a specified connection.
     * 
     * @param \Maleficarum\Command\AbstractCommand $command
     * @param string $connectionIdentifier
     * @param string $exchangeName
     *
     * @throws \InvalidArgumentException
     *
     * @return \Maleficarum\Rabbitmq\Manager\Manager
     */
    public function addCommand(\Maleficarum\Command\AbstractCommand $command, string $connectionIdentifier, string $exchangeName = '') : \Maleficarum\Rabbitmq\Manager\Manager {
        // set test connectionIdentifier
        $connectionIdentifier = $this->getConnectionIdentifier($command, $connectionIdentifier);

        // check if the specified connection identifier exists
        if (!array_key_exists($connectionIdentifier, $this->connections)) throw new \InvalidArgumentException(sprintf('Provided connection identifier does not exist. %s', __METHOD__));
        
        // recover the specified connection for internal storage
        $connection = $this->connections[$connectionIdentifier]['connection'];

        // initialise the connection if necessary
        $connection->connect();

        // send the command to the message broker
        $message = \Maleficarum\Ioc\Container::get('PhpAmqpLib\Message\AMQPMessage', [$command->toJSON(), ['delivery_mode' => 2]]);
        $channel = $connection->getChannel();
        $channel->basic_publish($message, $exchangeName, $connection->getQueueName());
        $channel->close();
        
        // close the connection if it's in transient mode
        'transient' === $this->connections[$connectionIdentifier]['mode'] and $connection->disconnect(); 
        
        return $this;
    }

    /**
     * Add a batch of comments to a specified connection. (much better performance when sending multiple commands)
     * 
     * @param array $commands
     * @param string $connectionIdentifier
     * @throws \InvalidArgumentException
     * @return Manager
     */
    public function addCommands(array $commands, string $connectionIdentifier, string $exchangeName = '') : \Maleficarum\Rabbitmq\Manager\Manager {
        // validate commands - set count
        if (count($commands) < 1) throw new \InvalidArgumentException(sprintf('Expected a nonempty array of commands. \%s()', __METHOD__));

        // set test connectionIdentifier
        $connectionIdentifier = $this->getConnectionIdentifier($commands[0], $connectionIdentifier);

        // check if the specified connection identifier exists
        if (!array_key_exists($connectionIdentifier, $this->connections)) throw new \InvalidArgumentException(sprintf('Provided connection identifier does not exist. %s', __METHOD__));

        // recover the specified connection for internal storage
        $connection = $this->connections[$connectionIdentifier]['connection'];

        // initialise the connection if necessary
        $connection->connect();

        // validate commands - entity type
        foreach ($commands as $command) {
            if (!$command instanceof \Maleficarum\Command\AbstractCommand) {
                throw new \InvalidArgumentException(sprintf('Not a valid command. \%s()', __METHOD__));
            }
        }

        // send commands
        $channel = $connection->getChannel();
        foreach ($commands as $command) {
            $message = \Maleficarum\Ioc\Container::get('PhpAmqpLib\Message\AMQPMessage', [$command->toJSON(), ['delivery_mode' => 2]]);
            $channel->batch_basic_publish($message, $exchangeName, $connection->getQueueName());
        }
        $channel->publish_batch();
        $channel->close();

        // close the connection if it's in transient mode
        'transient' === $this->connections[$connectionIdentifier]['mode'] and $connection->disconnect();

        return $this;
    }

    /**
     * Add raw message to a specified connection.
     *
     * @param string $message
     * @param string $connectionIdentifier
     * @throws \InvalidArgumentException
     * @return \Maleficarum\Rabbitmq\Connection\Connection
     */
    public function addRawMessage(string $message, string $connectionIdentifier) : \Maleficarum\Rabbitmq\Manager\Manager {
        // check if the specified connection identifier exists
        if (!array_key_exists($connectionIdentifier, $this->connections)) throw new \InvalidArgumentException(sprintf('Provided connection identifier does not exist. %s', __METHOD__));

        // recover the specified connection for internal storage
        $connection = $this->connections[$connectionIdentifier]['connection'];

        // initialise the connection if necessary
        $connection->connect();

        // send the message to the message broker
        $message = \Maleficarum\Ioc\Container::get('PhpAmqpLib\Message\AMQPMessage', [$message, ['delivery_mode' => 2]]);
        $channel = $connection->getChannel();
        $channel->basic_publish($message, '', $connection->getQueueName());
        $channel->close();

        // close the connection if it's in transient mode
        'transient' === $this->connections[$connectionIdentifier]['mode'] and $connection->disconnect();

        return $this;
    }

    /**
     * Fetch a list of available source connections. This ensures that returned connections are active.
     * 
     * @return array
     */
    public function fetchSources() : array {
        $sources = [];

        foreach ($this->sources as $connectionIdentifier) {
            array_key_exists($this->connections[$connectionIdentifier]['priority'], $sources) or $sources[$this->connections[$connectionIdentifier]['priority']] = [];
            $sources[$this->connections[$connectionIdentifier]['priority']][] = $this->connections[$connectionIdentifier]['connection']->connect();
        }
        
        ksort($sources);
        return $sources;
    }

    /**
     * Fetch a specific source connection. The connection will be activated if necessary.
     * 
     * @param string $connectionIdentifier
     * @throws \InvalidArgumentException
     * @return \Maleficarum\Rabbitmq\Connection\Connection
     */
    public function fetchSource(string $connectionIdentifier) : \Maleficarum\Rabbitmq\Connection\Connection {
        // check if the specified connection identifier exists
        if (!array_key_exists($connectionIdentifier, $this->connections)) throw new \InvalidArgumentException(sprintf('Provided connection identifier does not exist. %s', __METHOD__));

        // check if the specified connection is marked as a source
        if (!array_key_exists($connectionIdentifier, $this->sources)) throw new \InvalidArgumentException(sprintf('Provided connection identifier matched a connection that cannot be used as a command source. %s', __METHOD__));

        // recover the specified connection for internal storage
        $connection = $this->connections[$connectionIdentifier]['connection'];

        // initialise the connection if necessary
        $connection->connect();
        
        return $connection;
    }

    /**
     * Get connectionIdentifier based on current testMode.
     *
     * @param \Maleficarum\Command\AbstractCommand $command
     * @param string $connectionIdentifier
     * @return string
     */
    private function getConnectionIdentifier(\Maleficarum\Command\AbstractCommand $command, string $connectionIdentifier): string {
        $command->getTestMode() and $connectionIdentifier = self::TEST_PREFIX . $connectionIdentifier;
        return $connectionIdentifier;
    }

    /* ------------------------------------ Class Methods END ------------------------------------------ */
}
