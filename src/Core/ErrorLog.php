<?php
namespace Core;

/**
 * Class ErrorLog
 * @package GW\Core
 */
class ErrorLog
{
    /**
     * Write error details.
     * @param $code
     * @param $pid
     * @param $message
     */
    public static function emrexLog($code, $pid, $message)
    {
        $db = getDB();
        $session = (isset($_SESSION['session'])) ? $_SESSION['session'] : '0';
        $query  = "INSERT INTO cerberus.emrex_errors (code, pid, session, message, timestamp) VALUES ({$code}, {$pid}, '{$session}', '{$message}', now())";
        $db->exec($query);
    }
}