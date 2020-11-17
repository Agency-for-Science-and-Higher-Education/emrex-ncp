<?php

namespace EMREX;

use DateTime;
use DOMDocument;
use TCPDF;
use XML\XMLSecurityDSig;
use XML\XMLSecurityKey;

/**
 * Class EMREXUniversity
 * @package EMREX
 */
class EMREXUniversity extends EMREX
{
    /**
     * Creates an PDF file.
     * @param string $isvu_id   SQL query for fetching data which is being exported
     * @param int    $write  if not passed write output to disk
     * @return string XML file URI
     */
    public function getPDF($isvu_id, $write = 1)
    {
        $person = $_SESSION['person'];
        $data = $_SESSION['isvu'][$isvu_id];

        if (isset($_SESSION['isvu'][$isvu_id]))
        {

            // o/la
            $appendix1 = ($person['gender'] == 'M') ? 'o' : 'la';
            $appendix2 = ($person['gender'] == 'M') ? '' : 'a';
            $appendix3 = ($person['gender'] == 'M') ? '' : 'ica';
            $appendix4 = ($person['gender'] == 'M') ? 'io' : 'ijela';
            $appendix5 = ($person['gender'] == 'M') ? 'io' : 'ila';
            $appendix6 = ($data['study']['length'] < 5) ? 'ra' : 'ara';
            if ($data['study']['length'] == 1) $appendix6 = 'ar';

            $date = new DateTime();
            $record_number = "" . $date->format('Y-m-d') . '-' . time();
            $control_number = "" . str_pad(rand(0, 999), 3, '0') . '-' . str_pad(rand(0, 999), 3, '0') . '-' . str_pad(rand(0, 999), 3, '0');

            // create new PDF document
            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

            // set document information
            $pdf->SetAuthor('EMREX NCP Croatia');
            $pdf->SetTitle('Prijepis Ocjena - ' . $person['name'] . ' ' . $person['surname']);
            $pdf->SetSubject('Prijepis Ocjena');
            // set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            // set auto page breaks
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
            // set image scale factor
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
            // set document signature
            if (isset($_SESSION['SMP']))
            {
                $pdf->setSignature($this->cert_elmo, $this->key_elmo, '', '', 2, $this->info);
            }
            else
            {
                $pdf->setSignature($this->cert, $this->key, 'JeboTiMater1', '', 2, $this->info);
            }
            //Set font
            $pdf->SetFont('freesans', '', 14);
            // add a page
            $pdf->AddPage();

            $html = '';

            foreach ($data['study'] as $study)
            {
                $html .= '<div class="content">
                <table class="head-table" style="width:100%">
                    <tr>
                        <td class="crest">
                            <img width="60" src="https://cdn.studij.hr/images/crest.png">
                        </td>
                        <td class="filler">
                        </td>
                        <td class="logo">
                            <img width="80" src="https://cdn.studij.hr/images/emrex_executors/' . $data["isvu_id"] . '.png">
                        </td>
                    </tr>
                </table>

                <!--<div class="crest">
                    <img width="50" src="https://cdn.studij.hr/images/crest.png">
                </div>
                <div class="logo">
                    <img src="https://cdn.studij.hr/images/emrex_executors/' . $data["isvu_id"] . '.png">
                </div>-->

                <div>
                    <b><br>Republika Hrvatska<br>' . $study["overlord_name"] . '<br>' . $data["executor_name"] . '</b>
                </div>
            </div>
            
            <div class="content">Student' . $appendix3 . ' ' . $person['name'] . ' ' . $person['surname'] . ' podn' . $appendix4 . ' je zahtjev, te se na temelju članka 159. Zakona o općem upravnom postupku izdaje ova</div>';

                /**
                 * Ivana Kalić rođena 30.11.1994. godine u Imotskom, Republika Hrvatska, završila je 25.09.2017. godine na Prirodoslovno-matematičkom fakultetu Sveučilišta u Splitu, preddiplomski sveučilišni studij Matematika i informatika koji ukupno traje 6 semestara.

                 * Ivana Kalić upisala se na preddiplomski sveučilišni studij Prirodoslovno-matematičkog fakulteta Sveučilišta u Splitu akademske godine 2013/2014.

                 * Ivana Kalić je na preddiplomskom sveučilišnom studiju Prirodoslovno-matematičkog fakulteta Sveučilišta u Splitu ostvarila ukupno 181 ECTS bod.

                 * Studentica je položila ispite i obavila vježbe iz 38 predmeta prema priloženom prijepisu ocjena.
                 */
                if ($study['status'] == "Završio studij")
                {
                    $status = 'završi' . $appendix1;
                    $status_time = $study['sp_end'];
                    $status_na = '';
                }
                else
                {
                    $status = 'upisan' . $appendix2;
                    $status_time = $study['sp_start'];
                    $status_na = 'na';
                }

                $html .= '<div class="center">
		                <h1>POTVRDA</h1>
	                </div>
	                
	                <div class="content">' . $person['name'] . ' ' . $person['surname'] . ', rođen' . $appendix2 . ' ' . $person['dob'] . ' godine u naselju ' . ucwords(strtolower($person['place_of_birth'])) . ', ' . $status . ' je ' . $status_time . ' godine ' . $status_na . ' ' . $study['nacinIzvedbe'] . ' ' . $study['vrstaStudija'] . ' ' . $study['study_type'] . ' studij ' . $study['title'] . ', ' . $study['executor_name_hr'] . ', koji ukupno traje ' . $study['length'] . ' semest' . $appendix6 . '.</div>
	                
	                <div class="content">' . $person['name'] . ' ' . $person['surname'] . ' upisa' . $appendix1 . ' se na ' . $study['nacinIzvedbe'] . ' ' . $study['vrstaStudija'] . ' ' . $study['study_type'] . ' studij ' . $study['title'] . ' ' . $study['executor_name_hr'] . ' akademske godine ' . substr($study['sp_start'], 6, 4) . './' . (intval(substr($study['sp_start'], 6, 4)) + 1) . '.</div>
	                
	                <div class="content">' . $person['name'] . ' ' . $person['surname'] . ' je na gore navedenom studiju ostvari' . $appendix1 . ' ukupno ' . $study['current_credit_value'] . ' ECTS bodova.</div>
	                
                    <div class="content">Student' . $appendix3 . ' je položi' . $appendix1 . ' ispite i obavi' . $appendix1 . ' vježbe iz ' . count($study['grades']) . ' predmeta prema priloženom prijepisu ocjena:</div>
                    
                    <div class="center">
		                <h1>PRIJEPIS OCJENA</h1>
	                </div>
                    <div class="content">';

                $html .= '<table class="grades-table" style="width:100%">
                        <tr>
                            <th class="right number">Rbr</th>
                            <th style="font-family: freesans, sans-serif;" class="left name">Kolegij</th>
                            <th class="right ects">ECTS</th>
                            <th class="center">Datum polaganja</th>
                            <th class="left grade">Ocjena</th>
                        </tr>';

                $i = 0;

                foreach ($study['grades'] as $grade)
                {
                    $i++;
                    switch ($grade['resultLabel'])
                    {
                        case '1':
                            $grade['resultLabel'] = '1 (nedovoljan)';
                            break;
                        case '2':
                            $grade['resultLabel'] = '2 (dovoljan)';
                            break;
                        case '3':
                            $grade['resultLabel'] = '3 (dobar)';
                            break;
                        case '4':
                            $grade['resultLabel'] = '4 (vrlo dobar)';
                            break;
                        case '5':
                            $grade['resultLabel'] = '5 (odličan)';
                            break;
                        default:
                            $grade['resultLabel'] = '*';
                    }
                    //if ($grade['enters_average'] != 'DA') $grade['resultLabel'] = $grade['resultLabel'] . ' **';
                    //if ($grade['date'] == '') $grade['date'] = '*';
                    $html .= '<tr>
                    <td class="right number">' . $i . '.</td>
                    <td class="left name">' . $grade['title'] . '</td>
                    <td class="right ects">' . $grade['value'] . '</td>
                    <td class="center">' . $grade['date'] . '</td>
                    <td class="left grade">' . $grade['resultLabel'] . '</td>
                </tr>';
                }

                $html .= '</table><br><b>Opaske:</b><br> * ne ocjenjuje se<br>** ne ulazi u prosjek<br><br>
                <table class="grades-table" style="width:100%">
                <tr>
                    <td width="30%" class="footer">Prosječna ocjena: </td>
                    <td width="70%" class="footer2">' . $study['gpa'] . '</td>
                </tr>
                <tr>
                    <td width="30%" class="footer">Težinski prosjek ocjena: </td>
                    <td width="70%" class="footer">' . $study['wgpa'] . '</td>
                </tr>
                <tr>
                    <td width="30%" class="footer">Zbroj ECTS bodova: </td>
                    <td width="70%" class="footer">' . $study['current_credit_value'] . '</td>
                </tr>
            </table><br pagebreak="true"/>';
            }

            $footer = '';

            if (!isset($_SESSION['ncp']))
            {
                $footer = '
            <table class="grades-table" style="width:100%">
                <tr>
                    <th width="30%" class="footer" rowspan="6"><img src="https://cdn.studij.hr/images/azvo_logo.png"></th>
                    <th width="20%" class="footer">Vrijeme izdavanja</th>
                    <th width="50%" class="footer">' . $date->format('d.m.Y. h:m') . '</th>
                </tr>
                <tr>
                    <td width="20%" class="footer">Izdavatelj certifikata</td>
                    <td width="50%" class="footer">CN=AGENCIJA ZA ZNANOST I VISOKO OBRAZOVANJE, L=ZAGREB, O=AZVO 83358955356, C=HR</td>
                </tr>
                <tr>
                    <td width="20%" class="footer">Serijski broj</td>
                    <td width="50%" class="footer">288e6ffa5be8d3b400000000566069d8</td>
                </tr>
                <tr>
                    <td width="20%" class="footer">Algoritam potpisa</td>
                    <td width="50%" class="footer">sha256RSA</td>
                </tr>
                <tr>
                    <td width="20%" class="footer">Broj zapisa</td>
                    <td width="50%" class="footer">' . $record_number . '</td>
                </tr>
                <tr>
                    <td width="20%" class="footer">Kontrolni broj</td>
                    <td width="50%" class="footer">' . $control_number . '</td>
                </tr>
                <tr>
                    <td width="30%" class="footer">Elektronički pečat</td>
                    <td width="70%" class="footer2" colspan="2">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</td>
                </tr>
                <tr>
                    <td width="30%" class="footer">Informacija za provjeru dokumenta</td>
                    <td width="70%" class="footer" colspan="2">Elektronički zapisi se čuvaju najviše 3 mjeseca od trenutka generiranja te se u tom roku može izvršiti provjera elektroničkog zapisa uvidom u elektronički zapis kojem se pristupa putem broja zapisa i kontrolnog broja otisnutog u kontrolnom dijelu elektroničkog zapisa, putem Internet adrese https://emrex.studij.hr/provjera</td>
                </tr>
                <tr>
                    <td width="30%" class="footer">Napomena</td>
                    <td width="70%" class="footer" colspan="2">Elektronički pečat je kreiran certifikatom Agencija za znanost i visoko obrazovanje</td>
                </tr>
            </table>';
            }

            $pdf->writeHTML('<!DOCTYPE html><html><head><meta charset="utf-8" /><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><style>' . file_get_contents("../src/Core/pdf.css") . '</style></head><body>' . $html . $footer . '</div></body></html>', true, 0, true, 0);

            $filename = $record_number . '-' . $control_number;

            // Close and output PDF document
            if ($write == 0)
            {
                $file = fopen('../../pdf/' . $filename . '.html', "w") or die("Unable to open file!");
                fwrite($file, '<style>' . file_get_contents("../src/Core/html.css") . '</style>' . $html . '</div>');
                fclose($file);
                $pdf->Output($filename . '.pdf', 'D');
            }
            else
            {
                return $pdf->Output('../../pdf/' . $filename . '.pdf', 'S');
            }
            return 1;
        }
        return null;
    }

