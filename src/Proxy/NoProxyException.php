<?php

namespace GregPriday\ZyteApi\Proxy;

use Exception;

class NoProxyException extends Exception
{
    public function __construct()
    {
        parent::__construct('No proxy has been configured');
    }
}
