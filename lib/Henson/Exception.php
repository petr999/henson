<?php

namespace Henson;

class Exception extends \Exception {

function __construct( $message, $code, Throwable $previous = null ){
  http_response_code( $code );
  header( "Content-Type: application/json" );
  echo json_encode( [ 'code' => $code, 'message' => $message, ],
      JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
  parent:__construct( $message, $code, $previous );
}

}
