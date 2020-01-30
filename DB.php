<?php

require_once 'components/functions.php';

class DataBase 
{
      
  public $dblocation;
  public $dbname;
  public $dbuser;
  public $dbpasswd;


  public function __construct() 
  {
    $this->dblocation = "localhost";
    $this->dbname = "wikipdf";
    $this->dbuser = "root";
    $this->dbpasswd = "pass";
  }


  public function connect() 
  {
    $db = new mysqli($this->dblocation, $this->dbuser, $this->dbpasswd, $this->dbname);
    mysqli_set_charset($db, "utf8");

    if($db->connect_errno) {
      die("Ошибка доступа к MySQL");
    }
    echo "DB is connected<br>";
    return $db;
  }



  public function createIninitalArticleTable($db, string $DBtableName)
  {
    //CREATE TABLE wikipdf.$tableName 
    //(url VARCHAR(150) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL)
    //ENGINE = InnoDB CHARSET=utf8 COLLATE utf8_general_ci;
    
    $sql = "CREATE TABLE wikipdf.$DBtableName(url VARCHAR(150) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL) ENGINE = InnoDB CHARSET=utf8 COLLATE utf8_general_ci;";
    $rs = mysqli_query($db, $sql) or trigger_error(mysqli_error($rs) . " in " . $sql);
    echo "<br><b>Создана таблица: $DBtableName<b><br>"; 
  }

  public function dropTable($db, string $tN)
  {
    $sql = "DROP TABLE $tN";
    $rs = mysqli_query($db, $sql) or trigger_error(mysqli_error($rs) . " in " . $sql);
    echo "Уничтожена таблица: $tN<br>"; 
  }





  //ПРОВЕРКИ - СУЩЕСТВУЕТ ЛИ ТАБЛИЦА И НЕ ПУСТАЯ ЛИ ОНА
  public function tableExists($db, string $tN)
  {
    $sql = "SHOW TABLES LIKE " . "'" . "$tN" . "'";
    $rs = mysqli_query($db, $sql) or trigger_error(mysqli_error($rs) . " in " . $sql);
    return (mysqli_num_rows($rs) > 0);
  }

  public function isTableEmpty($db, string $tN)
  {
    //$sql = "SELECT count(*) FROM $tN";
    $sql = "SELECT * FROM $tN";
    $rs = mysqli_query($db, $sql) or trigger_error(mysqli_error($rs) . " in " . $sql);

    if (mysqli_num_rows($rs) == 0)  //table row is empty 
    { 
      return true;                  //table row is empty
    } else return false; 
  }


 
  //OPERATIONS WITH ROWS
  public function insertRow($db, $tN, string $r)
  {
    //INSERT INTO Маршрутизатор (url) VALUE ("ettet")
    $sql = "INSERT INTO $tN (url) VALUE (" . '"' . trim($r) . '"' . ")";
    $rs = mysqli_query($db, $sql) or trigger_error(mysqli_error($rs) . " in " . $sql); 
  }

  public function selectFirstRow($db, $tN)
  {
    $sql = "SELECT * FROM $tN url LIMIT 1";
    $rs = mysqli_query($db, $sql) or trigger_error(mysqli_error($rs) . " in " . $sql);
    return $rs;
  }

  public function deleteRow($db, $tN, $r)
  {
    $sql = "DELETE FROM $tN WHERE url = " . '"' . $r . '"';
    $rs = mysqli_query($db, $sql) or trigger_error(mysqli_error($rs) . " in " . $sql); 
  }

  public function selectAll($db, $tN)
  {
    $sql = "SELECT * FROM $tN";
    $rs = mysqli_query($db, $sql) or trigger_error(mysqli_error($rs) . " in " . $sql);
    return $rs;
  }

  public function showAll($rs) 
  {
    $allUrl = [];

    while ($row = $rs->fetch_assoc())
    {
      $r = $row['url'];
      printMessage($r, "");
    }
  }


  public function close()
  {
    $db->close();
    echo 'Connection closed'; 
  }
}
