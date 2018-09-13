<?php

\Maleficarum\Ioc\Container::registerBuilder('PhpAmqpLib\Connection\AMQPStreamConnection', function () {
    $connection = $this->getMockBuilder('PhpAmqpLib\Connection\AMQPStreamConnection')->disableOriginalConstructor();
    
    // testGetChannelWithCorrectId
    if ($this->getContext() === 'testGetChannelWithCorrectId') {
        $connection = $connection->setMethods(['channel'])->getMock();
        $connection->method('channel')->willReturn(\Maleficarum\Ioc\Container::get('PhpAmqpLib\Channel\AMQPChannel'));
    }
    
    // testDestruct
    if ($this->getContext() === 'testDestruct') {
        $connection = $connection->setMethods(['close', 'isConnected'])->getMock();
        $connection->expects($this->exactly(1))->method('close');
        $connection->expects($this->exactly(1))->method('isConnected')->willReturn(true);
    }

    // fallback to default context behaviour
    if (!($connection instanceof \PhpAmqpLib\Connection\AMQPStreamConnection)) $connection = $connection->getMock();
    
    return $connection;
});

\Maleficarum\Ioc\Container::registerBuilder('PhpAmqpLib\Message\AMQPMessage', function () {
    return $this->createMock('PhpAmqpLib\Message\AMQPMessage');
});

\Maleficarum\Ioc\Container::registerBuilder('PhpAmqpLib\Channel\AMQPChannel', function () {
    $chan = $this->getMockBuilder('PhpAmqpLib\Channel\AMQPChannel')->disableOriginalConstructor();
    $chan = $chan->setMethods(['getChannelId'])->getMock();
    $chan->method('getChannelId')->willReturn(9999);
    
    return $chan;
});