<?php

\Maleficarum\Ioc\Container::register('PhpAmqpLib\Connection\AMQPStreamConnection', function () {
    $connection = $this->getMockBuilder('PhpAmqpLib\Connection\AMQPStreamConnection')->disableOriginalConstructor();

    // testAddCommand
    if ($this->getContext() === 'testAddCommand') {
        // sub mock
        $channelMock = $this->createMock('PhpAmqpLib\Channel\AMQPChannel');
        $channelMock
            ->expects($this->once())
            ->method('basic_publish')
            ->with($this->callback(function ($object) {
                return ($object instanceOf \PhpAmqpLib\Message\AMQPMessage);
            }));
        $channelMock
            ->expects($this->once())
            ->method('close');

        $connection = $connection
            ->setMethods(['channel'])
            ->getMock();
        $connection
            ->expects($this->exactly(1))
            ->method('channel')
            ->will($this->returnValue($channelMock));
    }

    // testAddCommandsCorrect
    if ($this->getContext() === 'testAddCommandsCorrect') {
        // sub mock
        $channelMock = $this->createMock('PhpAmqpLib\Channel\AMQPChannel');
        $channelMock
            ->expects($this->exactly(2))
            ->method('batch_basic_publish')
            ->with($this->callback(function ($object) {
                return ($object instanceOf \PhpAmqpLib\Message\AMQPMessage);
            }));
        $channelMock
            ->expects($this->once())
            ->method('publish_batch');
        $channelMock
            ->expects($this->once())
            ->method('close');

        $connection = $connection
            ->setMethods(['channel'])
            ->getMock();
        $connection
            ->expects($this->exactly(1))
            ->method('channel')
            ->will($this->returnValue($channelMock));
    }

    // testDestruct
    if ($this->getContext() === 'testDestruct') {
        $connection = $connection
            ->setMethods(['close'])
            ->getMock();
        $connection
            ->expects($this->exactly(1))
            ->method('close');
    }

    // fallback to default context behaviour
    if (!($connection instanceof \PhpAmqpLib\Connection\AMQPStreamConnection)) $connection = $connection->getMock();
    
    return $connection;
});

\Maleficarum\Ioc\Container::register('PhpAmqpLib\Message\AMQPMessage', function () {
    return $this->createMock('PhpAmqpLib\Message\AMQPMessage');
});
