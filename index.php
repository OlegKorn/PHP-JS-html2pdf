<!--AJAX-->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.js"></script>
<script>
  $(document).ready(function() {

    $("#show").click(function(event) {
      event.preventDefault();

      var i = $("#i").val();
      var show = $("#show").val();

      $(".container").load("showajax.php", {
        i: i,
        show: show,
      });
    });
  });
</script>

<html class="html">
<form method="POST" id="form"> 
  <p><b>Статья на ru.wikipedia.org || en.wikiquote.org</b><br>
    <input id="i" autocomplete="off" type="text" size="40" name="initialArticle">
  </p>
  <p>
    <input id="show" name="send" type="submit" value="Показать ссылки из статьи">
    <input id="reset" name="reset" type="submit" value="Очистить">
    <input id="pdf" name="pdf" type="submit" value="Создать PDF">
  </p>
  <p class="foundMessage" id="message"></p>
</form>
<div class="container"></div>

<style>
  form { 
    margin: 0 auto; 
    margin-top: 2rem;
    border: 2px solid #6807f9;
    color: #6807f9;
    width:40%;
    padding: 1rem;
  }

  #input {
    text-align: left;
  }
  
  form * {
    margin: 0 auto;
    margin-top: .25rem;
    margin-bottom: .25rem;
    text-align: center;
  }
  
  p {
    margin: .5rem;
  }
  
  .container {
    margin-bottom: .25rem;
    text-align: left;
  }

  .foundMessage {
    font-weight: 200;
    font-style: Helvetica, Arial, sans-serif;
    color: #6807f9;
    font-size: 15px;
    margin: .25rem 0;
    padding: .25rem 0;
  }

  input {
    margin: .25rem;
  }

  #i {
    color: "";
    margin: 0;
    -ms-text-align-last: left;
    text-align-last: left;
  }
</style>


<?php

set_time_limit(600);

ini_set("memory_limit", "512M");
error_reporting(E_ALL);


$infoMessage = null;
$counter = null;

function contains($haystack, $needle)
{
    return strpos($haystack, $needle) !== false;
}

//CREATING PDF
if (isset($_POST["pdf"])) 
{
    $url = urldecode($_POST["initialArticle"]);

    if (contains($url, 'ru.wikipedia'))
    {
        require_once "DB.php";
        require_once "PdfWikipedia.php";
        require_once "components/functions.php";
        $className = "PdfLoaderWikipedia";
    }
    
    if (contains($url, 'en.wikiquote'))
    {
        require_once "DB.php";
        require_once "PdfWikiquote.php";
        require_once "components/functions.php";
        $className = "PdfLoaderWikiquote";
    }

    $db = new DataBase();
    $db_ = $db->connect();
      
    //вставляет в <p class="foundMessage" id="message"></p> текст ссылки
    echoJS("", $url, "");

    $tableName = substr($url, strpos($url, 'wiki/') +5);
    $tableName = str_replace("(", "_", $tableName);
    $tableName = str_replace(")", "_", $tableName);
    $tableName = str_replace(",", "_", $tableName);

    // IF NOT TABLE EXIST
    if (!($db->tableExists($db_, $tableName))) 
    {
        //CREATE INSTANCE OF PdfLoader()
        $pdf = new PdfLoader($url);
        $links_ = $pdf->getLinks();
        $fullLinks_ = $pdf->purifyLinks($links_);

        //CREATE TABLE
        $db->createIninitalArticleTable($db_, $tableName);
        
        //ВСТАВЛЯЕМ ССЫЛКИ В ТАБЛИЦУ
        foreach ($fullLinks_ as $row) 
        {
            $db->insertRow($db_, $tableName, $row);
        }
        //ПОКАЖЕМ ССЫЛКИ 
        $rs = $db->selectAll($db_, $tableName);
        $db->showAll($rs);
    }

    //ТАБЛИЦА СУЩЕСТВУЕТ
    if ($db->tableExists($db_, $tableName)) 
    {
        echo "<b>Таблица $tableName существует</b>";
      
        //TABLE EMPTY
        //THIS MEANS IT HAD ALREADY BEEN IN WORK
        //SO WE MUST DO NOTHING
        if ($db->isTableEmpty($db_, $tableName)) 
        { 
            echo "<br><b>Таблица $tableName пустая, значит, была в работе...</b><br><b>Не делаем ничего</b><br>";
        }
      
        //ТАБЛИЦА НЕ ПУСТАЯ
        //ЗНАЧИТ НАДО СКАЧАТЬ ОСТАТКИ СТАТЕЙ
        //И УДАЛИТЬ ИХ 
        if ( !($db->isTableEmpty($db_, $tableName)) ) 
        {
            echo "<br><b>Таблица $tableName не пустая</b><br>";
            echo "<br><b>Вот что осталось в таблице $tableName</b><br>";

            //ПОКАЖЕМ ССЫЛКИ 
            $rs = $db->selectAll($db_, $tableName);
            $allUrl_ = $db->showAll($rs);
            
            if ( (!empty($allUrl_)) )
            {
                foreach ($allUrl_ as $url_)
                {
                    printMessage("", $url_);      
                }
            }
            
            echo "<h1>CКАЧИВАЕМ...</h1><br><br>";

            $downloaded = FALSE;
            //СКАЧИВАЕМ ОСТАТКИ СТАТЕЙ ИЗ ТАБЛИЦЫ ПОКА ОНА НЕ ПУСТАЯ
            try 
            {
                while ( !$downloaded )
                {
                    if ($db->isTableEmpty($db_, $tableName)) 
                    { 
                        echo "<h2>Таблица $tableName пустая, скачаны остатки статей<h2><br><br>";
                        $downloaded = TRUE;
                    }

                    //берем статью из БД
                    //для сохранения ПДФ отсечем от ссылки статьи имя
                    $rs = $db->selectFirstRow($db_, $tableName);
                    $row = $rs->fetch_assoc();
                    $articleUrl = trim($row['url']);

                    $pdfTitle = substr($articleUrl, strpos($articleUrl, 'wiki/') +5);
                    $pdfTitle = str_replace("(", "_", $pdfTitle);
                    $pdfTitle = str_replace(")", "_", $pdfTitle);
                    $pdfTitle = str_replace("/", "_", $pdfTitle);
                    
                    //if , in $pdfTitle
                    if (strpos($pdfTitle, ",")) 
                    {
                        $pdfTitle = str_replace(",", "_", $pdfTitle);  
                    }

                    //if Warning: file_get_contents(): Filename cannot be empty
                    if (empty($pdfTitle)) 
                    {
                        echo "<b>Скачалось все.</b>";
                        die;
                    }

                    //создаем пдф
                    $pdf = new $className($articleUrl);
                    $pdf->savePdf($articleUrl);

                    //удалить использованную статью
                    $db->deleteRow($db_, $tableName, $articleUrl);
                    printMessage("Создан PDF: <b><i>" . $articleUrl . "</i></b>");
                }
            } catch (Exception $e) { echo 'Выброшено исключение: ' . $e . "\n"; } 
        }
    }
}
