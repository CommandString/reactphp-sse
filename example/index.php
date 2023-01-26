<?php

use CommandString\Sse\Channel;
use CommandString\Sse\Client;
use Psr\Http\Message\ResponseInterface;
use React\EventLoop\Loop;
use React\Http\Message\ServerRequest;
use React\Stream\ThroughStream;

require_once "vendor/autoload.php";

$loop = Loop::get();

$channel = new Channel;

$clientCount = 0;
(new React\Http\HttpServer(function (ServerRequest $req) use (&$clientCount, $channel): ResponseInterface
{
    if ($req->getRequestTarget() === "/sse") {
        $stream = new ThroughStream();
        $client = new Client($stream, $clientCount++);

        echo $clientCount . PHP_EOL;

        $channel->addClient($client);

        return new React\Http\Message\Response(
             React\Http\Message\Response::STATUS_OK,
            array(
                'Content-Type' => 'text/event-stream'
            ),
            $stream
        );
    }

    return new React\Http\Message\Response(
        React\Http\Message\Response::STATUS_OK,
        array(
            'Content-Type' => 'text/html'
        ),
        file_get_contents("index.html")
    );
}))->listen(new React\Socket\SocketServer('127.0.0.1:8000'));

Loop::addPeriodicTimer(1, function () use ($channel) {
    $channel->sendMessageToAll(mt_rand(0, 9999), "item");
});