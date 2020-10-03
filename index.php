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
<body>
<form method="POST" id="form"> 
  <p><b>Статья на ru.wikipedia.org || en.wikipedia.org</b><br>
    <input class="i" id="i" autocomplete="off" type="text" size="40" name="initialArticle" placeholder="Example: https://ru.wikipedia.org/wiki/Лошади || https://en.wikipedia.org/wiki/Horse">
  </p>
  <p>
    <input id="show" name="send" type="submit" value="Показать ссылки из статьи">
    <input id="reset" name="reset" type="submit" value="Очистить">
    <input id="pdf" name="pdf" type="submit" value="Создать PDF">
  </p>
  <p class="foundMessage" id="message"></p>
</form>

<form method="POST" id="form"> 
  <p><b>Создадим 1 PDF из 1 статьи en.wikiquote.org</b><br>
    <input class="i" id="onePdf" autocomplete="off" type="text" size="40" name="oneArticle" placeholder="Example: https://en.wikiquote.org/wiki/Naivety">
  </p>
  <p>
    <input id="reset" name="reset" type="submit" value="Очистить">
    <input name="onePdf" type="submit" value="Создать 1 PDF из данной статьи">
  </p>
  <p class="foundMessage" id="onePdfMessage"></p>
</form>
<div class="container"></div>
</body>
</html>

<style>
  body {
  background-color: #000;
  background-image: radial-gradient(
    rgba(0, 150, 0, 0.75), #000 120%
  );
  color: #FFF;
  font: 1.2rem Inconsolata, monospace;
  text-shadow: 0 0 1px #999;
  }
 
  form { 
    margin: 0 auto; 
    margin-top: .5rem;
    border: 1px solid #FFF;
    width: 60%;
    padding: .5rem;
  }

  #input {
    text-align: left;
  }
  
  form * {
    margin: 0 auto;
    margin-top: .25rem;
    padding: .25rem;
    margin-bottom: .25rem;
    text-align: center;
  }
  
  p {
    margin: .25rem;
  }
  
  .container {
    margin-bottom: .25rem;
    text-align: left;
  }

  .foundMessage {
    font-weight: 200;
    color: #FFF;
    font: 1rem Inconsolata, monospace;
    text-shadow: 0 0 5px #C8C8C8;
    margin: .25rem 0;
    padding: .25rem 0;
  }

  input {
    z-index: 100;
    color: #FFF;
    background-color: transparent;
    font: .8rem Inconsolata, monospace;
    text-shadow: 0 0 5px #C8C8C8;
    margin: .25rem;
    border: 1px solid #FFF;
  }
  input:hover {
    cursor: pointer;
    text-shadow: 0 1px 10px #C9C9C9;
  }

  .i {
    padding: .5rem;
    width: 80%;
    margin: .5rem;
  }
  .i:hover {
    cursor: auto;
  }

</style>


<?php

set_time_limit(6000);

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

    require_once "DB.php";
    require_once "PdfWikipedia.php";
    require_once "components/functions.php";

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
                    $pdf = new PdfLoaderWikipedia($articleUrl);
                    $pdf->savePdf($articleUrl);

                    //удалить использованную статью
                    $db->deleteRow($db_, $tableName, $articleUrl);
                    printMessage("Создан PDF: <b><i>" . $articleUrl . "</i></b>");
                }
            } catch (Exception $e) { echo 'Выброшено исключение: ' . $e . "\n"; } 
        }
    }
}


//CREATE 1 PDF OF 1 ARTICLE en.wikiquote.org
//get initial url html to grab needed links
$divStart_pStart = <<<EOT
<div class='container'>
  <p style='font-size: 13px; margin: 0; text-align: left; border: 1px dashed #FFF; padding: .25rem;' class='containerp'>
EOT;
$divEnd = "</div>";
$pEnd = "</p>";

if (isset($_POST["onePdf"])) 
{
    if ( $_POST["oneArticle"] !== '' )
    {
        $url = trim($_POST["oneArticle"]);

        require_once "DB.php";
        require_once "PdfWikiquote.php";
        require_once "components/functions.php";

        $db = new DataBase();
        $db_ = $db->connect();
          
        //сохраняет значение инпута и пишет сообщение с $url
        echo <<<EOL
        <script>
        var p = document.getElementById("onePdfMessage");
        var i = document.getElementById("onePdf");
        p.innerHTML = "$url";
        i.value = "$url";
        i.style.color = "#FFF;"
        </script>
        EOL;

        $pdf = new PdfLoaderWikiquote($url);
        $tableName = substr($url, strpos($url, 'wiki/') +5);
        $tableName = str_replace("(", "_", $tableName);     //delete ( ) from $tableName
        $tableName = str_replace(")", "_", $tableName);     //delete ( ) from $tableName
        $tableName = str_replace(",", "_", $tableName);
        $tableName .= "____WIKIQUOTE_ONE_ARTICLE";

        // IF NOT TABLE EXIST
        if (!($db->tableExists($db_, $tableName))) 
        {
            //CREATE TABLE
            $db->createIninitalArticleTable($db_, $tableName);

            //create PDF
            $pdfTitle = substr($url, strpos($url, 'wiki/') +5);
            $pdfTitle = str_replace("(", "_", $pdfTitle);
            $pdfTitle = str_replace(")", "_", $pdfTitle);
            $pdfTitle = str_replace("/", "_", $pdfTitle);

            //if , in $pdfTitle
            if (strpos($pdfTitle, ",")) 
            {
                $pdfTitle = str_replace(",", "_", $pdfTitle);  
            }

            //создаем пдф
            $pdf = new PdfLoaderWikiquote($url);
            $pdf->savePdf($url);
            echo "<br><b>Создан PDF из статьи: $url<b><br>"; 
            die;
        }

        //IF TABLE EXISTS - PDF from $url was already created
        if ($db->tableExists($db_, $tableName)) 
        {
            echo $divStart_pStart . "<b>Таблица <b>$tableName</b> существует</b><br>" . $pEnd . $divEnd;
            echo $divStart_pStart . "<b>Таблица <b>$tableName</b> существует, значит, статья $url уже конвертирована в PDF...</b><br><b>Не делаем ничего</b><br>" . $pEnd . $divEnd;

            unset($db);
            unset($db_);
            unset($pdf);
            die;
        }
    }
}