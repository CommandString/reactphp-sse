<?php

namespace CommandString\Sse;

use React\Stream\WritableStreamInterface;

class Client {
    public const EOL = "\n";
    private int $lastId = 0;
    
    public function __construct(private WritableStreamInterface $stream, private string $id, private int $retry = 5000) {}

    public function sendMessage(string $data, string $event = null):void
    {
        $this->lastId++;

        $this->stream->write(self::buildMessageString($data, $event, $this->lastId, $this->retry));
    }

    public function sendComment(string $comment): void
    {
        $this->stream->write(": $comment\n\n");
    }

    public static function buildMessageString(string $message, string $event = null, int $id = null, int $retry = null): string
    {
        $encodedMessage = "";
        $encodedMessage .= (!is_null($event)) ? self::encode("event", $event) : "";
        $encodedMessage .= (!is_null($id)) ? self::encode("id", $id) : "";
        $encodedMessage .= (!is_null($retry)) ? self::encode("retry", $retry) : "";

        $encodedMessage .= self::encode("data", $message);

        return $encodedMessage.self::EOL;
    }

    public function setRetry(int $retry): void
    {
        $this->retry = $retry;

        $this->stream->write(self::encode("retry", $retry) . self::EOL);
    }

    public function __get($name)
    {
        return $this->$name ?? null;
    }
    
    
    public static function encode(string $key, string $value): string
    {
        return "$key:$value".self::EOL;
    }
}