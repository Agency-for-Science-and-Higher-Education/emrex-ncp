<?php

namespace ISVU;

/**
 * Class ISVUGrades
 * @package ISVU
 */
class ISVUGrades
{
    private $isvu;

    /**
     * Counstructor
     */
    public function __construct($isvu)
    {
        $this->isvu = $isvu;
    }

    public function getGrades(&$data, $isvu_study_grades)
    {
        foreach ($sumarniPodaciStudent[0]->sumarniPodacistudent[0]->podaciSaZadnjegUpisnogLista->upisniList[$counter]->popisPolozenihPredmeta->polozenPredmet as $grade)
        {
            $isvu_grade = (string)$grade->predmet['sifraPredmet'];

            $data[$this->isvu]['study'][$isvu_study_grades]['grades'][$isvu_grade]['identifier'] = (string)$grade->predmet['sifraPredmet'];
            $data[$this->isvu]['study'][$isvu_study_grades]['grades'][$isvu_grade]['title'] = (string)$grade->predmet->nazivPredmet;
            $data[$this->isvu]['study'][$isvu_study_grades]['grades'][$isvu_grade]['type'] = "Course";
            $data[$this->isvu]['study'][$isvu_study_grades]['grades'][$isvu_grade]['status'] = "passed";
            $data[$this->isvu]['study'][$isvu_study_grades]['grades'][$isvu_grade]['enters_average'] = (string)$grade->predmet->ulaziUProsjek;
            $data[$this->isvu]['study'][$isvu_study_grades]['grades'][$isvu_grade]['resultLabel'] = (string)$grade->ispit->ocjena;
            $data[$this->isvu]['study'][$isvu_study_grades]['grades'][$isvu_grade]['value'] = (string)$grade->predmet->ectsBod;
            $data[$this->isvu]['study'][$isvu_study_grades]['grades'][$isvu_grade]['date'] = (string)$grade->ispit['datumIspit'];
            $data[$this->isvu]['study'][$isvu_study_grades]['current_credit_value'] = $data[$this->isvu]['study'][$isvu_study_grades]['current_credit_value'] + floatval(str_replace(',', '.', $grade->predmet->ectsBod));

            $exam_date = (string)$grade->ispit['datumIspit'];

            if (isset($exam_date))
            {
                if (strtotime((string)$grade->ispit['datumIspit']) > strtotime($end_date))
                {
                    $end_date = (string)$grade->ispit['datumIspit'];
                }
            }

            unset($exam_date);
        }

        return $data[$this->isvu];;
    }
}
