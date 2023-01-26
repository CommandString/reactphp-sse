<?php

namespace CommandString\Sse;

use CommandString\Sse\Exceptions\ClientIdAlreadyTaken;
use stdClass;

class Channel {
    private stdClass $clients;
    
    public function __construct()
    {
        $this->clients = new stdClass;
    }

    public function addClient(Client $client) {
        if (!is_null($this->getClient($client->id))) {
            throw new ClientIdAlreadyTaken($client->id);
        }

        $this->clients->{$client->id} = $client;

        $client->stream->on("close", function () use ($client) {
            unset($this->clients->{$client->id});
        });
    }

    public function sendMessageToAll(string $message, string $event = null): void
    {
        foreach ($this->clients as $client) {
            $client->sendMessage($message, $event);
        }
    }

    public function sendCommentToAll(string $comment): void
    {
        foreach ($this->clients as $client) {
            $client->sendComment($comment);
        }
    }

    public function sendMessageToAllExcept(string $message, string $event = null, string ...$ids): void
    {
        foreach ($this->clients as $client) {
            if (in_array($client->id, $ids)) {
                continue;
            }

            $client->sendMessage($message, $event);
        }
    }
    
    public function sendMessageTo(string $message, string $event = null, string ...$ids): void
    {
        foreach ($ids as $id) {
            $client = $this->getClient($id);

            if (is_null($client)) {
                error_log("No client has the id \"$id\"", E_WARNING);
                continue; 
            }

            $client->sendMessage($message, $event);
        }
    }

    public function getClient(string $id): ?Client
    {
        return $this->clients->$id ?? null;
    }
    
    public function getClients(): stdClass
    {
        return $this->clients;
    }

    public function disconnectClient(string $id): void
    {
        $this->getClient($id)?->stream->close();
    }
}