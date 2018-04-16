
<?php 
require 'inc/conn.php';
require 'fun.php';
header('Content-Type: text/html; charset=utf-8');
echo "<h1></h1>";



updateText();
function updateText(){
    echo "<h1>百度地图之采集系统</h1>";
    $userIp = getIp();
    echo "您的IP为:".$userIp;
    if ($userIp=="127.0.0.1") {
        echo " 欢迎管理员!<br />" ;

        printSelectRes(getCityValue("汕头",0),"酒店",1);


        return 0;
    }else{echo " 当前不具备采集权限,支持查询!但是可惜的是查询功能我不想做!";}
    echo "<pre id='preCode'>";
//更新日志
    echo "
/**
* Rem:
* 作者:陈沛杭
* 第四版开发阶段
* @return void
*/
----------------------------------
            UPDATE
----------------------------------
2018年4月3日10:34:29 [第四版][研发]
----------------------------------
更新内容:
1）增加地区查找方式
2）增加数据库
3）增加坐标值的获取

待更新:坐标系统
----------------------------------
            OUTPUT
----------------------------------

";
// ----------------------------------
// 2018年3月26日13:38:25 [第三版]
// ----------------------------------
// 更新内容:
// 1）更新API接口.
// 2）修复关键字查询BUG
// 3）更新最多为 50 条信息的BUG
// 4）添加更多实际型信息

// 待更新:不同平台的价格,坐标系统
// ----------------------------------
// 2018年3月25日21:30:11 [第二版]
// ----------------------------------
// 更新内容：
// 1）修复unicode编码多页转码问题
// 2）修复Json转码出错问题
// 3）取消Cookies,尝试使用其他接口

// 待更新：api接口存在不稳定因素
// ----------------------------------
// 2018年3月24日23:44:53 [第一版]
// ----------------------------------
// 更新内容：
// 1）修复UTF-8编码混乱问题
// 2）接口Cookies更新
//更新日志
    echo "</pre>";
}


// printSelectRes(getCityValue("深圳",0),"烤鱼",1);

/**
 * 获取百度地图中城市的ID或天气
 * @param  [type] $Name [城市名称]
 * @param  [type] $weather[1 返回id,2返回天气]
 * @return [type]       [城市ID ]
 */
function getCityValue($Name,$weather = 0){
/*
    返回json.json内具有天气的json，同时具有可用的uid
 */

    $data = getHttp("http://map.baidu.com/?newmap=1&qt=cur&ie=utf-8&wd=".$Name."&oue=1&res=jc");
    $json=json_decode($data, true);
    // echo "<pre>";var_dump($json);echo "</pre>";
    $nowWeather ="";
    if ($weather) {
        if(!empty($json['weather'])) {
            $json_weather=json_decode($json["weather"], true);
            if(!empty($json_weather['OriginQuery'])) $nowWeather =$nowWeather ." ". $json_weather['OriginQuery'];
            if(!empty($json_weather['pm25'])) $nowWeather =$nowWeather ." PM2.5:". $json_weather['pm25'];
            if(!empty($json_weather['weather0']) && !empty($json_weather['temp0']) && !empty($json_weather['wind0'])) 
                $nowWeather = $nowWeather . " data:" . $json_weather['weather0'] . "[" . $json_weather['temp0'] . "][" . $json_weather['wind0'] . "]";
            if(!empty($json_weather['weather1']) && !empty($json_weather['temp1']) && !empty($json_weather['wind1'])) 
                $nowWeather =$nowWeather ." data2:". $json_weather['weather1']."[".$json_weather['temp1']."][".$json_weather['wind1']."]";
            if(!empty($json_weather['_update_time'])) $nowWeather =$nowWeather ." updateTime:". date("Y-m-d H:i:s",$json_weather['_update_time']); //this update time is rand. very NB!
        }
        if (empty($nowWeather)) $nowWeather = "-1";
        return $nowWeather;
    }else{
        return empty($json['cur_area_id'])?"-1":$json['cur_area_id'];
    }

}




