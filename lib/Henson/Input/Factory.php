<?php

namespace Henson\Input;

class Factory {
  protected $input;
  protected $limit = HENSON_TASKLIST_ITEMS_LIMIT;

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
    $this->normalizeValues();
  }

  function normalizeValues(){
    $input =& $this->input;
    $limit = $this->limit;

    if( ( ! empty( $input[ 'start' ] ) )
        &&
        ( $input[ 'start' ] = (int) $input[ 'start' ] )
    ){ $input[ 'pageNo' ] = 1 + $input[ 'start' ] / $limit;
    }

    if(
      ( ! empty( $input[ 'order' ] ) )
        &&
      is_array( $input[ 'order' ] )
        &&
      ( ! empty( $input[ 'order' ][0] ) )
    ){
      $orderArr = $input[ 'order' ][ 0 ];
      list( $column, $sortDir ) = [
        $orderArr[ 'column' ], $orderArr[ 'dir' ], ];
      if(
        ( ! empty( $input[ 'columns' ] ) )
          &&
        is_array( $input[ 'columns' ] )
      ){
        $columns = $input[ 'columns' ];
        if( ! empty( $columns[ $column ]  ) ){
          $columnsElem = $columns[ $column ];
          if( ! empty( $columnsElem[ 'data' ] ) ){
            $columnName = $columnsElem[ 'data' ];
            $sortBy = $columnName;
            list( $input[ 'sortBy' ], $input[ 'sortDir' ] ) = [
              $sortBy, $sortDir,
            ];
          }
        }
      }
    }
  }

  function getInput( $varNames = [], $validate = null ){
    $rv = [];
    $input = $this->input;

    if( null != $validate ){
      $validate->validateInput( $input );
    }

    foreach( $varNames as $varName ){
      if( isset( $input[ $varName ] ) ){
        $val = $input[ $varName ];
        $val = htmlentities( $val, ENT_QUOTES, 'UTF-8' );

        $rv[ $varName ] = $val;
      }
    }

    return $rv;
  }

}
