<?php

namespace NAL\View\Exception;

use Throwable;

class PathNotFound extends \InvalidArgumentException
{
    public function __construct(string $file = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct("File is not found or Not a valid file: $file", $code, $previous);
    }
}
