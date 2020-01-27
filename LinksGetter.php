<?php 

class LinksHarvester {

  //аттрибут класса
  public $dom;
    

  //конструктор класса, который передает атрибуту значение
  public function __construct($dom)
  {
    $this->dom = $dom;
  }


  public function harvestLinks() 
  {
    //POST all a-tags from div.mw-body-content
    $links = [];
    $xPath = new DOMXPath($this->dom);
    $anchorTags = $xPath->evaluate("//div[@class=\"mw-body-content\"]//a/@href");

    //create an array[] of needed links to iterate trhough and to create PDF files from
    foreach ($anchorTags as $anchorTag) {
      
        //if link is not already in array:    
        if ( !in_array($anchorTag->nodeValue, $links) ) {
          $links[] = $anchorTag->nodeValue; 
          echo $anchorTag->nodeValue; 
        }    
    }
    return $links;
  }
}