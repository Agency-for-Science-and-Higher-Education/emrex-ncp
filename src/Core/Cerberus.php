<?php
namespace Core;

use Exception;

/**
 * Class Cerberus
 * @package Core
 */
class Cerberus
{
    private $db;

    /**
     * Counstructor
     */
    public function __construct()
    {
        $this->db = getDB();
    }

    /**
     * Check if the entered username and password match the ones stored in the database.
     * @param String $oib
     * @return bool TRUE on success, FALSE on failure
     */
    function authenticateNIAS($oib)
    {
        try
        {
            // Check if OIB is valid
            if (Validator::validateOIB($oib) === TRUE)
            {
				$stmt = $this->db->prepare("INSERT INTO cerberus.emrex_access (oib, timestamp) VALUES (:oib, now())");
				$stmt->bindParam(':oib', $oib);
				$stmt->execute();

				
                if (!isset($_SESSION['SMP']))
                {
                    $session = session_id();
                    $userAgent = $_SERVER['HTTP_USER_AGENT'];
                    $_SESSION['session'] = $session;
                    $_SESSION['userAgent'] = $userAgent;
                    $_SESSION['authenticated'] = 1;
					$_SESSION['type'] = 1;
					$_SESSION['oib'] = $oib;
					$_SESSION['active'] = 1;
					$_SESSION['source'] = 'nias';
					$_SESSION['keys'] = [];
                }

                // Pull data from OIB service and ISVU
                $isvu = new ISVU();
                $sync_oib  = $isvu->syncWithOIB($oib);
                $sync_isvu = $isvu->syncWithISVU($oib);

                return TRUE;

                if (($sync_oib === TRUE) && ($sync_isvu === TRUE))
                {
                    if ($this->logIn($oib) == 201)
                    {
                        return TRUE;
                    }
                    else
                    {
                        return FALSE;
                    }
                }
                // I don't know what the fuck happened
                else
                {
                    ErrorLog::emrexLog(702, 0, 'I dont know what the fuck happened');
                    return 702;
                }
            }
            else
            {
                // OIB validation error
                ErrorLog::emrexLog(607, 0, 'OIB validation error');
                return 607;
            }
        }
        catch (Exception $error)
        {
            return FALSE;
        }
    }

    /**
     * Check if the entered username and password match the ones stored in the database.
     * @param Array $data
     * @return bool TRUE on success, FALSE on failure
     */
    function authenticateeIDAS($data)
    {
        try
        {
            $pin = $data['http://eidas.europa.eu/attributes/naturalperson/PersonIdentifier'][0];

            // Pull data from OIB service and ISVU
            $isvu = new ISVU();
            $sync_oib  = $isvu->syncWithOIB($data);
            $sync_isvu = $isvu->syncWithISVU($data);

            if (($sync_oib === TRUE) && ($sync_isvu === TRUE))
            {
                if ($this->logIn($data) == 201)
                {
                    return TRUE;
                }
                else
                {
                    return FALSE;
                }
            }
            // I don't know what the fuck happened
            else
            {
                ErrorLog::emrexLog(702, 0, 'I dont know what the fuck happened');
                return 702;
            }
        }
        catch (Exception $error)
        {
            return FALSE;
        }
    }

    /**
     * Check if the entered username and password match the ones stored in the database.
     * @param String $oib
     * @return bool TRUE on success, FALSE on failure
     */
    function logIn($oib)
    {
        $stmt = $this->db->prepare('INSERT INTO cerberus.emrex_login_log (pid, timestamp) VALUES (:id, now())');
        $stmt->bindParam(':oib', $oib);
        $stmt->execute();

        $_SESSION['authenticated'] = 1;
        $_SESSION['oib'] = $oib;
        $_SESSION['source'] = 'nias';
        // Logged In
        return 201;
    }
}