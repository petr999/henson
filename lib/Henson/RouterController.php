<?php

namespace Henson;

use Henson\TaskModel;
use Henson\Exception;

class RouterController {

  protected $routes = [];
  protected $taskModel;
  protected $inputFactory;
  protected $inputPath;

  function __construct(){
    $taskModel = new TaskModel();

    $inputPath    = new Input\Path( $_SERVER );
    $inputFactory = new Input\Factory( $inputPath, $_GET, $_SESSION );

    $this->taskModel = $taskModel;
    $this->inputFactory = $inputFactory;
    $this->inputPath = $inputPath;
  }

  protected function addRoute( $method, $path, $endpoint ){
    $routes =& $this->routes;

    if( empty( $routes[ $method ][ $path ] ) ){
      $routes[ $method ][ $path ] = $endpoint;
    } else {
      throw new Exception( "Path '$path': duplicate", 500 );
    }

    return $this;
  }

  protected function addRoutes(){
    $this->addRoute( 'GET', '/count',  function() {
      $taskModel = $this->taskModel;
      $rv = $taskModel->fetchCount();

      return $rv;
    });
    $this->addRoute( 'GET', '/',  function() {
      $taskModel = $this->taskModel;
      $inputFactory = $this->inputFactory;
      $fields = [ 'sortBy', 'sortDir', 'pageNo', ];
      $validate = new Input\Validate( [], [
        'sortBy' => 'validateSortBy', 'pageNo' => 'validatePageNo',
        'sortDir' => 'validateSortDir',
      ] );
      $input = $inputFactory->getInput( $fields, $validate );

      $rv = $taskModel->fetchList( $input );

      return $rv;
    });
    $this->addRoute( 'POST', '/',  function() {
      $taskModel = $this->taskModel;
      $inputFactory = $this->inputFactory;
      $fields = [ 'name', 'email', 'taskText', ];
      $mandatoryFields = $fields;
      $validate = new Input\Validate( $mandatoryFields, [
        'email' => 'validateEmail', ] );
      $input = $inputFactory->getInput( $fields, $validate );

      $rv = $taskModel->createTask( $input );

      return $rv;
    });
    $this->addRoute( 'PUT', '/',  function() {
      $isAdmin = false;

      if ( ! empty( $_SESSION[ 'isAdmin' ] ) ){
        $isAdmin = $_SESSION[ 'isAdmin' ];
      }
      if( ! $isAdmin ){
        throw new Exception( 'Access denied!', 403 );
      }

      $taskModel = $this->taskModel;
      $inputFactory = $this->inputFactory;
      $fields = [ 'id', 'taskText', 'isDone' ];
      $mandatoryFields = $fields;
      $validate = new Input\Validate( [ 'id', 'taskText', 'isDone',  ], [ 'id' => 'validateId',
        'isDone' => 'validateIsDone', ] );
      $input = $inputFactory->getInput( $fields, $validate );

      $rv = $taskModel->updateTask( $input );

      return $rv;
    });
    $this->addRoute( 'POST', '/user/login',  function() {
      $rv = [];

      $inputFactory = $this->inputFactory;
      $fields = [ 'name', 'passwd', ];
      $mandatoryFields = $fields;
      $validate = new Input\Validate( $mandatoryFields );
      $input = $inputFactory->getInput( $fields, $validate );
      list( $name, $passwd ) = [ $input[ 'name' ], $input[ 'passwd' ], ];

      $success = ( 'admin' == $name ) && ( '123' == $passwd );

      if( $success ){ $rv = [ 'name' => 'admin', ];
        $_SESSION[ 'isAdmin' ] = true;
      } else {
        throw new Exception( 'Wrong user name and/or password!' , 403);
      }

      return $rv;
    });
    $this->addRoute( 'POST', '/user/logout',  function() {
      $rv = [];

      $_SESSION[ 'isAdmin' ] = false;

      return $rv;
    });
    $this->addRoute( 'GET', '/user',  function() {
      $rv = [ 'name' => 'anon', ];
      $isAdmin = false;

      if ( ! empty( $_SESSION[ 'isAdmin' ] ) ){
        $isAdmin = $_SESSION[ 'isAdmin' ];
      }

      if( $isAdmin ){
        $rv = [ 'name' => 'admin', ];
      }

      return $rv;
    });
  }

  protected function getRouteByMethodAndKey( $method, $routeKey ){
    $route = null;
    $routes = $this->routes;

    if( empty( $routes[ $method ][ $routeKey ] ) ){
      throw new Exception( "No route: '$method' '$routeKey'", 404 );
    } else {
      $route = $routes[ $method ][ $routeKey ];
    }

    return $route;
  }

  protected function getMethod(){
    $inputPath = $this->inputPath;

    $method= $inputPath->getMethod();
    return $method;
  }

  protected function getRouteKey(){
    $inputPath = $this->inputPath;

    $route= $inputPath->getRoute();
    return $route;
  }

  protected function getRoute(){
    list( $method, $routeKey ) = [
      $this->getMethod(), $this->getRouteKey(), ];

    $route = $this->getRouteByMethodAndKey( $method, $routeKey );

    return $route;
  }

  protected function printOutput( $outputValue ){
    header( "Content-Type: application/json" );
    echo json_encode( [ 'code' => 200, 'message' => $outputValue, ],
      JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
  }

  protected function routeAndControl(){
    $route = $this->getRoute();

    try{
      $rv = call_user_func( $route );
      $this->printOutput( $rv );
    } catch ( Exception $e ){
      list( $message, $code ) = [ $e, $e->getCode(), ];
      http_response_code( $code );
      header( "Content-Type: application/json" );
      echo $message;
    }

  }

  static function run(){
    $routerController = new self();
    $routerController->addRoutes();
    $routerController->routeAndControl();
  }

}
