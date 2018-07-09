<?php
namespace app\index\controller;
use think\Controller;
use think\Session;

use app\index\model\Wechat;
use WechatApi;

class Tui extends Controller
{
	public function tui()
	{

		$obj     = new WechatApi();
		if (!$obj->valid()) return;

		$obj->getRev();// 获取消息
		$getText = $this->keyrep($obj->getRevContent(),$obj);
		$obj->sendText($getText);
	}

    public function keyrep($key,&$obj) {

        if (strpos($key, '订阅')!==false) {

			$reText = '订阅信息失败,例:订阅 陈沛杭##开发部';
			$qian   = array("订阅");
			$repkey = str_replace($qian, '',$key);

        	if (strpos($repkey, '##')!==false) {
        		$tempKeyArr = explode("##",$repkey);
        		if (count($tempKeyArr) == 2) {
        			//数据可能需要经过一些处理
        				echo 'success';
						$reText        = "";
						$tempKeyArr[0] = Fun::CTrim($tempKeyArr[0]);
						$tempKeyArr[1] = Fun::CTrim($tempKeyArr[1]);
						$rem           = "加入部门组成功\n姓名：".$tempKeyArr[0]."\n部门：".$tempKeyArr[1];

        				$this->templateSubscribe1($obj,"https://www.baidu.com/","学生服务中心",$rem);
					    Wechat::setSubscribe($obj->getRevFrom(),1,$tempKeyArr[1],$tempKeyArr[0],"wxSubscribe");
        		}

        	}
            return $reText;
        }
        //
        if ($key == '小助手') {
            $mt = mt_rand(1, 2);
            $array = array(1 => 'qq.cn', 2 => 'www.qq.cn');
            return $array[$mt];
        }

        return "小助手.\n官网:www.qq.cn\n接口指令:".$obj->getRevContent()."\n指令响应:未处理";
    }
    public function templateSubscribe1(&$obj,$url='',$first='',$keyword1='',$remark='')
    {

		$data   = array(
			'touser'      => $obj->getRevFrom(),
			'template_id' => "HRatBJwYcpYR2fAm78FLON45ZK9Otq-cSodrrhXbOyc",
			'url'         => $url, 
			'data'        => array(
	            'first'     => array(
	                'value' => $first,
	                'color' => "#000"
	            ),
	            'keyword1'  => array(
	                'value' => $keyword1,
	                'color' => "#000"
	            ),
	            'keyword2'  => array(
	                'value' => date("Y-m-d"),
	                'color' => "#173177"
	            ),
	            'remark'    => array(
	                'value' => $remark."\n订阅成功哦,检查信息正确,以确保第一时间信息到手哦.",
	                'color' => "#3d3d3d"
	            )
	        )
	    );
	    $obj->sendTemplateMessage($data);
		if ($obj->isErrToken()) $obj->sendTemplateMessage($data);
    }


	public function getCurrentUrl(){
		$scheme     = $_SERVER['REQUEST_SCHEME'];
		$domain     = $_SERVER['HTTP_HOST']; 
		$requestUri = $_SERVER['REQUEST_URI']; 
		//将得到的各项拼接起来
		$currentUrl = $scheme . "://" . $domain . $requestUri;
	    return $currentUrl; //传回当前url
 	}
}