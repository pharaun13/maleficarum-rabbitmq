<?php
declare(strict_types = 1);

/**
 * Tests for the \Maleficarum\Rabbitmq\Connection\ConnectionTest class.
 */

namespace Maleficarum\Rabbitmq\Tests\Connection;

class ConnectionTest extends \Maleficarum\Tests\TestCase {
    /* ------------------------------------ Method: connect START -------------------------------------- */
    public function testConnectAfterItWasCalled() {
        $connection = \Maleficarum\Ioc\Container::get('Maleficarum\Rabbitmq\Connection\Connection', ['foo', 'bar', 0, 'baz', 'qux']);
        $connection->connect();
        
        $this->assertInstanceOf('PhpAmqpLib\Connection\AMQPStreamConnection', $connection->getConnection());
    }
    
    public function testConnectBeforeItWasCalled() {
        $connection = \Maleficarum\Ioc\Container::get('Maleficarum\Rabbitmq\Connection\Connection', ['foo', 'bar', 0, 'baz', 'qux']);

        $this->assertNull($connection->getConnection());
    }
    /* ------------------------------------ Method: connect END ---------------------------------------- */

    /* ------------------------------------ Method: getChannel START ----------------------------------- */
    public function testGetChannelWithCorrectId() {
        $connection = \Maleficarum\Ioc\Container::get('Maleficarum\Rabbitmq\Connection\Connection', ['foo', 'bar', 0, 'baz', 'qux']);
        $connection->connect();
        
        $this->assertInstanceOf('PhpAmqpLib\Channel\AMQPChannel', $connection->getChannel(1));
        $this->assertSame(9999, $connection->getChannel(1)->getChannelId());
    }
    
    /* ------------------------------------ Method: getChannel END ------------------------------------- */
    
    /* ------------------------------------ Method: __destruct START ----------------------------------- */
    public function testDestruct() {
        $connection = \Maleficarum\Ioc\Container::get('Maleficarum\Rabbitmq\Connection\Connection', ['foo', 'bar', 0, 'baz', 'qux']);
        $connection->connect();
        unset($connection);
    }
    /* ------------------------------------ Method: __destruct END ------------------------------------- */
}
