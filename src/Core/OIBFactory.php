<?php
namespace Core;

use Exception;

/**
 * Class OIBFactory
 * @package Core
 */
class OIBFactory
{
    /**
     * @param $db \PDO connection.
     * @param string $oib OIB for which the information is being fetched.
     * @param int $return return raw or processed data.
     * @return array|bool array containing person info on success, FALSE on FAILURE
     */
    public static function fetch(\PDO $db, $oib, $return = 1)
    {
        $config['url']          = '';  // testni OIB proxy
        $config['service_name'] = '';  // naziv testne usluge koja koristi OIB proxy
        $config['enc_key']      = '';  // enc pass za testni oib
        $config['int_key']      = '';  // int pass za testni oib
        $config['vrsta']        = '';  // dohvat oiba za fizicke osobe (moze biti i oibpo - za pravne)

        try
        {
            $oib_data = OIBService::oib_grab($oib, '', $config);
            if ($oib_data === FALSE) return FALSE;
            $oib_data = json_decode(json_encode($oib_data), true);

            // Person
            $person['oib']              = (isset($oib_data['OIB']))                                    ? $oib_data['OIB']                                    : NULL;  // CHAR
            $person['jmbg']             = (isset($oib_data['MBG']))                                    ? $oib_data['MBG']                                    : NULL;  // CHAR
            $person['name']             = (isset($oib_data['Ime']))                                    ? $oib_data['Ime']                                    : NULL;  // CHAR
            $person['surname']          = (isset($oib_data['Prezime']))                                ? $oib_data['Prezime']                                : NULL;  // CHAR
            //$person['original_surname'] = (isset($oib_data['RodjenoPrezime']))                         ? $oib_data['RodjenoPrezime']                         : NULL;  // CHAR
            $person['gender']           = (isset($oib_data['Spol']))                                   ? $oib_data['Spol']                                   : NULL;  // CHAR
            $person['date_of_birth']    = (isset($oib_data['DatumRodjenja']))                          ? $oib_data['DatumRodjenja']                          : NULL;  // YYYY-MM-DD
            $person['place_of_birth']   = (isset($oib_data['MjestoRodjenja']))                         ? $oib_data['MjestoRodjenja']                         : NULL;  // CHAR
            $person['country_of_birth'] = (isset($oib_data['SifraDrzaveRodjenja']))                    ? $oib_data['SifraDrzaveRodjenja']                    : NULL;  // INT
            $person['citizenship']      = (isset($oib_data['Drzavljanstvo']))                          ? $oib_data['Drzavljanstvo']                          : NULL;  // CHAR
            //$person['citizenship_date'] = (isset($oib_data['DatumStjecanjaHrvDrzavljanstva']))         ? $oib_data['DatumStjecanjaHrvDrzavljanstva']         : $person['date_of_birth'];  // YYYY-MM-DD

            // Address
            $person['address']          = (isset($oib_data['AdresaPrebivalista']['Ulica']))            ? $oib_data['AdresaPrebivalista']['Ulica']            : NULL;  // CHAR
            $person['address']         .= (isset($oib_data['AdresaPrebivalista']['KucniBroj']))        ? ' '.$oib_data['AdresaPrebivalista']['KucniBroj']    : NULL;  // CHAR
            $person['address']         .= (isset($oib_data['AdresaPrebivalista']['KucniBrojDodatak'])) ? $oib_data['AdresaPrebivalista']['KucniBrojDodatak'] : NULL;  // CHAR
            $person['city']             = (isset($oib_data['AdresaPrebivalista']['Naselje']))          ? $oib_data['AdresaPrebivalista']['Naselje']          : NULL;  // CHAR
            $person['country']          = (isset($oib_data['AdresaPrebivalista']['SifraDrzave']))      ? $oib_data['AdresaPrebivalista']['SifraDrzave']      : NULL;  // INT

            if (strlen($person['address']) < 2) $person['address'] = NULL;

            //print_r($oib_data);

            $oib                      = (isset($oib_data['OIB']))                                    ? $oib_data['OIB']                                    : NULL;  // char
            $oib_issuing_date         = (isset($oib_data['DatumIVrijemeDodjeleOIBa']))               ? $oib_data['DatumIVrijemeDodjeleOIBa']               : NULL;  // char
            $change_valid_from        = (isset($oib_data['PromjenaVrijediOd']))                      ? $oib_data['PromjenaVrijediOd']                      : NULL;  // char
            $oib_status               = (isset($oib_data['OIBStatus']))                              ? $oib_data['OIBStatus']                              : NULL;  // char
            $jmbg                     = (isset($oib_data['MBG']))                                    ? $oib_data['MBG']                                    : NULL;  // char
            $name                     = (isset($oib_data['Ime']))                                    ? $oib_data['Ime']                                    : NULL;  // char
            $surname                  = (isset($oib_data['Prezime']))                                ? $oib_data['Prezime']                                : NULL;  // char
            $birth_surname            = (isset($oib_data['RodjenoPrezime']))                         ? $oib_data['RodjenoPrezime']                         : NULL;  // char
            $date_of_birth            = (isset($oib_data['DatumRodjenja']))                          ? $oib_data['DatumRodjenja']                          : NULL;  // char
            $place_of_birth           = (isset($oib_data['MjestoRodjenja']))                         ? $oib_data['MjestoRodjenja']                         : NULL;  // char
            $country_of_birth_id      = (isset($oib_data['SifraDrzaveRodjenja']))                    ? $oib_data['SifraDrzaveRodjenja']                    : NULL;  // char
            $country_of_birth         = (isset($oib_data['DrzavaRodjenja']))                         ? $oib_data['DrzavaRodjenja']                         : NULL;  // char
            $gender                   = (isset($oib_data['Spol']))                                   ? $oib_data['Spol']                                   : NULL;  // char
            $citizenship              = (isset($oib_data['Drzavljanstvo']))                          ? $oib_data['Drzavljanstvo']                          : NULL;  // char
            $citizenship_issuing_date = (isset($oib_data['DatumStjecanjaHrvDrzavljanstva']))         ? $oib_data['DatumStjecanjaHrvDrzavljanstva']         : NULL;  // char
            $father_oib               = (isset($oib_data['Roditelj'][0]['OIB']))                     ? $oib_data['Roditelj'][0]['OIB']                     : NULL;  // char
            $father_jmbg              = (isset($oib_data['Roditelj'][0]['MBG']))                     ? $oib_data['Roditelj'][0]['MBG']                     : NULL;  // char
            $father_name              = (isset($oib_data['Roditelj'][0]['Ime']))                     ? $oib_data['Roditelj'][0]['Ime']                     : NULL;  // char
            $father_surname           = (isset($oib_data['Roditelj'][0]['Prezime']))                 ? $oib_data['Roditelj'][0]['Prezime']                 : NULL;  // char
            $father_birth_surname     = (isset($oib_data['Roditelj'][0]['RodjenoPrezime']))          ? $oib_data['Roditelj'][0]['RodjenoPrezime']          : NULL;  // char
            $father_gender            = (isset($oib_data['Roditelj'][0]['Spol']))                    ? $oib_data['Roditelj'][0]['Spol']                    : NULL;  // char
            $mother_oib               = (isset($oib_data['Roditelj'][1]['OIB']))                     ? $oib_data['Roditelj'][1]['OIB']                     : NULL;  // char
            $mother_jmbg              = (isset($oib_data['Roditelj'][1]['MBG']))                     ? $oib_data['Roditelj'][1]['MBG']                     : NULL;  // char
            $mother_name              = (isset($oib_data['Roditelj'][1]['Ime']))                     ? $oib_data['Roditelj'][1]['Ime']                     : NULL;  // char
            $mother_surname           = (isset($oib_data['Roditelj'][1]['Prezime']))                 ? $oib_data['Roditelj'][1]['Prezime']                 : NULL;  // char
            $mother_birth_surname     = (isset($oib_data['Roditelj'][1]['RodjenoPrezime']))          ? $oib_data['Roditelj'][1]['RodjenoPrezime']          : NULL;  // char
            $mother_gender            = (isset($oib_data['Roditelj'][1]['Spol']))                    ? $oib_data['Roditelj'][1]['Spol']                    : NULL;  // char
            $street                   = (isset($oib_data['AdresaPrebivalista']['Ulica']))            ? $oib_data['AdresaPrebivalista']['Ulica']            : NULL;  // char
            $street_number            = (isset($oib_data['AdresaPrebivalista']['KucniBroj']))        ? $oib_data['AdresaPrebivalista']['KucniBroj']        : NULL;  // char
            $street_number_appendix   = (isset($oib_data['AdresaPrebivalista']['KucniBrojDodatak'])) ? $oib_data['AdresaPrebivalista']['KucniBrojDodatak'] : NULL;  // char
            $pobox                    = (isset($oib_data['AdresaPrebivalista']['BrojPoste']))        ? $oib_data['AdresaPrebivalista']['BrojPoste']        : NULL;  // char
            $city_id                  = (isset($oib_data['AdresaPrebivalista']['SifraNaselja']))     ? $oib_data['AdresaPrebivalista']['SifraNaselja']     : NULL;  // char
            $city                     = (isset($oib_data['AdresaPrebivalista']['Naselje']))          ? $oib_data['AdresaPrebivalista']['Naselje']          : NULL;  // char
            $municipality_id          = (isset($oib_data['AdresaPrebivalista']['SifraOpcine']))      ? $oib_data['AdresaPrebivalista']['SifraOpcine']      : NULL;  // char
            $municipality             = (isset($oib_data['AdresaPrebivalista']['Opcina']))           ? $oib_data['AdresaPrebivalista']['Opcina']           : NULL;  // char
            $country_id               = (isset($oib_data['AdresaPrebivalista']['SifraDrzave']))      ? $oib_data['AdresaPrebivalista']['SifraDrzave']      : NULL;  // char
            $country                  = (isset($oib_data['AdresaPrebivalista']['Drzava']))           ? $oib_data['AdresaPrebivalista']['Drzava']           : NULL;  // char
            $document_type            = (isset($oib_data['VrstaIdDokumenta']))                       ? $oib_data['VrstaIdDokumenta']                       : NULL;  // char
            $document_id              = (isset($oib_data['BrojIdDokumenta']))                        ? $oib_data['BrojIdDokumenta']                        : NULL;  // char
            $document_expiry_date     = (isset($oib_data['DatumVazenjaIdDokumenta']))                ? $oib_data['DatumVazenjaIdDokumenta']                : NULL;  // char
            $issuing_country_id       = (isset($oib_data['SifraDrzaveIzdavanjaIdDokumenta']))        ? $oib_data['SifraDrzaveIzdavanjaIdDokumenta']        : NULL;  // char
            $issuing_country          = (isset($oib_data['DrzavaIzdavanjaIdDokumenta']))             ? $oib_data['DrzavaIzdavanjaIdDokumenta']             : NULL;  // char

            $stmt = $db->prepare('INSERT INTO cboe.oib_log (
                        oib,
                        oib_issuing_date,
                        change_valid_from,
                        oib_status,
                        jmbg,
                        name,
                        surname,
                        birth_surname,
                        dob,
                        place_of_birth,
                        country_of_birth_id,
                        country_of_birth,
                        gender,
                        citizenship,
                        citizenship_issuing_date,
                        father_oib,
                        father_jmbg,
                        father_name,
                        father_surname,
                        father_birth_surname,
                        father_gender,
                        mother_oib,
                        mother_jmbg,
                        mother_name,
                        mother_surname,
                        mother_birth_surname,
                        mother_gender,
                        street,
                        street_number,
                        street_number_appendix,
                        pobox,
                        city_id,
                        city,
                        municipality_id,
                        municipality,
                        country_id,
                        country,
                        document_type,
                        document_id,
                        document_expiry_date,
                        issuing_country_id,
                        issuing_country,
                        timestamp) VALUES (:oib, :oib_issuing_date, :change_valid_from, :oib_status, :jmbg, :name, :surname, :birth_surname, :dob, :place_of_birth, :country_of_birth_id, :country_of_birth, :gender, :citizenship, :citizenship_issuing_date, :father_oib, :father_jmbg, :father_name, :father_surname, :father_birth_surname, :father_gender, :mother_oib, :mother_jmbg, :mother_name, :mother_surname, :mother_birth_surname, :mother_gender, :street, :street_number, :street_number_appendix, :pobox, :city_id, :city, :municipality_id, :municipality, :country_id, :country, :document_type, :document_id, :document_expiry_date, :issuing_country_id, :issuing_country, now())');

            $stmt->bindParam(':oib',                      $oib);                       // char
            $stmt->bindParam(':oib_issuing_date',         $oib_issuing_date);          // char
            $stmt->bindParam(':change_valid_from',        $change_valid_from);         // char
            $stmt->bindParam(':oib_status',               $oib_status);                // char
            $stmt->bindParam(':jmbg',                     $jmbg);                      // char
            $stmt->bindParam(':name',                     $name);                      // char
            $stmt->bindParam(':surname',                  $surname);                   // char
            $stmt->bindParam(':birth_surname',            $birth_surname);             // char
            $stmt->bindParam(':dob',                      $date_of_birth);             // char
            $stmt->bindParam(':place_of_birth',           $place_of_birth);            // char
            $stmt->bindParam(':country_of_birth_id',      $country_of_birth_id);       // char
            $stmt->bindParam(':country_of_birth',         $country_of_birth);          // char
            $stmt->bindParam(':gender',                   $gender);                    // char
            $stmt->bindParam(':citizenship',              $citizenship);               // char
            $stmt->bindParam(':citizenship_issuing_date', $citizenship_issuing_date);  // char
            $stmt->bindParam(':father_oib',               $father_oib);                // char
            $stmt->bindParam(':father_jmbg',              $father_jmbg);               // char
            $stmt->bindParam(':father_name',              $father_name);               // char
            $stmt->bindParam(':father_surname',           $father_surname);            // char
            $stmt->bindParam(':father_birth_surname',     $father_birth_surname);      // char
            $stmt->bindParam(':father_gender',            $father_gender);             // char
            $stmt->bindParam(':mother_oib',               $mother_oib);                // char
            $stmt->bindParam(':mother_jmbg',              $mother_jmbg);               // char
            $stmt->bindParam(':mother_name',              $mother_name);               // char
            $stmt->bindParam(':mother_surname',           $mother_surname);            // char
            $stmt->bindParam(':mother_birth_surname',     $mother_birth_surname);      // char
            $stmt->bindParam(':mother_gender',            $mother_gender);             // char
            $stmt->bindParam(':street',                   $street);                    // char
            $stmt->bindParam(':street_number',            $street_number);             // char
            $stmt->bindParam(':street_number_appendix',   $street_number_appendix);    // char
            $stmt->bindParam(':pobox',                    $pobox);                     // char
            $stmt->bindParam(':city_id',                  $city_id);                   // char
            $stmt->bindParam(':city',                     $city);                      // char
            $stmt->bindParam(':municipality_id',          $municipality_id);           // char
            $stmt->bindParam(':municipality',             $municipality);              // char
            $stmt->bindParam(':country_id',               $country_id);                // char
            $stmt->bindParam(':country',                  $country);                   // char
            $stmt->bindParam(':document_type',            $document_type);             // char
            $stmt->bindParam(':document_id',              $document_id);               // char
            $stmt->bindParam(':document_expiry_date',     $document_expiry_date);      // char
            $stmt->bindParam(':issuing_country_id',       $issuing_country_id);        // char
            $stmt->bindParam(':issuing_country',          $issuing_country);           // char

            if ($stmt->execute())
            {
                if ($return == 1)
                {
                    return $person;
                }
                else
                {
                    return $oib_data;
                }
            }
            return $person;
        }
        catch (Exception $error)
        {
            return FALSE;
        }
    }
}