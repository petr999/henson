<?php

namespace Henson\Input;

class Factory {
  protected $input;

  function __construct( $inputPath, $requestInput, $sessionInput ){
    list( $trequestInput, $sessionInput
      ) = [ $requestInput ?: [], $sessionInput ?: [], ];

    $input = array_merge( $sessionInput, $requestInput );

    $method = $inputPath->getMethod();
    if( in_array( $method, [ 'PATCH', 'POST', 'PUT', ] ) ){
      $inputJson = file_get_contents( 'php://input' );
      if( ! empty( $inputJson ) ){
        $inputJson = json_decode( $inputJson, true );
        if( ( ! empty( $inputJson ) ) && is_array( $inputJson ) ){
          $input = array_merge( $input, $inputJson );
        }
      }
    }

    $this->input = $input;
  }

  function getInput( $varNames = [], $validate = null ){
    $rv = [];
    $input = $this->input;

    if( null != $validate ){
      $validate->validateInput( $input );

      foreach( $varNames as $varName ){
        if( isset( $input[ $varName ] ) ){
          $val = $input[ $varName ];
          $val = htmlentities( $val, ENT_QUOTES, 'UTF-8' );

          $rv[ $varName ] = $val;
        }
      }
    }

    return $rv;
  }

}
