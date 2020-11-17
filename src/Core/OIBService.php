<?php
namespace Core;

class OIBService
{
    /**
     * @param array $data
     * @param string $iv_hex
     * @param string $encryption_key -> max size 32 byte za MCRYPT_RIJNDAEL_128
     * @return string $encrypted
     */
    public static function encrypt($data, $iv_hex, $encryption_key) {
        $data = self::add_padding(json_encode($data));
        $encrypted = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $encryption_key, $data, MCRYPT_MODE_CBC, hex2bin($iv_hex));
        return bin2hex($encrypted);
    }

    public static function decrypt($data, $iv, $encryption_key) {
        $data = hex2bin($data);
        $iv = hex2bin($iv);
        $dec = self::remove_padding(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $encryption_key, $data, MCRYPT_MODE_CBC, $iv));
        return $dec;
    }

    /*
        http://stackoverflow.com/questions/7314901/how-to-add-remove-pkcs7-padding-from-an-aes-encrypted-string
    */
    private static function add_padding($string){
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $block = mcrypt_get_block_size('rijndael-128', 'cbc');
        $pad = $block - (mb_strlen($string) % $block);
        $string .= str_repeat(chr($pad), $pad);
        return $string;
    }

    /*
        http://stackoverflow.com/questions/7314901/how-to-add-remove-pkcs7-padding-from-an-aes-encrypted-string
    */
    private static function remove_padding($string){
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $block = mcrypt_get_block_size('rijndael-128', 'cbc');
        /** @noinspection PhpUnusedLocalVariableInspection */
        $pad = ord($string[($len = mb_strlen($string)) - 1]);
        $len = mb_strlen($string);
        $pad = ord($string[$len-1]);
        return mb_substr($string, 0, mb_strlen($string) - $pad);
    }

    public static function generateIV(){
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        return bin2hex($iv);
    }

    public static function prepare_request($request_array, $encryption_key, $integrity_key){
        $send_iv = self::generateIV();
        $encrypted_data = self::encrypt($request_array, $send_iv, $encryption_key);
        $result = array(
            'data' => $encrypted_data,
            'iv' => $send_iv,
            'integrity' => self::generate_integrity($encrypted_data, $send_iv,$integrity_key)
        );
        return base64_encode(json_encode($result));
    }

    public static function validate($response, $encryption_key, $integrity_key){
        $ret=array("error"=>true);
        $json_decoded=json_decode(base64_decode($response));
        if ($json_decoded === null){
            $ret["error_msg"]="Greška";
            return $ret;
        }

        if (isset($json_decoded->error) && $json_decoded->error==true){
            $ret["error_msg"]=$json_decoded->error_msg;
            return $ret;
        }

        if (self::validate_integrity($json_decoded->data, $json_decoded->iv, $json_decoded->integrity, $integrity_key) === false) {
            $ret["error_msg"]="Primljeni odgovor nije vjerodostojan";
            return $ret;
        }

        $decrypted=self::decrypt($json_decoded->data, $json_decoded->iv, $encryption_key);
        $decr_json_decoded = json_decode(utf8_encode(trim($decrypted)));

        if ($decr_json_decoded === null){
            $ret['error_msg']="Poruka nije uspješno dekriptirana";
            return $ret;
        }

        $ret['error']=false;
        $ret['content']=$decr_json_decoded;
        return $ret;
    }

    private static function generate_integrity($data,$iv, $integrity_key){
        return hash_hmac('sha256', $iv.$data, $integrity_key);
    }

    private static function validate_integrity($data, $iv, $integrity, $integrity_key){
        $calculated =  hash_hmac('sha256', $iv.$data, $integrity_key);
        if ($calculated == $integrity) return true;
        return false;
    }


    public static function oib_grab($search_oib, $admin_oib, $config){
        $oib['korisnik_oib'] = (string) $admin_oib;
        $oib['oib'] = (string) $search_oib;
        $oib['servis'] = 'oib';
        $oib['vrsta'] = $config['vrsta'];
        $oib['url'] = $config['url'];

        $url = $oib['url']."/api/service/".$config['service_name']."/".OIBService::prepare_request($oib, $config['enc_key'], $config['int_key']);
        $response=file_get_contents($url);
        if($response == null) return FALSE;
        //throw new Exception("Greška prilikom dohvata OIB-a!");

        $re=OIBService::validate($response, $config['enc_key'], $config['int_key']);
        if ($re['error']===true){
            return FALSE;
            //throw new Exception("Greška prilikom dohvata OIB-a! ". $re['error_msg'] );
        }

        $content=$re['content'];
        if($content->Greske->ImaGresaka=="true"){
            return FALSE;
            //throw new Exception($content->Greske->Greska->Poruka);
        }
        return $content->oib;
    }

}