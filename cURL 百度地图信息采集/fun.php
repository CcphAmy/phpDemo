<?php 
/**
 * getip 摘自discuz
 * @return [type] [description]
 */
 	function getIp(){
	    $ip='未知IP';
	    if(!empty($_SERVER['HTTP_CLIENT_IP'])){
	        return is_ip($_SERVER['HTTP_CLIENT_IP'])?$_SERVER['HTTP_CLIENT_IP']:$ip;
	    }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
	        return is_ip($_SERVER['HTTP_X_FORWARDED_FOR'])?$_SERVER['HTTP_X_FORWARDED_FOR']:$ip;
	    }else{
	        return is_ip($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:$ip;
	    }
	}
	function is_ip($str){
	    $ip=explode('.',$str);
	    for($i=0;$i<count($ip);$i++){ 
	        if($ip[$i]>255){ 
	            return false; 
	        } 
	    } 
	    return preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/',$str); 
	}



	/**
	 * 参考文献:PHP手册 — cURL 函数
	 * URL:http://www.php.net/manual/zh/ref.curl.php 
	 * @param  [type] $getUrl  [URL]
	 * @param  string $cookies [COOKIES]
	 * @return [string]        [body]
	 */
	function getHttp($getUrl,$cookies=""){
	    $curl = curl_init();

	    curl_setopt($curl, CURLOPT_URL, $getUrl);
	    curl_setopt($curl, CURLOPT_HEADER, 1);//调试,返回头
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	    if ($cookies!="") {
	        curl_setopt($curl, CURLOPT_COOKIEFILE, $cookies);
	    }
	    $data = curl_exec($curl);
	    //var_dump($data); 
	    $json=$data;
	    
	    if (curl_getinfo($curl, CURLINFO_HTTP_CODE) == '200') {
	        $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
	        $header = substr($data, 0, $headerSize);
	        $data = substr($data, $headerSize);
	    }
	    curl_close($curl);
	    return $data;
	}
 ?>