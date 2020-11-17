<?php

namespace ISVU;

/**
 * Class ISVUSumarno
 * @package ISVU
 */
class ISVUSumarno
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

        return $sp_data['_embedded']['studentoviStudiji']['_embedded']['upisaniElementStruktureStudija']['sifra'];
    }

    public function getSummaryData($url, $study_link)
    {
        $data = [];

        $connector = new Connector();
        $summary_data = $connector->get($url);

        foreach ($summary_data['_embedded']['upisaniElementiStruktureStudija'] as $study)
        {
            $sp_id = $study['sifra'];

            // Get SP data
            $isvu_sp = new ISVUStudentovStudij($this->isvu);
            $sp_data = $isvu_sp->getSPData($study_link);
            $data[$sp_id]['sp_start'] = $sp_data['sp_start_hr'];
            $data[$sp_id]['sp_end'] = $sp_data['sp_end_hr'];
            $data[$sp_id]['gpa'] = $sp_data['gpa'];
            $data[$sp_id]['wgpa'] = $sp_data['wgpa'];
            $data[$sp_id]['eqf_level'] = $sp_data['eqf_level'];

            $sp_element_link = $sp_data['sp_element_link'];

            // Get SP element data
            $isvu_sp_element = new ISVUElementStruktureStudija($this->isvu);
            $sp_element_data = $isvu_sp_element->getSPElementData($sp_element_link);
            $data[$sp_id]['length'] = $sp_element_data['length'];

            // Get summary data
            $data[$sp_id]['los_identifier'] = $study['sifra'];
            $data[$sp_id]['title'] = $study['naziv'];
            $data[$sp_id]['type'] = "Degree Programme";
            $data[$sp_id]['status'] = "studira";
            $data[$sp_id]['credit_scheme'] = "ects";
            $data[$sp_id]['credit_level'] = "Bachelor";
            $data[$sp_id]['credit_value'] = 180;
            $data[$sp_id]['current_credit_value'] = 0;

            foreach ($study['_embedded']['polozeniPredmeti'] as $subject)
            {
                $isvu_subject = $subject['_embedded']['predmet']['sifra'];

                $data[$sp_id]['grades'][$isvu_subject]['identifier'] = $subject['_embedded']['predmet']['sifra'];
                $data[$sp_id]['grades'][$isvu_subject]['title'] = $subject['_embedded']['predmet']['naziv'];
                $data[$sp_id]['grades'][$isvu_subject]['type'] = "Course";
                $data[$sp_id]['grades'][$isvu_subject]['status'] = "passed";
                $data[$sp_id]['grades'][$isvu_subject]['resultLabel'] = $subject['_embedded']['ispit']['ocjena'];
                $data[$sp_id]['grades'][$isvu_subject]['value'] = $subject['_embedded']['predmet']['ectsBodovi'];
                $data[$sp_id]['grades'][$isvu_subject]['date'] = $subject['_embedded']['ispit']['datumIspita'];

                $data[$sp_id]['current_credit_value'] = $data[$sp_id]['current_credit_value'] + $subject['_embedded']['predmet']['ectsBodovi'];
            }

            switch ($this->isvu)
            {
                case   81:
                    $data[$sp_id]['executor_name_hr'] = 'Ekonomskog fakulteta Sveučilišta u Rijeci';
                    $data[$sp_id]['overlord_name'] = 'Sveučilište u Rijeci';
                    break;
                case    9:
                    $data[$sp_id]['executor_name_hr'] = 'Filozofskog fakulteta Sveučilišta u Rijeci';
                    $data[$sp_id]['overlord_name'] = 'Sveučilište u Rijeci';
                    break;
                case  244:
                    $data[$sp_id]['executor_name_hr'] = 'Filozofskog fakulteta Sveučilišta u Splitu';
                    $data[$sp_id]['overlord_name'] = 'Sveučilište u Splitu';
                    break;
                case  331:
                    $data[$sp_id]['executor_name_hr'] = 'Hrvatskog katoličkog sveučilišta';
                    $data[$sp_id]['overlord_name'] = 'Hrvatsko katoličko sveučilište';
                    break;
                case   11:
                    $data[$sp_id]['executor_name_hr'] = 'Kemijsko-tehnološkog fakulteta Sveučilišta u Splitu';
                    $data[$sp_id]['overlord_name'] = 'Sveučilište u Splitu';
                    break;
                case  335:
                    $data[$sp_id]['executor_name_hr'] = 'Odjela za biotehnologiju Sveučilišta u Rijeci';
                    $data[$sp_id]['overlord_name'] = 'Sveučilište u Rijeci';
                    break;
                case  316:
                    $data[$sp_id]['executor_name_hr'] = 'Odjela za fiziku Sveučilišta u Rijeci';
                    $data[$sp_id]['overlord_name'] = 'Sveučilište u Rijeci';
                    break;
                case  318:
                    $data[$sp_id]['executor_name_hr'] = 'Odjela za informatiku Sveučilišta u Rijeci';
                    $data[$sp_id]['overlord_name'] = 'Sveučilište u Rijeci';
                    break;
                case  319:
                    $data[$sp_id]['executor_name_hr'] = 'Odjela za matematiku Sveučilišta u Rijeci';
                    $data[$sp_id]['overlord_name'] = 'Sveučilište u Rijeci';
                    break;
                case  115:
                    $data[$sp_id]['executor_name_hr'] = 'Pravnog fakulteta Sveučilišta u Rijeci';
                    $data[$sp_id]['overlord_name'] = 'Sveučilište u Rijeci';
                    break;
                case  177:
                    $data[$sp_id]['executor_name_hr'] = 'Prirodoslovno-matematičkog fakulteta Sveučilišta u Splitu';
                    $data[$sp_id]['overlord_name'] = 'Sveučilište u Splitu';
                    break;
                case  243:
                    $data[$sp_id]['executor_name_hr'] = 'Sveučilišnog odjela za stručne studije Sveučilišta u Splitu';
                    $data[$sp_id]['overlord_name'] = 'Sveučilište u Splitu';
                    break;
                case 9998:
                    $data[$sp_id]['executor_name_hr'] = 'Sveučilišta u Rijeci';
                    $data[$sp_id]['overlord_name'] = 'Sveučilište u Rijeci';
                    break;
                case  269:
                    $data[$sp_id]['executor_name_hr'] = 'Sveučilišta u Zadru';
                    $data[$sp_id]['overlord_name'] = 'Sveučilište u Zadru';
                    break;
                case   69:
                    $data[$sp_id]['executor_name_hr'] = 'Tehničkog fakulteta Sveučilišta u Rijeci';
                    $data[$sp_id]['overlord_name'] = 'Sveučilište u Rijeci';
                    break;
                case  246:
                    $data[$sp_id]['executor_name_hr'] = 'Tehničkog veleučilišta u Zagrebu';
                    $data[$sp_id]['overlord_name'] = 'Tehničko veleučilište u Zagrebu';
                    break;
                case  299:
                    $data[$sp_id]['executor_name_hr'] = 'Učiteljskog fakulteta Sveučilišta u Rijeci';
                    $data[$sp_id]['overlord_name'] = 'Sveučilište u Rijeci';
                    break;
                case  234:
                    $data[$sp_id]['executor_name_hr'] = 'Veleučilišta s pravom javnosti BALTAZAR ZAPREŠIĆ';
                    $data[$sp_id]['overlord_name'] = 'Veleučilište s pravom javnosti BALTAZAR ZAPREŠIĆ';
                    break;
            }
        }

        return $data;
    }
}
