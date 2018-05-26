<?php

namespace ChromeHeadless\Exceptions;

use Exception;

class ChromeException extends Exception
{
    public function __construct($url, $message)
    {
        parent::__construct("Could not get $url\n$message");
    }
}
