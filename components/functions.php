<?php

function printMessage(string $mess = NULL, string $url = NULL)
{
    echo <<<EOL
    <style> 
    .container 
    {
    margin: 0 auto; 
    margin-top: .1rem;
    width: 40%;
    padding: 0;
    }
    .container p 
    {
    margin: 0;
    text-align: left;
    border: 1px dashed #0074D9;
    padding: .25rem;
    }  
    </style>        
    <div class="container">
    <p>$mess $url</p>
    </div>
    EOL;
}


function echoJS(string $mess = NULL, string $url, string $tableName = NULL)
{
    echo <<<EOT
    <script>
    var p = document.getElementById("message");
    var i = document.getElementById("i");
    p.innerHTML = "$mess $tableName";
    i.value = "$url";
    i.style.color = "#0044D9;"
    </script>
    EOT;
}