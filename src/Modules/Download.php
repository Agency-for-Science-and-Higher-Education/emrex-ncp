<?php
namespace Modules;

use EMREX\EMREXUniversity;
use EMREX\EMREXHighSchool;
use DateTime;
use Slim\Http\Request;
use Slim\Http\Response;

class Download
{
    function __construct($container)
    {
        $this->container = $container;
    }

    // GET https://emrex.studij.hr/transfer
    function transfer(Request $request, Response $response)
    {
        $isvu_id = $request->getAttribute('isvu');

        $emrex = ($isvu_id == 0) ? new EMREXHighSchool() :new EMREXUniversity();
        $content = $emrex->getXML($isvu_id, 1);

        $date = new DateTime();
        $filename = rand(1, 10000).$date->getTimestamp().rand(1, 10000);

        $file = fopen($filename, "w") or die("Unable to open file!");
        fwrite($file, $content);
        fclose($file);
        header('Content-Type: text/xml; charset=utf-8');
        readfile($filename);
        unlink($filename);
    }

    // GET https://emrex.studij.hr/download/xml
    function getXML(Request $request, Response $response)
    {
        $isvu_id = $request->getAttribute('isvu');

        $emrex = ($isvu_id == 0) ? new EMREXHighSchool() :new EMREXUniversity();
        $content = $emrex->getXML($isvu_id, 0);

        $date = new DateTime();
        $filename = rand(1, 10000).$date->getTimestamp().rand(1, 10000);

        $file = fopen($filename.'.xml', "w") or die("Unable to open file!");
        fwrite($file, $content);
        fclose($file);
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename='.$filename.'.xml');
        readfile($filename.'.xml');
        unlink($filename.'.xml');
    }

    // GET https://emrex.studij.hr/download/pdf
    function getPDF(Request $request, Response $response)
    {
        $isvu_id = $request->getAttribute('isvu');

        $emrex = ($isvu_id == 0) ? new EMREXHighSchool() :new EMREXUniversity();
        $emrex->getPDF($isvu_id, 0);
    }
}