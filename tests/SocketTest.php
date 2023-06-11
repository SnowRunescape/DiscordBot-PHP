<?php

declare(strict_types=1);

namespace Tests;

use DiscordPHP\Socket;
use PHPUnit\Framework\TestCase;
use WebSocket\Client;

class SocketTest extends TestCase
{
    private Socket $socket;
    private Client $mockWebSocket;

    protected function setUp(): void
    {
        $this->mockWebSocket = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->socket = new Socket("wss://localhost");

        $reflection = new \ReflectionClass($this->socket);
        $reflectionProperty = $reflection->getProperty("socket");
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->socket, $this->mockWebSocket);
    }

    public function testIsConnected()
    {
        $this->mockWebSocket->expects($this->once())
            ->method("isConnected")
            ->willReturn(true);

        $this->assertTrue($this->socket->isConnected());
    }

    public function testSend()
    {
        $this->mockWebSocket->expects($this->once())
            ->method("send")
            ->with("ping");

        $this->socket->send("ping");
    }

    public function testReceive()
    {
        $this->mockWebSocket->method("receive")->willReturn('{"foo": "bar"}');

        $this->assertEquals(["foo" => "bar"], $this->socket->receive());
    }
}
