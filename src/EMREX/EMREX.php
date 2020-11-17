<?php
namespace EMREX;

/**
 * Class EMREX
 * @package EMREX
 */
class EMREX
{
    protected $elmo = '';

    /*
    NOTES:
     - To create self-signed signature: openssl req -x509 -nodes -days 365000 -newkey rsa:1024 -keyout tcpdf.crt -out tcpdf.crt
     - To export crt to p12: openssl pkcs12 -export -in tcpdf.crt -out tcpdf.p12
     - To convert pfx certificate to pem: openssl pkcs12 -in tcpdf.pfx -out tcpdf.crt -nodes
    */        
    protected $key  = '/srv/studijweb/emrex.studij.hr/src/Certs/AZVO_EMREX_Pecat.key';
    protected $pem  = '/srv/studijweb/emrex.studij.hr/src/Certs/AZVO_EMREX_Pecat.pem';
    protected $cert = '/srv/studijweb/emrex.studij.hr/src/Certs/AZVO_EMREX_Pecat.crt';
    
    protected $key_elmo  = '/srv/studijweb/emrex.studij.hr/src/Certs/emrex_studij_hr.key';
	protected $pem_elmo  = '/srv/studijweb/emrex.studij.hr/src/Certs/emrex_studij_hr.pem';
	protected $cert_elmo = '/srv/studijweb/emrex.studij.hr/src/Certs/emrex_studij_hr.crt';

    protected $info = array(
        'Name' => 'Agencija za znanost i visoko obrazovanje',
        'Location' => 'Zagreb, Croatia',
        'ContactInfo' => 'http://www.azvo.hr',
    );

    // HEADER
    public function getHeader()
    {
        return '<elmo xmlns="https://github.com/emrex-eu/elmo-schemas/tree/v1.3.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="https://github.com/emrex-eu/elmo-schemas/tree/v1.3.0 https://raw.githubusercontent.com/emrex-eu/elmo-schemas/v1.3.0/schema.xsd">';
    }

    public function getGeneratedDate()
    {
        return "<generatedDate>".date("Y-m-d")."T".date("H:i:s")."+01:00</generatedDate>";
    }

    // LEARNER
    public function getCitizenship($citizenship)
    {
        return "<citizenship>".$citizenship."</citizenship>";
    }

    public function getNationalIdentifier($oib)
    {
        return "<identifier type='nationalIdentifier'>".$oib."</identifier>";
    }

    public function getName($name)
    {
        return "<givenNames>".$name."</givenNames>";
    }

    public function getSurname($surname)
    {
        return "<familyName>".$surname."</familyName>";
    }

    public function getDOB($dob)
    {
        return "<bday>".$dob."</bday>";
    }

    // ISSUER
    public function getCountry($country)
    {
        return "<country>".$country."</country>";
    }

    public function getPICIdentifier($pic)
    {
        return "<identifier type='pic'>".$pic."</identifier>";
    }

    public function getERASMUSIdentifier($erasmus)
    {
        return "<identifier type='erasmus'>".$erasmus."</identifier>";
    }

    public function getSCHACIdentifier($schac)
    {
        return "<identifier type='schac'>".$schac."</identifier>";
    }

    public function getExecutor($title)
    {
        return "<title xml:lang='hr'>".$title."</title>";
    }

    public function getURL($url)
    {
        return "<url>".$url."</url>";
    }

    // LEARNING OPPORTUNITY SPECIFICATION
    public function getLOSIdentifier($los_identifier)
    {
        return "<identifier type='local'>".$los_identifier."</identifier>";
    }

    public function getTitle($title)
    {
        return "<title>".$title."</title>";
    }

    public function getType($type)
    {
        return "<type>".$type."</type>";
    }

    public function getSubjectArea($subject_area)
    {
        return "<subjectArea>".$subject_area."</subjectArea>";
    }

    public function getIscedCode($isced_code)
    {
        return "<iscedCode>".$isced_code."</iscedCode>";
    }

    public function getLOSURL($los_url)
    {
        return "<url>".$los_url."</url>";
    }

    public function getDescription($description)
    {
        return "<description xml:lang='hr'>".$description."</description>";
    }

    // LEARNING OPPORTUNITY INSTANCE
    public function getLOIIdentifier($sp)
    {
        return "<identifier>".$sp."</identifier>";
    }

    public function getStartDate($sp_start)
    {
        return "<start>".$sp_start."</start>";
    }

    public function getEndDate($sp_end)
    {
        return "<date>".$sp_end."</date>";
    }

    public function getStatus($status)
    {
        return "<status>".$status."</status>";
    }

    public function getGradingSchemeLocalID($grading_scheme_local_id)
    {
        return "<gradingSchemeLocalId>".$grading_scheme_local_id."</gradingSchemeLocalId>";
    }

    public function getResultLabel($grade)
    {
        return "<resultLabel>".$grade."</resultLabel>";
    }

    // CREDIT
    public function getCreditScheme($credit_scheme)
    {
        return "<scheme>".$credit_scheme."</scheme>";
    }

    public function getCreditLevel($credit_level)
    {
        return "<level>".$credit_level."</level>";
    }

    public function getCreditValue($credit_value)
    {
        return "<value>".$credit_value."</value>";
    }

    public function getIssueDate()
    {
        return "<issueDate>".date("Y-m-d")."T".date("H:i:s")."+01:00</issueDate>";
    }

    public function str_replace_first($from, $to, $content)
    {
        $from = '/'.preg_quote($from, '/').'/';
        return preg_replace($from, $to, $content, 1);
    }
}