    /**
     * Creates an XML file.
     * @param string $isvu_id
     * @param int    $transfer    if not passed write output to disk
     * @return string XML file URI
     */
    public function getXML($isvu_id, $transfer = 1)
    {
        $person = $_SESSION['person'];
        $data = $_SESSION['isvu'][$isvu_id];

        $this->elmo  = $this->getHeader();
        $this->elmo .= $this->getGeneratedDate();

        $this->elmo .= "<learner>";
        $this->elmo .= $this->getCitizenship($person['citizenship']);
        $this->elmo .= $this->getNationalIdentifier($person['oib']);
        $this->elmo .= $this->getName($person['name']);
        $this->elmo .= $this->getSurname($person['surname']);
        $this->elmo .= $this->getDOB($person['dob']);
        $this->elmo .= "</learner>";

        $this->elmo .= "<report>";

        $this->elmo .= "<issuer>";
        $this->elmo .= $this->getCountry('HR');
        $this->elmo .= $this->getSCHACIdentifier($data['schac']);
        $this->elmo .= $this->getExecutor($data['executor_name']);
        $this->elmo .= $this->getURL($data['url']);
        $this->elmo .= "</issuer>";

        foreach ($data['study'] as $study)
        {
            $this->elmo .= "<learningOpportunitySpecification>";

            $this->elmo .= $this->getLOSIdentifier($study['los_identifier']);
            $this->elmo .= $this->getTitle($study['title']);
            $this->elmo .= $this->getType($study['type']);
            //$this->elmo .= $this->getSubjectArea($subject_area);
            //$this->elmo .= $this->getIscedCode($isced_code);
            //$this->elmo .= $this->getLOSURL($los_url);
            //$this->elmo .= $this->getDescription($description);

            $this->elmo .= "<specifies>";
            $this->elmo .= "<learningOpportunityInstance>";

            //$this->elmo .= $this->getLOIIdentifier($sp);
            $this->elmo .= $this->getStartDate($study['sp_start']);
            $this->elmo .= $this->getEndDate($study['sp_end']);
            $this->elmo .= $this->getStatus($study['status']);
            $this->elmo .= $this->getGradingSchemeLocalID("1-5");
            $this->elmo .= $this->getResultLabel($study['grade']);

            $this->elmo .= "<credit>";

            $this->elmo .= $this->getCreditScheme($study['credit_scheme']);
            //$this->elmo .= $this->getCreditLevel($study['credit_level']);
            $this->elmo .= $this->getCreditValue($study['credit_value']);

            $this->elmo .= "</credit>";

            $this->elmo .= "<level>";
            $this->elmo .= "<type>EQF</type>";
            $this->elmo .= "<description>European Qualification Framework</description>";
            $this->elmo .= "<description>" . $study['eqf_level'] . "</description>";
            $this->elmo .= "</level>";

            $this->elmo .= "</learningOpportunityInstance>";
            $this->elmo .= "</specifies>";

            foreach ($study['grades'] as $grade)
            {
                $this->elmo .= "<hasPart>";
                $this->elmo .= "<learningOpportunitySpecification>";

                $this->elmo .= "<identifier type='local'>" . $grade['identifier'] . "</identifier>";
                $this->elmo .= "<title xml:lang='hr'>" . $grade['title'] . "</title>";
                $this->elmo .= "<type>" . $grade['type'] . "</type>";

                $this->elmo .= "<specifies>";
                $this->elmo .= "<learningOpportunityInstance>";
                $this->elmo .= "<status>" . $grade['status'] . "</status>";
                $this->elmo .= "<gradingSchemeLocalId>1-5</gradingSchemeLocalId>";
                $this->elmo .= "<resultLabel>" . $grade['resultLabel'] . "</resultLabel>";

                $this->elmo .= "<credit>";
                $this->elmo .= "<scheme>ects</scheme>";
                $this->elmo .= "<value>" . $grade['value'] . "</value>";
                $this->elmo .= "</credit>";

                $this->elmo .= "</learningOpportunityInstance>";
                $this->elmo .= "</specifies>";

                $this->elmo .= "</learningOpportunitySpecification>";
                $this->elmo .= "</hasPart>";
            }

            $this->elmo .= "</learningOpportunitySpecification>";
        }

        $this->elmo .= $this->getIssueDate();

        $this->elmo .= "<gradingScheme localId='1-5'>";
        $this->elmo .= "<description xml:lang='en'>Broj izmedu 1 i 5</description>";
        $this->elmo .= "<description xml:lang='hr'>A number of the scale from 1 to 5 with up to 3 decimal places</description>";
        $this->elmo .= "</gradingScheme>";
        $this->elmo .= "<gradingScheme localId='PF'>";
        $this->elmo .= "<description xml:lang='en'>Pass/Fail (non-graded pass)</description>";
        $this->elmo .= "<description xml:lang='hr'>Polozen/Nepolozen</description>";
        $this->elmo .= "</gradingScheme>";

        $this->elmo .= "</report>";

        $this->elmo .= "<attachment>";
        $this->elmo .= "<type>EMREX transcript</type>";
        $this->elmo .= "<title>" . "Transcript of Records" . "</title>";
        $this->elmo .= "<content>data:application/pdf;base64," . base64_encode($this->getPDF($isvu_id, 1)) . "</content>";
        $this->elmo .= "</attachment>";

        $this->elmo = "<?xml version='1.0' encoding='UTF-8' standalone='no'?>" . $this->elmo . "</elmo>";

        // Load the XML to be signed
        $doc = new DOMDocument();
        $doc->loadXML($this->elmo);
        // Create a new Security object
        $objDSig = new XMLSecurityDSig('');
        // Use the c14n exclusive canonicalization
        $objDSig->setCanonicalMethod(XMLSecurityDSig::EXC_C14N);
        // Sign using SHA-256
        $objDSig->addReference($doc, XMLSecurityDSig::SHA1, array('http://www.w3.org/2000/09/xmldsig#enveloped-signature'), array('force_uri' => true));
        // Create a new (private) Security key
        $objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA1, array('type' => 'private'));
        // Load the private key
        $objKey->loadKey($this->pem_elmo, TRUE);
        // Sign the XML file
        $objDSig->sign($objKey);
        // Add the associated public key to the signature
        $objDSig->add509Cert(file_get_contents($this->cert_elmo), false, false, array("force_uri" => true));
        // Append the signature to the XML
        $objDSig->appendSignature($doc->documentElement);
        // Save the signed XML
        //$doc->save('./signed.xml');

        if ($transfer == 1)
        {
            return base64_encode(gzencode($doc->saveXML()));
        }
        else
        {
            return $doc->saveXML();
        }
    }
}
