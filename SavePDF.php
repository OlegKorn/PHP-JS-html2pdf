<?php

require_once 'dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;
use Dompdf\Cache;


class SavePDF {

  function save($html, $title) {
    $options = new Options();

    $options->set('defaultFont', 'DejaVu Sans'); 

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);

    $dompdf->setPaper('A4', 'landscape');

    $dompdf->render($title);
   
    $dompdf->stream($title);
    $dompdf->clear();
  }
}