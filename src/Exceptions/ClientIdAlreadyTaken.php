<?php

namespace CommandString\Sse\Exceptions;

use Exception;

class ClientIdAlreadyTaken extends Exception {
    public function __construct(string $uuid)
    {
        parent::__construct("Another client is using the uuid $uuid");
    }
}