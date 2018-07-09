<?php
namespace app\index\model;

use \think\Model;
use think\Input;


class Wechat extends Model
{
    public static function getToken($appid)
    {
		$where['appid'] = $appid;
		$user           = Wechat::name('wxToken')->where($where)->find();
        if ($user) {
            return $user['access_token'];
        }else{
            return false;
        }
    }

    public static function setToken($appid,$appsecret,$access_token,$expires_in,$atime='')
    {
        if (empty($atime)) $atime = time();

        $user = new Wechat;
        $user->name('wxToken');

        $dataArr = array(
					'appid'        => $appid,
					'appsecret'    => $appsecret,
					'access_token' => $access_token,
					'expires_in'   => $expires_in,
					'atime'        => $atime
        );

        if (Wechat::name('wxToken')->where('appid',$appid)->find()) $user->update($dataArr);
        else $user->data($dataArr,true)->save();
    }


    public static function setSubscribe($openid,$type,$name='',$alias='',$TABLE='wxSubscribe',$atime='')
    {
        if (empty($atime)) $atime = time();

        $user = new Wechat;
        $user->name($TABLE);

        $dataArr = array(
					'openid' => $openid,
					'type'  => $type,//类型: 0 普通订阅小助手 1 订阅课程推送
					'name'  => $name,
					'alias' => $alias,
					'atime' => $atime
        );

        if (Wechat::name($TABLE)->where('openid',$openid)->find()) $user->update($dataArr);
        else $user->data($dataArr,true)->save();
    }

    public static function getSubscribeArr($type,$TABLE='wxSubscribe')
    {
    	$user = new User();
    	$user->name($TABLE);
    	return $user->where('type', $type)->select();;
	}




}