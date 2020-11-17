<?php

namespace ISVU;

/**
 * Class ISVUStudentovStudij
 * @package ISVU
 */
class ISVUStudentovStudij
{
    private $isvu;

    /**
     * Counstructor
     */
    public function __construct($isvu)
    {
        $this->isvu = $isvu;
    }

    public function getSPID($url)
    {
        $connector = new Connector();
        $sp_data = $connector->get($url);

        return $sp_data['_embedded']['studentoviStudiji'][0]['_embedded']['upisaniElementStruktureStudija']['sifra'];
    }

    public function getSPData($url)
    {
        $data = [];

        $connector = new Connector();
        $sp_data = $connector->get($url);

        $data['los_identifier'] = $sp_data['_embedded']['studentoviStudiji'][0]['_embedded']['upisaniElementStruktureStudija']['sifra'];
        $data['title'] = $sp_data['_embedded']['studentoviStudiji'][0]['_embedded']['upisaniElementStruktureStudija']['naziv'];
        $data['type'] = "Degree Programme";

        $sp_start = $sp_data['_embedded']['studentoviStudiji'][0]['_embedded']['upisNaRazinuStudija']['datumUpisa'];
        $data['sp_start_hr'] = $sp_start;
        $day = substr($sp_start, 0, 2);
        $month = substr($sp_start, 3, 2);
        $year = substr($sp_start, 6, 4);
        $data['sp_start'] = $year . '-' . $month . '-' . $day;

        $sp_end = $sp_data['_embedded']['studentoviStudiji'][0]['_embedded']['upisNaRazinuStudija']['datumUpisa'];
        $data['sp_end_hr'] = $sp_end;
        $day = substr($sp_end, 0, 2);
        $month = substr($sp_end, 3, 2);
        $year = substr($sp_end, 6, 4);
        $data['sp_end'] = $year . '-' . $month . '-' . $day;

        $data['status'] = "studira";
        $data['gpa'] = $sp_data['_embedded']['studentoviStudiji'][0]['prosjek'];
        $data['wgpa'] = $sp_data['_embedded']['studentoviStudiji'][0]['tezinskiProsjek'];

        $data['credit_scheme'] = "ects";
        $data['credit_level'] = "Bachelor";
        $data['credit_value'] = 180;
        $data['current_credit_value'] = 0;

        $data['eqf_level'] = 6;

        $data['sp_element_link'] = $sp_data['_embedded']['studentoviStudiji'][0]['_embedded']['upisaniElementStruktureStudija']['_links']['nastavniprogram_ess']['href'];

        return $data;
    }
}
