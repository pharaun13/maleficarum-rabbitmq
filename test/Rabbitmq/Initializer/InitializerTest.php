<?php
declare(strict_types = 1);

/**
 * Tests for the \Maleficarum\Rabbitmq\Initializer\Initializer class.
 */

namespace Maleficarum\Rabbitmq\Tests\Initializer;

class InitializerTest extends \Maleficarum\Tests\TestCase {
    /* ------------------------------------ Method: __construct START ---------------------------------- */
    public function testInitializeWithoutSkip() {
        \Maleficarum\Rabbitmq\Initializer\Initializer::initialize();

        $this->assertTrue(\Maleficarum\Ioc\Container::isBuilderRegistered('Maleficarum\Rabbitmq\Connection\Connection'));
        $this->assertTrue(\Maleficarum\Ioc\Container::isBuilderRegistered('Maleficarum\Rabbitmq\Manager\Manager'));
        
        $this->assertInstanceOf('Maleficarum\Rabbitmq\Manager\Manager', \Maleficarum\Ioc\Container::retrieveShare('Maleficarum\CommandRouter'));
    }
    
    public function testInitializeWithSkip() {
        \Maleficarum\Rabbitmq\Initializer\Initializer::initialize(['builders' => ['queue' => ['skip' => true]]]);
        
        $this->assertFalse(\Maleficarum\Ioc\Container::isBuilderRegistered('Maleficarum\Rabbitmq\Connection\Connection'));
        $this->assertFalse(\Maleficarum\Ioc\Container::isBuilderRegistered('Maleficarum\Rabbitmq\Manager\Manager'));

        $this->assertInstanceOf('Maleficarum\Rabbitmq\Manager\Manager', \Maleficarum\Ioc\Container::retrieveShare('Maleficarum\CommandRouter'));
    }
    /* ------------------------------------ Method: __destruct END ------------------------------------- */
}
