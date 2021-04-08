<?php

namespace Henson;

use Henson\Exception;

class TaskModel{

  protected $fileName = HENSON_SQLITE;
  protected $dbh;
  protected $limit = HENSON_TASKLIST_ITEMS_LIMIT;

  function __construct(){
    $fileName = $this->fileName;
    $dbh = new \PDO('sqlite:' . $fileName);
    $this->dbh = $dbh;
  }

  function fetchList( $input ){
    $rv = [];
    $dbh = $this->dbh;

    $limit = $this->limit;

    try {
      $sql = 'SELECT * FROM tasks ';
      if( ! empty( $input[ 'sortBy' ] ) ){
        // $sql .= ' order by :sortBy ';
        $sortBy = $input[ 'sortBy' ];
        $sql .= " order by `$sortBy`";
      }
      $sql .= ' limit :limit ';
      if( ! empty( $input[ 'pageNo' ] ) ){ $sql .= ' offset :offset '; }

      $statementHandle = $dbh->prepare( $sql );
      $statementHandle->bindValue(':limit', $limit);
      if( ! empty( $input[ 'pageNo' ] ) ){ $pageNo = $input[ 'pageNo' ];
        $offset = $limit * ( $pageNo - 1 );
        $statementHandle->bindValue(':offset', $offset);
      }
      $statementHandle->execute();

      while( $taskRow = $statementHandle->fetch( \PDO::FETCH_ASSOC ) ){
        $rv[] = $taskRow;
      }

    } catch( PDOException $e ) {
      $message = $e->getMessage();
      throw new Exception( "Fetch error: '$message'" , 500 );
    }

    if( empty( $rv ) ){
      throw new Exception( 'Tasks: no records found', 404 );
    }

    return $rv;
  }

  function fetchTaskById( $id ){
    $rv = [];
    $dbh = $this->dbh;

    $limit = $this->limit;

    try {
      $sql = 'SELECT * FROM tasks where id = :id';
      $statementHandle = $dbh->prepare( $sql );
      $statementHandle->bindValue(':id', $id);
      $statementHandle->execute();

      while( $taskRow = $statementHandle->fetch( \PDO::FETCH_ASSOC ) ){
        $rv[] = $taskRow;
      }

    } catch( PDOException $e ) {
      $message = $e->getMessage();
      throw new Exception( "Fetch error: '$message'" , 500 );
    }

    if( empty( $rv ) ){
      throw new Exception( 'Task: no records found', 404 );
    }

    return $rv; // [ { "id":  ... } ]
  }

  function updateTask( $input ){
    $dbh = $this->dbh;
    $rv = [];

    try{
      $id = $input[ 'id' ];
      list( $taskText, $isDone ) = array_map(
        function( $key ) use ( $input ){
          $value = null;
          if( isset( $input[ $key ] ) ){
            $value = $input[ $key ];
          }
          return $value;
      },  [ 'taskText', 'isDone',
      ] );

      $statement = $dbh->prepare('update tasks set '
        . ' taskText = :taskText, isDone = :isDone where '
        . 'id = :id'
      );

      $statement->bindValue(':id',        $id);
      $statement->bindValue(':taskText',  $taskText);
      $statement->bindValue(':isDone',    $isDone);

      $statement->execute();
      $rv = $this->fetchTaskById( $id );
    } catch( PDOException $e ){
      $message = $e->getMessage();
      throw new Exception( "Fetch error: '$message'" , 500 );
    }

    return $rv;
  }

  function createTask( $input ){
    $task = $input;
    $dbh = $this->dbh;
    $id = null;

    try{
      list( $name, $email, $taskText ) = array_map(
        function( $key ) use ( $task ){
          $value = $task[ $key ]; return $value;
      },  [ 'name', 'email', 'taskText',
      ] );

      $statement = $dbh->prepare('insert into tasks '
        . ' ( name, email, taskText ) values '
        . '( :name, :email, :taskText )'
      );

      $statement->bindValue(':name',      $name);
      $statement->bindValue(':email',     $email);
      $statement->bindValue(':taskText',  $taskText);

      $statement->execute();
      $id = $dbh->lastInsertId();

    } catch( PDOException $e ){
      $message = $e->getMessage();
      throw new Exception( "Fetch error: '$message'" , 500 );
    }

    $task[ 'id' ] = $id;

    return $task;
  }

}
