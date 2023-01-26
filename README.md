# commandstring/sse

Server-sent events with ReactPHP

# Channel
This is where all clients are stored, you can perform actions on all clients from here or specific clients.

# Client
This represents an active connection

# Example

```php
# index.php
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
```

```html
<!-- index.html -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SSE</title>
    
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.1/dist/jquery.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/fomantic-ui@2.9.0/dist/semantic.min.css">
    <script src="https://cdn.jsdelivr.net/npm/fomantic-ui@2.9.0/dist/semantic.min.js"></script>
</head>
<body>
    <div style="height: 100vh" class="ui inverted segment">
        <div class="ui header">List</div>
        <ul id="items">
            
        </ul>
    </div>
<script>
let eventStream = new EventSource("http://localhost:8000/sse");

eventStream.addEventListener("item", (event) => {
    $("#items").append(`<li>${event.data}</li>`);
    console.log(event);
});

eventStream.addEventListener("open", () => {
    console.log("connected");
    $.toast({
        "title": "Connected to SSE server"
    });
})
</script>
</body>
</html>
```