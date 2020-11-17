<?php

namespace ISVU;

/**
 * Class ISVUStudentOIB
 * @package ISVU
 */
class ISVUStudentOIB
{
    private $isvu;

    /**
     * Counstructor
     */
    public function __construct($isvu)
    {
        $this->isvu = $isvu;
    }

    public function getStudentData($oib)
    {
        $data = [];

        // open connector
        $connector = new Connector();

        $url = "https://www.isvu.hr/api/vu/" . $this->isvu . "/student/v2/oib/" . $oib;
        $person = $connector->get($url);

        $data['isvu_id'] = (string)$this->isvu;
        $data['jmbag'] = $person['_embedded']['osobniPodaci']['jmbag'];
        $data['executor_name'] = $person['_embedded']['studentNaVisokomUcilistu']['_embedded']['studiraVisokoUciliste']['naziv'];

        // Init variables
        $data['executor_address'] = "";
        $data['schac'] = "";
        $data['url'] = "";

        // Fill initialized variables
        if (isset($person['_embedded']['elektronickiIdentitet']['adresa']))
        {
            $data['executor_address'] = $person['_embedded']['elektronickiIdentitet']['adresa'] . ', ' . $person['_embedded']['elektronickiIdentitet']['_embedded']['mjesto']['postanskaOznaka'] . ' ' . $person['_embedded']['elektronickiIdentitet']['_embedded']['mjesto']['naziv'];
        }

        if (isset($person['_embedded']['elektronickiIdentitet']['oznaka']))
        {
            $data['schac'] = substr($person['_embedded']['elektronickiIdentitet']['oznaka'], strpos($person['_embedded']['elektronickiIdentitet']['oznaka'], '@') + 1);
            $data['url'] = "www." . substr($person['_embedded']['elektronickiIdentitet']['oznaka'], strpos($person['_embedded']['elektronickiIdentitet']['oznaka'], '@') + 1);
            if (strrpos($data['url'], '/') != FALSE)
            {
                $data['url'] = substr($data['url'], 0, strpos($data['url'], '/'));
            }
        }

        $data['study_link'] = $person['_links']['student_student_studentovstudij']['href'];
        $data['summary_link'] = $person['_links']['student_student_sumarnipodaci']['href'];

        return $data;
    }
}
