<?php

namespace App\Exceptions;

use Exception;

class PermissionDeniedException extends Exception
{
    protected $code = 403;
}
