<?php
namespace app\index\controller;

class Index
{
    public function index()
    {
        return '需要配置database.php,extra/WechatrApi.php的微信公众号信息,且需要导入数据库.';
    }
}
