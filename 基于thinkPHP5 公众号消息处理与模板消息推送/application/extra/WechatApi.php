<?php 
use app\index\model\Wechat;

/**
 */

class WechatApi
{
	const MSGTYPE_TEXT = 'text';

	//明文
	private static $token       = "你的加密Token"; //接口token
	private static $appid       = "你的appid";
	private static $appsecret   = "你的appsecret";
	private static $weChatId    = "你的微信服务号Id";
	
	private $_msg;
	private $_receive;
	public $access_token = "no token";
	public $debug        = false;
	public $errCode      = 10000;
	public $errMsg       = "no access";

	public function saveToken()
	{
		if (!$this->getToken()){
			$this->errCode = '41001';
			return $this->isErrToken();
		}
		return $this;
	}
	public function setToken($token)
	{
		Wechat::setToken(self::$appid,self::$appsecret,$token,'7200');
		return $this;

	}
	
	public function getToken()
	{
        $access_token = Wechat::getToken(self::$appid);
        if ($access_token) {
        	$this->access_token = $access_token;
        	return $this;
        }
        return false;
	}

	public function isErrToken()
	{
		if ($this->errCode == '41001' || $this->errCode == '40001' || $this->errCode == '42001') {
			$token = $this->get_access_token(self::$appid,self::$appsecret);
			if($token){
				$this->errCode = '10086';
				$this->errMsg  = 'reply token';
				return $this->setToken($token);
			}
			return false;
		}
	}
	public function getErrCode()
	{
		return $this->errCode;
	}
	/**
	 * For weixin server validation 
	 */	
	private function checkSignature()
	{
		$signature = isset($_GET["signature"])?$_GET["signature"]:'';
		$timestamp = isset($_GET["timestamp"])?$_GET["timestamp"]:'';
		$nonce     = isset($_GET["nonce"])?$_GET["nonce"]:'';

		$tmpArr = array(self::$token, $timestamp, $nonce);
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode($tmpArr);
		$tmpStr = sha1($tmpStr);
		
		if($tmpStr == $signature){
			return true;
		}else{
			return false;
		}
	}
	
	/**
	 * For weixin server validation 
	 * @param bool $return 是否返回
	 */
	public function valid($return=false)
    {
  
    	$this->saveToken();

        $echoStr = isset($_GET["echostr"]) ? $_GET["echostr"]: '';
        if ($return) {
        		if ($echoStr) {
        			if ($this->checkSignature()) 
        				return $echoStr;
        			else
        				return false;
        		} else 
        			return $this->checkSignature();
        } else {
	        	if ($echoStr) {
	        		if ($this->checkSignature())
	        			die($echoStr);
	        		else 
	        			die('no access1');
	        	}  else {
	        		if ($this->checkSignature())
	        			return true;
	        		else
	        			die('no access2');
	        	}
        }
        return false;
    }

