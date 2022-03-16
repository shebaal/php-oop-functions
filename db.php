<?php
$dns = "mysql:host=localhost";
$username = 'root';
$password = '';

class Database
{
  private $conn;
  private $select_query;

  public function __construct($db_name)
  {
    global $dns, $username, $password;

    $this->conn = new PDO($dns, $username, $password, [
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $this->conn->exec("CREATE DATABASE IF NOT EXISTS `$db_name`; USE `$db_name`");
  }

  public function setTable(string $table_name, array $data)
  {
    $columns = "";
    foreach ($data as $key => $value) {
      $columns .= "`$key` $value ,";
    }

    $query = "CREATE TABLE IF NOT EXISTS `$table_name` ( 
          `id` INT NOT NULL AUTO_INCREMENT ,
          $columns
          `is_active` INT DEFAULT 1 ,
          PRIMARY KEY (`id`));
    )";

    $statement = $this->conn->prepare($query);
    $statement->execute();
  }




  public function select(
    string $table_name,
    array $columns = ["*"]
  ) {
    $columns_name = implode(',', $columns);
    $query = "SELECT $columns_name FROM $table_name ";

    $this->select_query = $query;


    $statement = $this->conn->prepare($query);
    $statement->execute();


    return $this;
  }




  public function where(array $conditions = null)
  {
    $where_condition = "";

    if ($conditions) {
      foreach ($conditions as $item) {

        // don't touch this code :)
        if (strtoupper($item[1]) === "IN") {
          $in_query = '';
          foreach ($item[2] as $i) {
            $in_query .= " '$i' ,";
          }
          $in_query = substr($in_query, 0, -1);

          $where_condition .= "`{$item[0]}` {$item[1]} ($in_query) AND ";
        } else {
          $where_condition .= "`{$item[0]}` {$item[1]} '{$item[2]}' AND ";
        }
      }

      $where_condition = substr($where_condition, 0, -4);
      $query = $this->select_query .  " WHERE $where_condition ";
    } else {
      $query = $this->select_query;
    }
    // print_r($where_condition);
    // exit;



    $statement = $this->conn->prepare($query);
    $statement->execute();

    return $statement->fetchAll();
  }

  public function OrderBy(string $orderBy_condition)
  {
    

      $query = $this->select_query .  " ORDER BY $orderBy_condition ";
    $statement = $this->conn->prepare($query);
    $statement->execute();

    return $statement->fetchAll();
  }

  public function GroupBy(string $groupBy_condition)
  {
    

      $query = $this->select_query .  " GROUP BY $groupBy_condition ";
    $statement = $this->conn->prepare($query);
    $statement->execute();

    return $statement->fetchAll();
  }
 

  public function selectWithCount(
    string $table_name,
    string $columns_name_to_count
  ) {
    
    $query = "SELECT COUNT($columns_name_to_count) FROM $table_name ";

    $this->select_query = $query;


    $statement = $this->conn->prepare($query);
    $statement->execute();


    return $this;
  }


  public function insert(string $table_name, array $data)
  {
    $columns = '';
    $values = '';

    foreach ($data as $key => $value) {
      $columns .= "`$key` ,";
      $values .= "'$value' ,";
    }

    $columns = substr($columns, 0, -1);
    $values = substr($values, 0, -1);

    $query = "INSERT INTO `$table_name` ($columns) VALUES ($values)";
    // echo $query;

    $statement = $this->conn->prepare($query);
    $statement->execute();
  }

  public function update(string $table_name, int $id, array $data)
  {
    $params = [];

    foreach ($data as $key => $value) {
      array_push($params,  "`$key` = '$value'");
    }

    $sql  = "UPDATE $table_name SET " . implode(',', $params) .  " WHERE `id` = $id";

    $statement = $this->conn->prepare($sql);
    $statement->execute();
  }

  public function delete($table_name, $id)
  {
    $query = "DELETE FROM `$table_name` WHERE `id` = $id";

    $statement = $this->conn->prepare($query);
    $statement->execute();
  }
}



// $testObject = new Database('sabaa');


// $testObject->setTable('school', [
//   'name' => 'VARCHAR(255) NOT NULL',
//   'age' => 'INT(12) DEFAULT 5',
// ]);


