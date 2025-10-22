<?php
namespace app\modules\story\exceptions;

class UpstreamException extends \Exception
{
    public function __construct(string $message, int $statusCode = 0)
    {
        parent::__construct($message, $statusCode);
    }
}