<?php

namespace Henson;

class Exception extends \Exception
{

    function __construct($message, $code, Throwable $previous = null)
    {
        $message = json_encode(
            $message,
            JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
        );
        parent::__construct($message, $code, $previous);
    }

    function __toString()
    {
        list( $message, $code ) = [ $this->message, $this->code, ];

        $rv = json_encode(
            [ 'code' => $code, 'description' => $message, ],
            JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
        );

        return $rv;
    }
}
