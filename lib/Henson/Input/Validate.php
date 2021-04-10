<?php

namespace Henson\Input;

use Henson\Exception;

class Validate {

  protected $mandatoryFields;
  protected $validators;

  function __construct( $mandatoryFields, $validators = [] ){
    list(
      $this->mandatoryFields, $this->validators,
    ) = [
      $mandatoryFields, $validators
    ];
  }

  function validateInputMandatoryFields( $inputArr ){
    $mandatoryErrors = [];
    $mandatoryFields = $this->mandatoryFields;

    foreach( $mandatoryFields as $mandatoryField ){
      if( empty( $inputArr[ $mandatoryField ] )
          && (
            ( ! isset( $inputArr[ $mandatoryField ] ) )
              ||
            '' === $inputArr[ $mandatoryField ]
          )
      ){
        $mandatoryErrors[] = "Empty field: '$mandatoryField'";
      }
    }

    return $mandatoryErrors;
  }

  function validateIsDone( $value ){
    $rv = true;

    $rv = ( $value === (boolean) $value );

    return $rv;
  }

  function validateId( $value ){
    $rv = true;

    $rv = ( $value === (int) $value );

    return $rv;
  }

  function validatePageNo( $value ){
    $rv = true;

    $rv = ( false != filter_var( $value, FILTER_VALIDATE_INT ) );

    return $rv;
  }

  function validateSortBy( $value ){
    $rv = true;

    $rv = in_array( $value, [ 'id', 'name', 'email', 'isDone', ] );

    return $rv;
  }

  function validateSortDir( $value ){
    $rv = true;

    $value = strtolower( $value );
    $rv = in_array( $value, [ 'asc', 'desc', ] );

    return $rv;
  }

  function validateEmail( $value ){
    $rv = true;

    $rv = ( false != filter_var( $value, FILTER_VALIDATE_EMAIL ) );

    return $rv;
  }

  function validateInputByValidators( $inputArr ){
    $byValidatorErrors = [];
    $validators = $this->validators;

    foreach( $inputArr as $field => $value ){
      if( ! empty( $validators[ $field ] ) ){
        $validator = $validators[ $field ];
        if( ! call_user_func( [ $this, $validator, ], $value ) ){
          $byValidatorErrors[] = "Invalid field: '$field'";
        }
      }
    }

    return $byValidatorErrors;
  }

  function validateInput( $inputArr ){
    $mandatoryErrors = $this->validateInputMandatoryFields( $inputArr );
    $byValidatorsErrors = [];
    if( empty( $mandatoryErrors ) ){
      $byValidatorsErrors = $this->validateInputByValidators( $inputArr );
    }
    $validationErrors = array_merge( $mandatoryErrors, $byValidatorsErrors );
    if( ! empty( $validationErrors ) ){
      throw new Exception( $validationErrors, 422 );
    }
  }

}