	/**
	 * 通用auth验证方法，暂时仅用于菜单更新操作
	 * @param string $appid
	 * @param string $appsecret
	 */
	public function get_access_token($appid='',$appsecret=''){
		if (!$appid || !$appsecret) {
			$appid = $this->appid;
			$appsecret = $this->appsecret;
		}
		//TODO: get the cache access_token
		$result = $this->https_request('https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$appid.'&secret='.$appsecret);
		// var_dump($result);
		// return;
		if ($result)
		{
			$json = json_decode($result,true);
			if (!$json || isset($json['errcode'])) {
				$this->errCode = $json['errcode'];
				$this->errMsg = $json['errmsg'];
				return false;
			}
			$this->access_token = $json['access_token'];
			$expire = $json['expires_in'] ? intval($json['expires_in'])-100 : 3600;
			//TODO: cache access_token
			return $this->access_token;
		}
		return false; 
	}


    private function log($log){
		if ($this->debug) {
			if (is_array($log)) $log = print_r($log,true);

			$log_content = '['.date('Y-m-d H:i:s').'] HTTP_REQUEST '.$_SERVER['REMOTE_ADDR'].' ';
			$log_content .= $_SERVER['REQUEST_METHOD'].' '.$_SERVER['REQUEST_URI'].$_SERVER['QUERY_STRING'];
			$log = $log_content .$log;

			$myfile = fopen("log.txt", "a+") or die("Unable to open file!");
			fwrite($myfile, $log . '\n');
			fclose($myfile);
			return;
		}
    }
    
	/**
	 * 
	 * 回复微信服务器, 此函数支持链式操作
	 * @param string $msg 要发送的信息, 默认取$this->_msg
	 * @param bool $return 是否返回信息而不抛出到浏览器 默认:否
	 */
	public function reply($msg=array(),$return = false)
	{
		if (empty($msg)) 
			$msg = $this->_msg;
		$xmldata=  $this->xml_encode($msg);
		$this->log($xmldata);
		if ($return)
			return $xmldata;
		else
			echo $xmldata;
	}

	/**
	 * 设置回复消息
	 * @param string $text
	 */
	public function sendText($text)
	{
		$msg = array(
			'ToUserName' => $this->getRevFrom(),
			'FromUserName'=>$this->getRevTo(),
			'MsgType'=> self::MSGTYPE_TEXT,
			'Content'=>$this->_auto_text_filter($text),
			'CreateTime'=>time()
		);
		$this->Message($msg);
		$this->reply();
		return $this;
	}
	/**
	 * 发送模板消息
	 * @param array $data 消息结构
	 * ｛
			"touser":"OPENID",
			"template_id":"ngqIpbwh8bUfcSsECmogfXcV14J0tQlEpBO27izEYtY",
			"url":"http://weixin.qq.com/download",
			"topcolor":"#FF0000",
			"data":{
				"参数名1": {
					"value":"参数",
					"color":"#173177"	 //参数颜色
					},
				"Date":{
					"value":"06月07日 19时24分",
					"color":"#173177"
					},
				"CardNumber":{
					"value":"0426",
					"color":"#173177"
					},
				"Type":{
					"value":"消费",
					"color":"#173177"
					}
			}
		}
	 * @return boolean|array
	 */
	public function sendTemplateMessage($data){
		$result = $this->https_request('https://api.weixin.qq.com/cgi-bin/message/template/send?access_token='.$this->access_token,urldecode(json_encode($data)));

		if($result){
			$json = json_decode($result,true);
			if (!$json || !empty($json['errcode'])) {
				$this->errCode = $json['errcode'];
				$this->errMsg = $json['errmsg'];
				return false;
			}
			return $json;
		}
		return false;
	}

	/**
	 * 设置发送消息
	 * @param array $msg 消息数组
	 * @param bool $append 是否在原消息数组追加
	 */
    public function Message($msg = '',$append = false){
    		if (is_null($msg)) {
    			$this->_msg =array();
    		}elseif (is_array($msg)) {
    			if ($append)
    				$this->_msg = array_merge($this->_msg,$msg);
    			else
    				$this->_msg = $msg;
    			return $this->_msg;
    		} else {
    			return $this->_msg;
    		}
    }

	/**
	 * 过滤文字回复\r\n换行符
	 * @param string $text
	 * @return string|mixed
	 */
	private function _auto_text_filter($text) {
		return str_replace("\r\n", "\n", $text);
	}


    /**
     * 获取微信服务器发来的信息
     */
	public function getRev()
	{
		if ($this->_receive) return $this;
		$postStr = file_get_contents("php://input");
		$this->log($postStr);
		if (!empty($postStr)) {
			$this->_receive = (array)simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
		}
		return $this;
	}
	
	/**
	 * 获取微信服务器发来的信息
	 */
	public function getRevData()
	{
		return $this->_receive;
	}
	
	/**
	 * 获取消息发送者
	 */
	public function getRevFrom() {
		if (isset($this->_receive['FromUserName']))
			return $this->_receive['FromUserName'];
		else 
			return false;
	}
	
	/**
	 * 获取消息接受者
	 */
	public function getRevTo() {
		if (isset($this->_receive['ToUserName']))
			return $this->_receive['ToUserName'];
		else 
			return false;
	}
	
	/**
	 * 获取接收消息的类型
	 */
	public function getRevType() {
		if (isset($this->_receive['MsgType']))
			return $this->_receive['MsgType'];
		else 
			return false;
	}
	
	/**
	 * 获取消息ID
	 */
	public function getRevID() {
		if (isset($this->_receive['MsgId']))
			return $this->_receive['MsgId'];
		else 
			return false;
	}
	
	/**
	 * 获取消息发送时间
	 */
	public function getRevCtime() {
		if (isset($this->_receive['CreateTime']))
			return $this->_receive['CreateTime'];
		else 
			return false;
	}
	
	/**
	 * 获取接收消息内容正文
	 */
	public function getRevContent(){
		if (isset($this->_receive['Content']))
			return $this->_receive['Content'];
		else if (isset($this->_receive['Recognition'])) //获取语音识别文字内容，需申请开通
			return $this->_receive['Recognition'];
		else
			return false;
	}
	
	/**
	 * 获取接收消息图片
	 */
	public function getRevPic(){
		if (isset($this->_receive['PicUrl']))
			return $this->_receive['PicUrl'];
		else 
			return false;
	}
	
	/**
	 * 获取接收消息链接
	 */
	public function getRevLink(){
		if (isset($this->_receive['Url'])){
			return array(
				'url'=>$this->_receive['Url'],
				'title'=>$this->_receive['Title'],
				'description'=>$this->_receive['Description']
			);
		} else 
			return false;
	}
	
	/**
	 * 获取接收地理位置
	 */
	public function getRevGeo(){
		if (isset($this->_receive['Location_X'])){
			return array(
				'x'=>$this->_receive['Location_X'],
				'y'=>$this->_receive['Location_Y'],
				'scale'=>$this->_receive['Scale'],
				'label'=>$this->_receive['Label']
			);
		} else 
			return false;
	}
	
	/**
	 * 获取上报地理位置事件
	 */
	public function getRevEventGeo(){
        	if (isset($this->_receive['Latitude'])){
        		 return array(
				'x'=>$this->_receive['Latitude'],
				'y'=>$this->_receive['Longitude'],
				'precision'=>$this->_receive['Precision'],
			);
		} else
			return false;
	}
	
	/**
	 * 获取接收事件推送
	 */
	public function getRevEvent(){
		if (isset($this->_receive['Event'])){
			$array['event'] = $this->_receive['Event'];
		}
		if (isset($this->_receive['EventKey'])){
			$array['key'] = $this->_receive['EventKey'];
		}
		
		if (isset($array) && count($array) > 0) {
			return $array;
		} else {
			return false;
		} 
	}
	
	/**
	 * 获取接收语音推送
	 */
	public function getRevVoice(){
		if (isset($this->_receive['MediaId'])){
			return array(
				'mediaid'=>$this->_receive['MediaId'],
				'format'=>$this->_receive['Format'],
			);
		} else 
			return false;
	}
	
	/**
	 * 获取接收视频推送
	 */
	public function getRevVideo(){
		if (isset($this->_receive['MediaId'])){
			return array(
					'mediaid'=>$this->_receive['MediaId'],
					'thumbmediaid'=>$this->_receive['ThumbMediaId']
			);
		} else
			return false;
	}
	
	/**
	 * 获取接收TICKET
	 */
	public function getRevTicket(){
		if (isset($this->_receive['Ticket'])){
			return $this->_receive['Ticket'];
		} else
			return false;
	}
	
	/**
	* 获取二维码的场景值
	*/
	public function getRevSceneId (){
		if (isset($this->_receive['EventKey'])){
			return str_replace('qrscene_','',$this->_receive['EventKey']);
		} else{
			return false;
		}
	}
	
	/**
	* 获取模板消息ID
	* 经过验证，这个和普通的消息MsgId不一样
	*/
	public function getRevTplMsgID(){
		if (isset($this->_receive['MsgID'])){
			return $this->_receive['MsgID'];
		} else 
			return false;
	}
	
	/**
	* 获取模板消息发送状态
	*/
	public function getRevStatus(){
		if (isset($this->_receive['Status'])){
			return $this->_receive['Status'];
		} else 
			return false;
	}


	public static function xmlSafeStr($str)
	{   
		return '<![CDATA['.preg_replace("/[\\x00-\\x08\\x0b-\\x0c\\x0e-\\x1f]/",'',$str).']]>';   
	} 
	
	/**
	 * 数据XML编码
	 * @param mixed $data 数据
	 * @return string
	 */
	public static function data_to_xml($data) {
	    $xml = '';
	    foreach ($data as $key => $val) {
	        is_numeric($key) && $key = "item id=\"$key\"";
	        $xml    .=  "<$key>";
	        $xml    .=  ( is_array($val) || is_object($val)) ? self::data_to_xml($val)  : self::xmlSafeStr($val);
	        list($key, ) = explode(' ', $key);
	        $xml    .=  "</$key>";
	    }
	    return $xml;
	}	
	
	/**
	 * XML编码
	 * @param mixed $data 数据
	 * @param string $root 根节点名
	 * @param string $item 数字索引的子节点名
	 * @param string $attr 根节点属性
	 * @param string $id   数字索引子节点key转换的属性名
	 * @param string $encoding 数据编码
	 * @return string
	*/
	public function xml_encode($data, $root='xml', $item='item', $attr='', $id='id', $encoding='utf-8') {
	    if(is_array($attr)){
	        $_attr = array();
	        foreach ($attr as $key => $value) {
	            $_attr[] = "{$key}=\"{$value}\"";
	        }
	        $attr = implode(' ', $_attr);
	    }
	    $attr   = trim($attr);
	    $attr   = empty($attr) ? '' : " {$attr}";
	    $xml   = "<{$root}{$attr}>";
	    $xml   .= self::data_to_xml($data, $item, $id);
	    $xml   .= "</{$root}>";
	    return $xml;
	}

	public function https_request($url, $data = null)
	{
	    $curl = curl_init();
	    curl_setopt($curl, CURLOPT_URL, $url);
	    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
	    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
	    if (!empty($data)) {
	        curl_setopt($curl, CURLOPT_POST, 1);
	        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
	    }
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	    $output = curl_exec($curl);
	    curl_close($curl);
	    return $output;
	}

}


 ?>