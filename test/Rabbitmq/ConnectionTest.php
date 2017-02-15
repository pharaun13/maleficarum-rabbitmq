<?php
declare(strict_types = 1);

/**
 * Tests for the \Maleficarum\Rabbitmq\ConnectionTest class.
 */

namespace Maleficarum\Rabbitmq\Tests;

class ConnectionTest extends \Maleficarum\Tests\TestCase
{
    /* ------------------------------------ Method: addCommand START ----------------------------------- */
    public function testAddCommand() {
        $command = $this->createMock('Maleficarum\Command\AbstractCommand');

        $connection = new \Maleficarum\Rabbitmq\Connection();
        $connection->addCommand($command);
    }
    /* ------------------------------------ Method: addCommand END ------------------------------------- */

    /* ------------------------------------ Method: addCommands START ---------------------------------- */
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAddCommandsEmpty() {
        $connection = new \Maleficarum\Rabbitmq\Connection();
        $connection->addCommands([]);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAddCommandsIncorrect() {
        $connection = new \Maleficarum\Rabbitmq\Connection();
        $connection->addCommands([null, null]);
    }

    public function testAddCommandsCorrect() {
        $connection = new \Maleficarum\Rabbitmq\Connection();
        $connection->addCommands([
            $this->createMock('Maleficarum\Command\AbstractCommand'),
            $this->createMock('Maleficarum\Command\AbstractCommand')
        ]);
    }
    /* ------------------------------------ Method: addCommands END ------------------------------------ */

    /* ------------------------------------ Method: init START ----------------------------------------- */
    public function testInit() {
        $connection = \Maleficarum\Ioc\Container::get('Maleficarum\Rabbitmq\Connection')->init();

        $method = new \ReflectionMethod($connection, 'getConnection');
        $method->setAccessible(true);
        $this->assertInstanceOf('PhpAmqpLib\Connection\AMQPConnection', $method->invoke($connection));
        $method->setAccessible(false);
    }
    /* ------------------------------------ Method: init END ------------------------------------------- */

    /* ------------------------------------ Method: __destruct START ----------------------------------- */
    public function testDestruct() {
        $connection = \Maleficarum\Ioc\Container::get('Maleficarum\Rabbitmq\Connection')->init();
        unset($connection);
    }
    /* ------------------------------------ Method: __destruct END ------------------------------------- */
}
