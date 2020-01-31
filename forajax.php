<?php

$divStart_pStart = <<<EOT
<div class='container'>
  <p style='margin: 0; text-align: left; border: 1px dashed #0074D9; padding: .25rem;' class='containerp'>
EOT;
$divEnd = "</div>";
$pEnd = "</p>";


//get initial url html to grab needed links
if (isset($_POST["show"])) 
{
    echo 'show';
    
    if ( $_POST["i"] !== '' )
    {
        $url = urldecode($_POST["i"]);

        require_once "DB.php";
        require_once "PdfConverter.php";
        require_once "components/functions.php";

        $db = new DataBase();
        $db_ = $db->connect();
      
        //вставляет в <p class="foundMessage" id="message"></p> текст ссылки
        echoJS("", $url, "");

        $pdf = new PdfLoader($url);
        $links_ = $pdf->getLinks();
        $fullLinks_ = $pdf->purifyLinks($links_);

        $tableName = substr($url, strpos($url, 'wiki/') +5);
        $tableName = str_replace("(", "_", $tableName);     //delete ( ) from $tableName
        $tableName = str_replace(")", "_", $tableName);     //delete ( ) from $tableName

        // IF NOT TABLE EXIST
        if (!($db->tableExists($db_, $tableName))) 
        {
            //CREATE TABLE
            $db->createIninitalArticleTable($db_, $tableName);
        
            //ВСТАВЛЯЕМ ССЫЛКИ В ТАБЛИЦУ
            foreach ($fullLinks_ as $row) 
            {
                $db->insertRow($db_, $tableName, $row);
            }
        
            //ВЫВЕДЕМ ИЗ ТАБЛИЦЫ СУЩЕСТВУЮЩИЕ ТАМ ЗАПИСИ (ОСТАВШИЕСЯ)
            $rs = $db->selectAll($db_, $tableName);
            $allUrl_ = $db->showAll($rs);

            foreach ($allUrl_ as $url) 
            {
                echo <<<EOT
                <script> 
                var div = document.createElement("div");
                div.style.cssText = "margin:0 auto; margin-top:.1rem; width:40%; padding:0;"; 
                div.classList.add("container");
                div.innerHTML = "<p style='margin: 0; text-align: left; border: 1px dashed #0074D9; padding: .25rem;' class='containerp' style>$url</p>";
                document.body.append(div);
                </script> 
                EOT;
            }
            die;
        }

        //IF TABLE EXISTS - IT'S SUPPOSED IT'S ALREADY BEEN IN WORK
        //PRINT OFF THE ROWS
        if ($db->tableExists($db_, $tableName)) 
        {
            echo $divStart_pStart . "<b>Таблица <b>$tableName</b> существует</b><br>" . $pEnd . $divEnd;
            
            if (!$db->isTableEmpty($db_, $tableName)) 
            {
                echo $divStart_pStart . "<br><b>Таблица $tableName не пустая</b><br>Выведем записи из таблицы: <b>$tableName</b><br>" . $pEnd . $divEnd;
                
                //ВЫВЕДЕМ ИЗ ТАБЛИЦЫ СУЩЕСТВУЮЩИЕ ТАМ ЗАПИСИ (ОСТАВШИЕСЯ)
                $rs = $db->selectAll($db_, $tableName);
                $allUrl_ = $db->showAll($rs);

                foreach ($allUrl_ as $url) 
                {
                    echo <<<EOT
                    <script> 
                    var div = document.createElement("div");
                    div.style.cssText = "margin:0 auto; margin-top:.1rem; width:40%; padding:0;"; 
                    div.classList.add("container");
                    div.innerHTML = "<p style='margin: 0; text-align: left; border: 1px dashed #0074D9; padding: .25rem;' class='containerp' style>$url</p>";
                    document.body.append(div);
                    </script> 
                    EOT;
                }
            }  
      
            //TABLE EMPTY
            //THIS MEANS IT HAD ALREADY BEEN IN WORK
            //SO WE MUST DO NOTHING
            if ($db->isTableEmpty($db_, $tableName)) 
            { 
              echo $divStart_pStart . "<b>Таблица <b>$tableName</b> пустая, значит, была в работе...</b><br><b>Не делаем ничего</b><br>" . $pEnd . $divEnd;
            }

            unset($db);
            unset($db_);
            die;
        }
    }

}

