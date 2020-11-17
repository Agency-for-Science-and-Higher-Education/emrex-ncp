<?php

namespace ISVU;

/**
 * Class ISVUI
 * @package ISVU
 */
class Connector
{
    private $ch;
    /**
     * Counstructor
     */
    public function __construct()
    {
        $this->ch = curl_init();

        curl_setopt($this->ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.52 Safari/537.17');
        curl_setopt($this->ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($this->ch, CURLOPT_VERBOSE, 1);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($this->ch, CURLOPT_USERPWD, "emrex.azvo:ezij11orok799");
    }

    public function get($url)
    {
        curl_setopt($this->ch, CURLOPT_URL, $url);

        $output = curl_exec($this->ch);

        $myfile = fopen("newfile.txt", "a") or die("Unable to open file!");
        fwrite($myfile, $url);
        fwrite($myfile, "\r");
        $results = print_r($output, true);
        fwrite($myfile, "1 " . $results);
        fwrite($myfile, "\r");
        fclose($myfile);

        return json_decode($output, true);
    }

    public function getStatus()
    {
        return curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
    }
}
