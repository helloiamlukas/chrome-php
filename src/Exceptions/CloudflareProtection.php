<?php

namespace ChromeHeadless\Exceptions;

use Exception;

class CloudflareProtection extends Exception
{
    public function __construct($url)
    {
        parent::__construct("Could not get `${url}`. This website is protected by Cloudflare.");
    }
}
