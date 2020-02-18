<?php
declare(strict_types = 1);

/**
 * Tests for the \Maleficarum\Rabbitmq\Connection\Connection class.
 */

namespace Maleficarum\Rabbitmq\Tests\Connection;

class ConnectionTest extends \Maleficarum\Tests\TestCase {
    /* ------------------------------------ Method: __construct START ---------------------------------- */
    public function testConstructParameres() {
        $connection = \Maleficarum\Ioc\Container::get('Maleficarum\Rabbitmq\Connection\Connection', ['foo', 0, 'bar', 'baz', 'qux', 'quux', 'quuz']);
        $this->assertSame('foo', $this->getProperty($connection, 'host'));
        $this->assertSame(0, $this->getProperty($connection, 'port'));
        $this->assertSame('bar', $this->getProperty($connection, 'username'));
        $this->assertSame('baz', $this->getProperty($connection, 'password'));
        $this->assertSame('qux', $this->getProperty($connection, 'vhost'));
        $this->assertSame('quux', $this->getProperty($connection, 'exchangeName'));
        $this->assertSame('quuz', $this->getProperty($connection, 'queueName'));
    }
    
    /* ------------------------------------ Method: __construct END ------------------------------------ */
    
    /* ------------------------------------ Method: connect START -------------------------------------- */
    public function testConnectAfterItWasCalled() {
        $connection = \Maleficarum\Ioc\Container::get('Maleficarum\Rabbitmq\Connection\Connection', ['foo', 0, 'bar', 'baz', 'qux', 'quux', 'quuz']);
        $connection->connect();
        
        $this->assertInstanceOf('PhpAmqpLib\Connection\AMQPStreamConnection', $connection->getConnection());
    }
    
    public function testConnectBeforeItWasCalled() {
        $connection = \Maleficarum\Ioc\Container::get('Maleficarum\Rabbitmq\Connection\Connection', ['foo', 0, 'bar', 'baz', 'qux', 'quux', 'quuz']);

        $this->assertNull($connection->getConnection());
    }
    /* ------------------------------------ Method: connect END ---------------------------------------- */

    /* ------------------------------------ Method: getChannel START ----------------------------------- */
    public function testGetChannelWithCorrectId() {
        $connection = \Maleficarum\Ioc\Container::get('Maleficarum\Rabbitmq\Connection\Connection', ['foo', 0, 'bar', 'baz', 'qux', 'quux', 'quuz']);
        $connection->connect();
        
        $this->assertInstanceOf('PhpAmqpLib\Channel\AMQPChannel', $connection->getChannel(1));
        $this->assertSame(9999, $connection->getChannel(1)->getChannelId());
    }
    
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetChannelWithIncorrectId() {
        $connection = \Maleficarum\Ioc\Container::get('Maleficarum\Rabbitmq\Connection\Connection', ['foo', 0, 'bar', 'baz', 'qux', 'quux', 'quuz']);
        $connection->connect();
        $connection->getChannel(-11);
    }
    
    /* ------------------------------------ Method: getChannel END ------------------------------------- */
    
    /* ------------------------------------ Method: __destruct START ----------------------------------- */
    public function testDestruct() {
        $connection = \Maleficarum\Ioc\Container::get('Maleficarum\Rabbitmq\Connection\Connection', ['foo', 0, 'bar', 'baz', 'qux', 'quux', 'quuz']);
        $connection->connect();
        unset($connection);
    }
    /* ------------------------------------ Method: __destruct END ------------------------------------- */
}
