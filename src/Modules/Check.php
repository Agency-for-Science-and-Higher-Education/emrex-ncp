<?php
namespace Modules;

use Slim\Http\Request;
use Slim\Http\Response;

class Check
{
    function __construct($container)
    {
        $this->container = $container;
    }

    // GET https://emrex.studij.hr/provjera
    function check(Request $request, Response $response)
    {
        return $this->container->renderer->render($response, '../templates/check.html');
    }

    // GET https://emrex.studij.hr/check/{filename}
    function getHTML(Request $request, Response $response)
    {
        $filename = $request->getAttribute('filename');

        if (file_exists("../../pdf/".$filename))
        {
            $response = $response->withStatus(200)
                ->withHeader('Content-type', 'application/html')
                ->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Access-Control-Allow-Methods', 'GET, POST')
                ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With')
                ->write(file_get_contents( "../../pdf/".$filename));
            return $response;
        }
        else
        {
            $response = $response->withStatus(200)
                ->withHeader('Content-type', 'application/html')
                ->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Access-Control-Allow-Methods', 'GET, POST')
                ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With')
                ->write("Traženi dokument ne postoji.");
            return $response;
        }
    }
}