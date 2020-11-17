<?php
namespace Modules;

require_once "/web/simplesaml/lib/_autoload.php";

use Slim\Http\Request;
use Slim\Http\Response;
use Core\Cerberus;

class Landing
{
    // Constructor
    function __construct($container)
    {
        $this->container = $container;
    }

    // Catch non authenticated users
    public function __invoke(Request $request, Response $response, $next)
    {
        $as = new \SimpleSAML_Auth_Simple("default-sp");
        if (!isset($_SESSION['authenticated']) || ($as->isAuthenticated() == false))
        {
            return $response->withStatus(403)->withHeader('Location','/');
        }
        $response = $next($request, $response);
        return $response;
    }

    public function check(Request $request, Response $response, $next)
    {
        $response = $response->withStatus(200)
            ->withHeader('Content-type', 'application/json')
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With')
            ->write(json_encode(1, JSON_FORCE_OBJECT));
        return $response;
    }

    function ip_in_range($ip)
    {
        $handle = fopen("../src/Modules/iprange.txt", "r");
        if ($handle)
        {
            while (($range = fgets($handle)) !== false)
            {
                if (strpos( $range, '/' ) == false)
                {
                    $range .= '/32';
                }
                // $range is in IP/CIDR format eg 127.0.0.1/24
                list ($range, $netmask) = explode('/', $range, 2);
                $range_decimal = ip2long($range);
                $ip_decimal = ip2long($ip);
                $wildcard_decimal = pow(2, (32 - $netmask)) - 1;
                $netmask_decimal = ~ $wildcard_decimal;
                if ( ($ip_decimal & $netmask_decimal) == ($range_decimal & $netmask_decimal) )
                {
                    fclose($handle);
                    return false;
                }
            }
            fclose($handle);
            return true;
        }
        else
        {
            return false;
        }
    }

