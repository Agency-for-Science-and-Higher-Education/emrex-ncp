<?php

namespace ISVU;

/**
 * Class ISVUI
 * @package ISVU
 */
class ISVUVI
{
    public function getInstitutions($oib)
    {
        $url = "https://www.isvu.hr/api/student/upisanavu/oib/" . $oib;

        $connector = new Connector();
        $result = $connector->get($url);

        $institution = array();

        if (isset($result['_embedded']['studentVisokaUcilista']))
        {
            foreach ($result['_embedded']['studentVisokaUcilista'] as $isvu_code)
            {
                $institution[] = $isvu_code['sifra'];
            }
        }

        return $institution;
    }
}
