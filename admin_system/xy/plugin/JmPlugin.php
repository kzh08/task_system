<?php
final class JmPlugin {
    public function str2hex($s)   
    {       
        $r = "";   
        $hexes = array ("0","1","2","3","4","5","6","7","8","9","a","b","c","d","e","f");   
        for ($i=0; $i<strlen($s); $i++)   
            $r .= ($hexes [(ord($s{$i}) >> 4)] . $hexes [(ord($s{$i}) & 0xf)]);   
        return $r;   
    }   
      
    //����   
    public function hex2str($s)   
    {   
        $r = "";   
        for ( $i = 0; $i<strlen($s); $i += 2)   
        {   
            $x1 = ord($s{$i});   
            $x1 = ($x1>=48 && $x1<58) ? $x1-48 : $x1-97+10;   
            $x2 = ord($s{$i+1});   
            $x2 = ($x2>=48 && $x2<58) ? $x2-48 : $x2-97+10;   
            $r .= chr((($x1 << 4) & 0xf0) | ($x2 & 0x0f));   
        }   
        return $r;   
    }    


    public function strEncode($content){
        $code = $this->str2hex(urlencode($content));
        return $code;
    }

    public function strDecode($code){
        return urldecode($this->hex2str($code));
    }

	public function randNum(){
		$length = mt_rand(5,16);
		$bytes = openssl_random_pseudo_bytes($length);
		$hex   = bin2hex($bytes);
		return $hex;
	}

	public function randNumEncode($str){
		if($str == ''){
			return false;
		}
        $key            = 'kiwe0dg3kr30fe0k2';
		$cipher 		= MCRYPT_DES;
		$modes  		= MCRYPT_MODE_ECB;
		$iv 			= mcrypt_create_iv(mcrypt_get_iv_size($cipher,$modes),MCRYPT_RAND);
		$str_encrypt 	= mcrypt_encrypt($cipher,$key,$str,$modes,$iv);
		return $str_encrypt;
	}

	public function randNumDncode($str){
		if($str == ''){
			return false;
		}
        $key            = 'kiwe0dg3kr30fe0k2';
		$cipher 		= MCRYPT_DES;
		$modes  		= MCRYPT_MODE_ECB;
		$iv 			= mcrypt_create_iv(mcrypt_get_iv_size($cipher,$modes),MCRYPT_RAND);
		$str_decrypt 	= mcrypt_decrypt($cipher,$key,$str,$modes,$iv);
		return $str_decrypt;
	}
	
    
}
?>