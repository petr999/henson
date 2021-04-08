<?php

namespace Henson\Input;

class Path {

  protected $method = 'GET';
  protected $route  = HENSON_ROUTE_DEFAULT;
  protected $instanceId = null;

  function __construct( $serverInput ){
    $this->initMethodRouteAndInstanceId( $serverInput );
  }

  protected function initMethodRouteAndInstanceId( $serverInput ){
    $instanceId = $this->instanceId;

    if( ! empty( $serverInput[ 'PATH_INFO' ] ) ){
      $route =& $this->route;
      $pathInfo = $serverInput[ 'PATH_INFO' ];

      $pathInfo = preg_replace( '/[^\/a-zA-Z0-9]/', '', $pathInfo );
      $pathInfoArr = explode( '/', $pathInfo );
      $pathInfoArrCount = count( $pathInfoArr );
      if( 0 < $pathInfoArrCount ){
        if( 1 < $pathInfoArr ){
          $pathLastElem = $pathInfoArr[ $pathInfoArrCount - 1 ];
          if( 1 === preg_match( '/^[a-f0-9]+$/', $pathLastElem ) ){
            $instanceId = array_pop( $pathInfoArr ); // instanceId
          }
        }
        $pathInfo = implode( '/', $pathInfoArr );
      }
      if( ! empty( $pathInfo ) ){ $route = $pathInfo; }
    }

    if( ! empty( $serverInput[ 'REQUEST_METHOD' ] ) ){
      $this->method = $serverInput[ 'REQUEST_METHOD' ]; }

    $this->instanceId = $instanceId;
  }

  function getMethod(){
    $method = $this->method;

    return $method;
  }

  function getRoute(){
    $route = $this->route;

    return $route;
  }

}
