<?php

$divStart_pStart = <<<EOT
<div class='container'>
  <p style='font-size: 16px; margin: 0; text-align: left; border: 1px dashed #FFF; padding: .25rem;' class='containerp'>
EOT;
$divEnd = "</div>";
$pEnd = "</p>";


function contains($haystack, $needle)
{
    return strpos($haystack, $needle) !== false;
}

//get initial url html to grab needed links
if (isset($_POST["show"])) 
{
    if ( $_POST["i"] !== '' )
    {
        $url = urldecode($_POST["i"]);


        //CASE ru.wikipedia.org
        if (contains($url, 'ru.wikipedia'))
        {
            require_once "DB.php";
            require_once "PdfWikipedia.php";
            require_once "components/functions.php";

            $db = new DataBase();
            $db_ = $db->connect();
          
            //вставляет в <p class="foundMessage" id="message"></p> текст ссылки
            echoJS("", $url, "");

            $pdf = new PdfLoaderWikipedia($url);
            $links_ = $pdf->getLinks();
            $fullLinks_ = $pdf->purifyLinks($links_);

            $tableName = substr($url, strpos($url, 'wiki/') +5);
            $tableName = str_replace("(", "_", $tableName);     //delete ( ) from $tableName
            $tableName = str_replace(")", "_", $tableName);     //delete ( ) from $tableName
            $tableName = str_replace(",", "_", $tableName);

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
                    div.innerHTML = "<p style='font-size: 16px; margin: 0; text-align: left; border: 1px dashed #FFF; padding: .25rem;' class='containerp' style>$url</p>";
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
                        div.innerHTML = "<p style='font-size: 16px; margin: 0; text-align: left; border: 1px dashed #FFF; padding: .25rem;' class='containerp' style>$url</p>";
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

        //CASE en.wikiquote.org
        if (contains($url, 'en.wikiquote'))
        {
            require_once "DB.php";
            require_once "PdfWikiquote.php";
            require_once "components/functions.php";

            $db = new DataBase();
            $db_ = $db->connect();
          
            //вставляет в <p class="foundMessage" id="message"></p> текст ссылки
            echoJS("", $url, "");

            $pdf = new PdfLoaderWikiquote($url);
            $links_ = $pdf->getLinks();
            $fullLinks_ = $pdf->purifyLinks($links_);

            $tableName = substr($url, strpos($url, 'wiki/') +5);
            $tableName = str_replace("(", "_", $tableName);     //delete ( ) from $tableName
            $tableName = str_replace(")", "_", $tableName);     //delete ( ) from $tableName
            $tableName = str_replace(",", "_", $tableName);

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
                    div.innerHTML = "<p style='font-size: 16px; margin: 0; text-align: left; border: 1px dashed #FFF; padding: .25rem;' class='containerp' style>$url</p>";
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
                        div.innerHTML = "<p style='font-size: 16px; margin: 0; text-align: left; border: 1px dashed #FFF; padding: .25rem;' class='containerp' style>$url</p>";
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
}

unset($db);
unset($db_);
unset($pdf);
unset($links_);
unset($fullLinks_);