unset($db);
unset($db_);
unset($pdf);
unset($links_);
unset($fullLinks_);


//CREATING PDF
if (isset($_POST["pdf"])) 
{
    echo "test";
    
    if ( $_POST["i"] !== '' )
    {
        echo "not empty";
    }

    if ( $_POST["i"] === '' )
    {
        echo "empty";
    }

        /*
        $url = urldecode($_POST["i"]);

        require_once "DB.php";
        require_once "components/functions.php";

        $db = new DataBase();
        $db_ = $db->connect();
      
        //вставляет в <p class="foundMessage" id="message"></p> текст ссылки
        echoJS("", $url, "");

        $pdf = new PdfLoader($url);
        $links_ = $pdf->getLinks();
        $fullLinks_ = $pdf->purifyLinks($links_);

        $tableName = substr($url, strpos($url, 'wiki/') +5);
        $tableName = str_replace("(", "_", $tableName);     //delete ( ) from $tableName
        $tableName = str_replace(")", "_", $tableName);     //delete ( ) from $tableName

        // IF NOT TABLE EXIST
        if (!($db->tableExists($db_, $tableName))) 
        {
            //CREATE TABLE
            $db->createIninitalArticleTable($db_, $tableName);

            //ВСТАВЛЯЕМ ССЫЛКИ В ТАБЛИЦУ
            foreach ($fullLinks_ as $row) 
            {
                $db->insertRow($db_, $tableName, $row);
            }
        }

        //ТАБЛИЦА СУЩЕСТВУЕТ
        if ($db->tableExists($db_, $tableName)) 
        {
            //TABLE EMPTY
            //THIS MEANS IT HAD ALREADY BEEN IN WORK
            //SO WE MUST DO NOTHING
            if ($db->isTableEmpty($db_, $tableName)) 
            { 
                echo $divStart_pStart . "<br><b>Таблица $tableName пустая, значит, была в работе...</b><br><b>Не делаем ничего</b><br>" . $pEnd . $divEnd;
            }
          
            //ТАБЛИЦА НЕ ПУСТАЯ
            //ЗНАЧИТ НАДО СКАЧАТЬ ОСТАТКИ СТАТЕЙ
            //И УДАЛИТЬ ИХ 
            if ( !($db->isTableEmpty($db_, $tableName)) ) 
            {
                echo $divStart_pStart . "<br><b>Таблица $tableName не пустая</b><br>" . $pEnd . $divEnd;
                echo $divStart_pStart . "<br><b>Вот что осталось в таблице $tableName</b><br>" . $pEnd . $divEnd;
                
                $downloaded = FALSE;
                //СКАЧИВАЕМ ОСТАТКИ СТАТЕЙ ИЗ ТАБЛИЦЫ ПОКА ОНА НЕ ПУСТАЯ
                try 
                {
                    while ( !$downloaded )
                    {
                        if ($db->isTableEmpty($db_, $tableName)) 
                        { 
                            echo $divStart_pStart . "<b>Таблица $tableName пустая, скачаны остатки статей</b>" . $pEnd . $divEnd;
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

                        //if Warning: file_get_contents(): Filename cannot be empty
                        if (empty($pdfTitle)) 
                        {
                            echo $divStart_pStart . "<b>Скачалось все.</b>" . $pEnd . $divEnd;
                            die;
                        }

                        //создаем пдф
                        require_once 'PdfConverter.php';
                        $pdf = new PdfLoader($articleUrl);
                        $pdf->savePdf($articleUrl, $pdfTitle);

                        //удалить использованную статью
                        $db->deleteRow($db_, $tableName, $articleUrl);
                                            
                        echo <<<EOT
                        <script> 
                        var div = document.createElement("div");
                        div.style.cssText = "margin:0 auto; margin-top:.1rem; width:40%; padding:0;"; 
                        div.classList.add("container");
                        div.innerHTML = "<p style='margin: 0; text-align: left; border: 1px dashed #0074D9; padding: .25rem;' class='containerp' style>СОЗДАН PDF -----------> <b>$articleUrl</b></p>";
                        document.body.append(div);
                        </script> 
                        EOT;
                    }
                } catch (Exception $e) { echo 'Выброшено исключение: ',  $e->POSTMessage(), "\n"; } 
            }
        }
    }*/
}