<?php


namespace ChromeHeadless\Exceptions;

use Exception;

class EmptyDocument extends Exception
{
    public function __construct($url)
    {
        parent::__construct("Could not get `${url}`. This could be due to network problems or an invalid request.");
    }
}