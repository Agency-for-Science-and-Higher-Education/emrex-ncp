<?php
namespace Core;

/**
 * Class Validator
 * @package Core
 */
class Validator
{
    /**
     * @param $oib
     * @return bool
     */
    public static function validateOIB($oib)
    {
        if (strlen($oib) === 11)
        {
            if (is_numeric($oib))
            {
                $x = 10;
                for ($i = 0; $i < 10; $i++)
                {
                    $x = $x + intval(substr($oib, $i, 1), 10);
                    $x = $x % 10;
                    if ($x === 0)
                    {
                        $x = 10;
                    }
                    $x *= 2;
                    $x = $x % 11;
                }
                $controlNumber = 11 - $x;
                if ($controlNumber === 10)
                {
                    $controlNumber = 0;
                }
                return $controlNumber === intval(substr($oib, 10, 1), 10);
            }
        }
        return FALSE;
    }
}