<?php

namespace Core;

use ISVU\ISVUStudentOIB;
use ISVU\ISVUStudentovStudij;
use ISVU\ISVUSumarno;
use ISVU\ISVUVI;

/**
 * Class ISVU
 * @package Core
 */
class ISVU
{
    private $db;

    /**
     * Counstructor
     */
    public function __construct()
    {
        $this->db = getDB();
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // DOMESTIC
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * @param $oib
     * @return int
     */
    public function syncWithOIB($oib)
    {
        $result = OIBFactory::fetch($this->db, $oib);
        if ($result === FALSE)
        {
            // OIB fetch error
            ErrorLog::emrexLog(601, 0, 'OIB fetch error ' . $oib);
            return FALSE;
        }

        $person['gender'] = $result['gender'];
        $person['place_of_birth'] = $result['place_of_birth'];
        $person['country_of_birth'] = $result['country_of_birth'];

        // Case corrections
        $person['name']    = $this->firstLetterUpperCase($result['name']);
        $person['surname'] = $this->firstLetterUpperCase($result['surname']);

        $person['dob'] = date('d.m.Y.', strtotime(substr($result['date_of_birth'], 0, 4) . substr($result['date_of_birth'], 5, 2) . substr($result['date_of_birth'], 8, 2)));
        $person['oib'] = $oib;

        if ($result['citizenship'] == 1)
        {
            $person['citizenship'] = 'HR';
        }
        else
        {
            $person['citizenship'] = 'XX';
        }

        $_SESSION['person'] = $person;

        return TRUE;
    }

    /**
     * @param $oib
     * @return int
     */
    public function syncWithISVU($oib)
    {
        // Check if person is in ISVU
        $isvu_vi = new ISVUVI();
        $institution = $isvu_vi->getInstitutions($oib);

        if (!empty($institution))
        {
            foreach ($institution as $isvu)
            {
                $data = [];

                // Get student data
                $isvu_student = new ISVUStudentOIB($isvu);
                $student_data = $isvu_student->getStudentData($oib);

                $data[$isvu]['isvu_id'] = $student_data['isvu_id'];
                $data[$isvu]['jmbag'] = $student_data['jmbag'];
                $data[$isvu]['executor_name'] = $student_data['executor_name'];
                $data[$isvu]['executor_address'] = $student_data['executor_address'];
                $data[$isvu]['schac'] = $student_data['schac'];
                $data[$isvu]['url'] = $student_data['url'];
                $data[$isvu]['study_link'] = $student_data['study_link'];
                $data[$isvu]['summary_link'] = $student_data['summary_link'];

                // Get summary data
                $isvu_summary = new ISVUSumarno($isvu);
                $summary_data = $isvu_summary->getSummaryData($student_data['summary_link'], $student_data['study_link']);

                $data[$isvu]['study'] = $summary_data;

                // Store data in session
                $_SESSION['isvu'][$isvu] = $data[$isvu];
            }
        }
        return TRUE;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // HELPER FUNCTIONS
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * @param $string
     * @return string
     */
    public function firstLetterUpperCase($string)
    {
        // Exceptions in lower case are words you don't want converted
        // Exceptions all in upper case are any words you don't want converted to title case
        //   but should be converted to upper case, e.g.:
        //   king henry viii or king henry Viii should be King Henry VIII

        $delimiters = array(" ", "-", ".", "'", "O'", "Mc");
        $exceptions = array("and", "to", "of", "das", "dos", "I", "II", "III", "IV", "V", "VI", "SR");

        $string = mb_convert_case($string, MB_CASE_TITLE, "UTF-8");
        foreach ($delimiters as $dlnr => $delimiter)
        {
            $words = explode($delimiter, $string);
            $newwords = array();
            foreach ($words as $wordnr => $word)
            {
                if (in_array(mb_strtoupper($word, "UTF-8"), $exceptions))
                {
                    // Check exceptions list for any words that should be in upper case
                    $word = mb_strtoupper($word, "UTF-8");
                }
                else if (in_array(mb_strtolower($word, "UTF-8"), $exceptions))
                {
                    // Check exceptions list for any words that should be in upper case
                    $word = mb_strtolower($word, "UTF-8");
                }
                else if (!in_array($word, $exceptions))
                {
                    // Convert to uppercase (non-utf8 only)
                    $word = ucfirst($word);
                }
                array_push($newwords, $word);
            }
            $string = join($delimiter, $newwords);
        }
        return $string;
    }
}
