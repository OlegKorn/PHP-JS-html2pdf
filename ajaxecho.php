<?php

if (isset($_POST["ajaxbut"])) 
{
    $ajaxinput = $_POST["ajaxinput"];

    echo <<<EOT
    <script> 
    var div = document.createElement("div");
    div.style.cssText = "margin:0 auto; margin-top:.1rem; width:40%; padding:0;"; 
    div.classList.add("container");
    div.innerHTML = "<p style='margin: 0; text-align: left; border: 1px dashed #0074D9; padding: .25rem;' class='containerp' style>$ajaxinput</p>";
    document.body.append(div);
    </script> 
    EOT;
}

    /*
let div = document.createElement("div");
    div.className = container;
    div.innerHTML = "fsdf";
    document.body.append(div); 
    */