function printSelectRes($cityId,$getName,$bool=0){
    $qt="s";//s and cen
    $rn="10";
    $modNum="10";
    $loopValue=1;
    $creTableName ="";
    if ($cityId<0) {
        return 0;
    }
     for ($i=1; $i <= $loopValue; $i++) { 
        if ($i==$loopValue) $rn=$modNum;
        $getUrl ="http://api.map.baidu.com/?qt=".$qt."&c=".$cityId."&wd=".urlencode($getName)."&rn=".$rn.($i==1?"":"&pn=".$i)."&ie=utf-8&oue=1&fromproduct=jsapi&res=api&callback=BMap._rd._cbk7303&ak=E4805d16520de693a3fe707cdc962045";
        //echo "<br />".$getUrl."<br/>";
        $data=getHttp ($getUrl);

        $pattern = "~content([\s\S]*?),".'"'."current_city~";

        preg_match_all($pattern,$data,$result);  
        //$result = preg_match($pattern, $data);

        $pattern2 = "~\"total\":([\s\S]*?),~";
        preg_match_all($pattern2,$data,$count);  
        if (isset($count[1][0])){if($count[1][0]==0)return 0;} else return 0;
        // var_dump($result);
        if(!empty($result[1][0])){//爸爸做个数组检查,怕越界
            if ($i==1 && $bool==1) {
                //第一次取出循环次数
    
                // var_dump($count);
            if (isset($count[1][0])) {
                    $modNum = $count[1][0] % 10;
                    $modNum > 0 ?$loopValue = (int)($count[1][0] /10 + 1) :$loopValue =(int)($count[1][0] / 10);
                    echo "<br /> ".$count[1][0]." loopValue2:".$loopValue;
                }
            //数据库
            
            $toAscii ="";
            for($nameALen=0;$nameALen<strlen($getName);$nameALen++){$toAscii =$toAscii . "|" . ord($getName[$nameALen]);}

            $creTableName = "form_".$cityId.$toAscii;
            echo $creTableName."|drop and cretabe<br/>";
            $GLOBALS['newDB']->dropTable($creTableName);
            $GLOBALS['newDB']->createTable($creTableName,"
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                  `name` varchar(50) NOT NULL,
                  `price` varchar(20) DEFAULT NULL,
                  `rating` varchar(10) DEFAULT NULL,
                  `level` varchar(10) DEFAULT NULL,
                  `addr` varchar(50) DEFAULT NULL,
                  `tel` varchar(30) DEFAULT NULL,
                  `tag` varchar(200) DEFAULT NULL,
                  PRIMARY KEY (`id`),
                  KEY `name` (`name`)
                ");
        }
                
                $result[1][0] = '{"current_city'. $result[1][0].'}';
                

            }

            //json处理 
            //decode
            $json=json_decode($result[1][0], true);

         // echo "<pre>";
         // // var_dump($result[1][0]);
         // var_dump($json);
         // echo "</pre>";

            //echo "OUTPUT:".$json['current_city'];
            //var_dump($json);
            if (!empty($json['current_city'])) {
                //var_dump($json['current_city']);
                $arrLength=count($json['current_city']);

                for($x=0;$x<$arrLength;$x++)
                {
                    $nowX=($i-1)*10 + $x + 1;
                    echo "数据 ". $nowX .":"."--------------------"."<br />";
                    //

                    $name="";$price="";$rating="";$level="";$addr="";$tel="";$tag="";

                    if(!empty($json['current_city'][$x]['name'])){$name = $json['current_city'][$x]['name'];}
                    if(!empty($json['current_city'][$x]['ext']['detail_info']['pc_realtime_price'])){$price =$json['current_city'][$x]['ext']['detail_info']['pc_realtime_price'];}
                    if(!empty($json['current_city'][$x]['ext']['detail_info']['overall_rating'])){$rating =$json['current_city'][$x]['ext']['detail_info']['overall_rating'];}
                    if(!empty($json['current_city'][$x]['link']['level'])){$level =$json['current_city'][$x]['link']['level'];}
                    if(!empty($json['current_city'][$x]['addr'])){$addr =$json['current_city'][$x]['addr'];}
                    if(!empty($json['current_city'][$x]['tel'])){$tel =$json['current_city'][$x]['tel'];}
                    if(!empty($json['current_city'][$x]['ext']['detail_info']['short_comm'])){$tag =$json['current_city'][$x]['ext']['detail_info']['short_comm'];}//这个是酒店的
                    if (empty($tag)) {
                        if(!empty($json['current_city'][$x]['ext']['detail_info']['cater_tag'])){$tag =$json['current_city'][$x]['ext']['detail_info']['cater_tag'];}//这个是美食的
                    }

                    //"INSERT INTO $tableName $fields VALUES $value"
                    //
                    //
                    //
                    //
                    if (!empty($name)) {
                        $GLOBALS['newDB']->insert($creTableName,"(`id`,`name`, `price`, `rating`, `level`, `addr`, `tel`, `tag`)","('','$name', '$price', '$rating', '$level', '$addr', '$tel', '$tag')");

                        echo "名称:".$name."<br/>".
                             "价格:￥".$price."<br/>".
                             "服务评分:".$rating."<br/>".
                             "等级:".$level."<br/>".
                             "地址:".$addr."<br/>".
                             "电话:".$tel."<br/>".
                             "评价:".$tag;

                        echo "<p style='color:red;'>插入成功</p>";
                    }

                    echo "<br />";
                }
            }
        }
        $GLOBALS['newDB']->dbClose();
        return 1;
    }

 ?>