    // GET https://emrex.studij.hr
    function index(Request $request, Response $response)
    {
		$as = new \SimpleSAML_Auth_Simple("default-sp");

        if (isset($_POST['returnUrl']))
        {
            $session = session_id();
            $userAgent = $_SERVER['HTTP_USER_AGENT'];
            $_SESSION['session'] = $session;
            $_SESSION['userAgent'] = $userAgent;
            $_SESSION['SMP'] = 1;
            $_SESSION['returnUrl'] = $_POST['returnUrl'];
            $_SESSION['sessionId'] = $_POST['sessionId'];
        }

        if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] == 1 && $as->isAuthenticated())
        {
            // Render panel view
            return $this->container->renderer->render($response, '../templates/panel.html');
        }
        else
        {
            // Render index view
            if ($this->ip_in_range($_SERVER['REMOTE_ADDR']))
            {
                return $this->container->renderer->render($response, '../templates/landing.html');
            }
            else
            {
                return $this->container->renderer->render($response, '../templates/landing_hr.html');
            }
        }
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // NIAS
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // GET https://emrex.studij.hr/login
    function login()
    {
        header('Access-Control-Allow-Origin', '*');
        header('Access-Control-Allow-Methods', 'GET, POST');
        header('Access-Control-Allow-Headers', 'X-Requested-With');

        if (isset($_POST['returnUrl']))
        {
            $session = session_id();
            $userAgent = $_SERVER['HTTP_USER_AGENT'];
            $_SESSION['session'] = $session;
            $_SESSION['userAgent'] = $userAgent;
            $_SESSION['SMP'] = 1;
            $_SESSION['returnUrl'] = $_POST['returnUrl'];
            $_SESSION['sessionId'] = $_POST['sessionId'];
        }

        try
        {
            $as = new \SimpleSAML_Auth_Simple("default-sp");

            $valid_saml_session = $as->isAuthenticated();
            if(!$valid_saml_session /*&& $redirect*/)
            {
                // Take the user to IdP and authenticate.
                $as->requireAuth(array(
                    'ReturnTo' => 'https://emrex.studij.hr/login',
                    'KeepPost' => FALSE
                ));
            }
            $valid_saml_session = $as->isAuthenticated();

            if($valid_saml_session)
            {
                if (array_key_exists('logout', $_REQUEST))
                {
                    self::logout();
                }
                if (array_key_exists(\SimpleSAML_Auth_State::EXCEPTION_PARAM, $_REQUEST))
                {
                    // This is just a simple example of an error.
                    $state = \SimpleSAML_Auth_State::loadExceptionState();
                    assert('array_key_exists(SimpleSAML_Auth_State::EXCEPTION_DATA, $state)');
                    $e = $state[\SimpleSAML_Auth_State::EXCEPTION_DATA];
                    $error = "Exception during login:\n";
                    foreach ($e->format() as $line) $error .= $line . "\n";
                    throw new Exception($error);
                }

                $attributes = $as->getAttributes();
                $cerberus = new Cerberus();
                if (isset($attributes['oib'][0]))
                {
                    if ($cerberus->authenticateNIAS($attributes['oib'][0]))
                    {
                        header('location: /');
                        exit(1);
                    }
                }
                else
                {
                    if ($cerberus->authenticateeIDAS($attributes))
                    {
                        header('location: /');
                        exit(1);
                    }
                }
            }
        }
        catch (Exception $e)
        {
            // SimpleSAMLphp is not configured correctly.
            throw(new Exception("SSO authentication failed: ". $e->getMessage()));
        }
        return null;
    }

    // GET https://emrex.studij.hr/log_out
    function log_out()
    {
		try
		{
			$as = new \SimpleSAML_Auth_Simple("default-sp");
			if ($as->isAuthenticated())
			{
                $attr = $as->getAttributes();

                //ako je strana vjerodajnica
                if (isset($attr['http://eidas.europa.eu/attributes/naturalperson/PersonIdentifier']))
                {
                    //echo "Korisnik je prijavljen sa stranom vjerodajnicom za koju ne postoji SingleSignOn niti SingleLogOut.<br>";
                    //echo "Zbog toga korisnika odjavimo samo iz lokalnog sessiona, ne koristeći NIAS logout servis.<br><br>";

                    $session = \SimpleSAML_Session::getSessionFromRequest();
                    if ($session->isValid("default-sp"))
                    {
                        $session->doLogout("default-sp");
                        session_destroy();
                        header('Location: https://emrex.studij.hr/');
                        exit(1);
                    }
                    session_destroy();
                    header('Location: https://emrex.studij.hr/');
                    exit(1);
                    //echo "-----";
                    //echo "Korisnik je odjavljen - simpleSAMLphp lokalni session više nije validan<br>";
                    //echo "<br>Idi na pocetnu stranicu za provjeru - <a href='/index.php'> POČETNA </a> <br><br>";

                }
                else
                {
                    $as->logout(array(
                        'ReturnTo' => 'https://emrex.studij.hr/logout',
                        'ReturnStateParam' => 'LogoutState',
                        'ReturnStateStage' => 'MyLogoutState',
                    ));
                }
			}
            else
            {
                session_destroy();
                header('Location: https://emrex.studij.hr/');
                exit(1);
            }
		}
		catch (Exception $e)
		{	// SimpleSAMLphp is not configured correctly.
			throw(new Exception("SSO authentication failed: ". $e->getMessage()));
		}
    }

    // GET https://emrex.studij.hr/logout
    function get_logout()
    {
		$as = new \SimpleSAML_Auth_Simple("default-sp");
		if ($as->isAuthenticated())
		{
			header('location: https://emrex.studij.hr');
			exit(1);
		}
		else
		{
			session_destroy();
			setcookie("SimpleSAMLAuthToken", "", time()-360000);
			setcookie("SimpleSAMLSessionID", "", time()-360000);
			header('Location: https://emrex.studij.hr');
			exit(1);
		}
	}

    // POST https://emrex.studij.hr/logout
    function post_logout()
    {
		if(isset($_SESSION['pass']))
		{
			$_SESSION['pass'] = $_SESSION['pass'] + 1;
		}
		else
		{
			$_SESSION['pass'] = 1;
		}

		$sourceId = "default-sp";
		$source = \SimpleSAML_Auth_Source::getById($sourceId);
		if ($source === NULL) throw new Exception('Could not find authentication source with id ' . $sourceId);
		if (!($source instanceof \sspmod_saml_Auth_Source_SP)) throw new \SimpleSAML_Error_Exception('Source type changed?');

		$binding = \SAML2_Binding::getCurrentBinding();
		$message = $binding->receive();
		$idpEntityId = $message->getIssuer();
		if ($idpEntityId === NULL)
		{
			// Without an issuer we have no way to respond to the message.
			throw new SimpleSAML_Error_BadRequest('Received message on logout endpoint without issuer.');
		}

		$spEntityId = $source->getEntityId();
		$metadata = \SimpleSAML_Metadata_MetaDataStorageHandler::getMetadataHandler();
		$idpMetadata = $source->getIdPMetadata($idpEntityId);
		$spMetadata = $source->getMetadata();
		\sspmod_saml_Message::validateMessage($idpMetadata, $spMetadata, $message);
		$destination = $message->getDestination();
		//if ($destination !== NULL && $destination !== SimpleSAML_Utilities::selfURLNoQuery()) throw new SimpleSAML_Error_Exception('Destination in logout message is wrong.');

		if ($message instanceof \SAML2_LogoutResponse)
		{
			$relayState = $message->getRelayState();
			if ($relayState === NULL)
			{
				// Somehow, our RelayState has been lost. 
				throw new \SimpleSAML_Error_BadRequest('Missing RelayState in logout response.');
			}
			//if (!$message->isSuccess()) SimpleSAML_Logger::warning('Unsuccessful logout. Status was: ' . sspmod_saml_Message::getResponseError($message));

			// Sanitize the input
			$sid = \SimpleSAML_Utilities::parseStateID($relayState);
			if (!is_null($sid['url'])) \SimpleSAML_Utilities::checkURLAllowed($sid['url']);

			$state = \SimpleSAML_Auth_State::loadState($relayState, 'saml:slosent');
			$state['saml:sp:LogoutStatus'] = \SAML2_Const::STATUS_SUCCESS;//$message->getStatus();
			\SimpleSAML_Auth_Source::completeLogout($state);
			$_SESSION['pass'] = 2;
		}
		elseif ($message instanceof \SAML2_LogoutRequest)
		{
			\SimpleSAML_Logger::debug('module/saml2/sp/logout: Request from ' . $idpEntityId);
			\SimpleSAML_Logger::stats('saml20-idp-SLO idpinit ' . $spEntityId . ' ' . $idpEntityId);

			if ($message->isNameIdEncrypted())
			{
				try
				{
					$keys = \sspmod_saml_Message::getDecryptionKeys($idpMetadata, $spMetadata);
				}
				catch (Exception $e)
				{
					throw new \SimpleSAML_Error_Exception('Error decrypting NameID: ' . $e->getMessage());
				}

				$blacklist = \sspmod_saml_Message::getBlacklistedAlgorithms($idpMetadata, $spMetadata);

				$lastException = NULL;
				foreach ($keys as $i => $key)
				{
					try
					{
						$message->decryptNameId($key, $blacklist);
						\SimpleSAML_Logger::debug('Decryption with key #' . $i . ' succeeded.');
						$lastException = NULL;
						break;
					}
					catch (Exception $e)
					{
						\SimpleSAML_Logger::debug('Decryption with key #' . $i . ' failed with exception: ' . $e->getMessage());
						$lastException = $e;
					}
				}
				if ($lastException !== NULL)
				{
					throw $lastException;
				}
			}

			$nameId = $message->getNameId();
			$sessionIndexes = $message->getSessionIndexes();
			$numLoggedOut = \sspmod_saml_SP_LogoutStore::logoutSessions($sourceId, $nameId, $sessionIndexes);
			if ($numLoggedOut === FALSE)
			{
				// This type of logout was unsupported. Use the old method.
				$source->handleLogout($idpEntityId);
				$numLoggedOut = count($sessionIndexes);
			}

			// Create an send response.
			$lr = \sspmod_saml_Message::buildLogoutResponse($spMetadata, $idpMetadata);
			$lr->setRelayState($message->getRelayState());
			$lr->setInResponseTo($message->getId());

			if ($numLoggedOut < count($sessionIndexes))
			{
				\SimpleSAML_Logger::warning('Logged out of ' . $numLoggedOut  . ' of ' . count($sessionIndexes) . ' sessions.');
			}
			$dst = $idpMetadata->getEndpointPrioritizedByBinding('SingleLogoutService', array(
				\SAML2_Const::BINDING_HTTP_REDIRECT,
				\SAML2_Const::BINDING_HTTP_POST)
			);

			if (!$binding instanceof \SAML2_SOAP)
			{
				$binding = \SAML2_Binding::getBinding($dst['Binding']);
				if (isset($dst['ResponseLocation']))
					$dst = $dst['ResponseLocation'];
				else
					$dst = $dst['Location'];
			}
		    //$lr->setDestination($dst);
			if (is_array($dst))
                $lr->setDestination($dst['Location']);
			else
                $lr->setDestination($dst);
			$binding->send($lr);
		}
		else
		{
			throw new \SimpleSAML_Error_BadRequest('Unknown message received on logout endpoint: ' . get_class($message));
		}
	}
}