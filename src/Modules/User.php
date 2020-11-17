<?php
namespace Modules;

use Exception;
use Slim\Http\Request;
use Slim\Http\Response;

class User
{
    function __construct($container)
    {
        $this->container = $container;
    }

    // GET https://emrex.studij.hr/user
    function user(Request $request, Response $response)
    {
        try
        {
            $lang = $request->getAttribute('lang');
            switch ($lang)
            {
                case 'hr': $lang = '';
                    break;
                case 'en': $lang = '_eng';
                    break;
                default: throw new Exception('Invalid language.');
                    break;
            }

            $isvu_id = $request->getAttribute('isvu');

            $person_data = $_SESSION['person'];
            
            if (isset($_SESSION['isvu'][$isvu_id]))
            {
				$person_data['university'] = $_SESSION['isvu'][$isvu_id];
				$person_data['universities'][] = 1;
				foreach($_SESSION['isvu'] as $isvu)
				{
					$person_data['universities'][] = $isvu['isvu_id'];
				}

				if ($isvu_id == 0) $person_data['university'] = $_SESSION['ematica'][$isvu_id];
			}

            if (isset($_SESSION['returnUrl']))
            {
                $person_data['returnUrl'] = $_SESSION['returnUrl'];
                $person_data['sessionId'] = $_SESSION['sessionId'];
            }

            $db = null;

            $response = $response->withStatus(200)
                ->withHeader('Content-type', 'application/json')
                ->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Access-Control-Allow-Methods', 'GET, POST')
                ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With')
                ->write(json_encode($person_data, JSON_FORCE_OBJECT));
            return $response;
        }
        catch (Exception $e)
        {
            return '{"error":{"text":' . $e->getMessage() . '}}';
        }
    }

    // GET https://emrex.studij.hr/executor
    function executor(Request $request, Response $response)
    {
        return $this->container->renderer->render($response, '../templates/panel.html');
    }

    // GET https://emrex.studij.hr/executors
    function executors(Request $request, Response $response)
    {
        $cookies = $request->getCookieParams();
        $lang = isset($cookies['NG_TRANSLATE_LANG_KEY']) ? $cookies['NG_TRANSLATE_LANG_KEY'] : '"hr"';
        switch ($lang)
        {
            case '"hr"': $lang = '';
                break;
            case '"en"': $lang = '_eng';
                break;
            default: $lang = '';
                break;
        }

        if (isset($_SESSION['SMP']))
        {
            $lang = '_eng';
        }

        $filename = "data/executors".$lang.".json";

        $response = $response->withStatus(200)
            ->withHeader('Content-type', 'application/json')
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With')
            ->write(file_get_contents( $filename ));
        return $response;
    }
}