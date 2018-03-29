<?php 

 header('Content-Type: text/html; charset=utf-8');


mainInit();


function mainInit(){

$getName="酒店";

    echo "<pre>";

 for ($i=1; $i <= 5; $i++) { 

    $getUrl ="http://api.map.baidu.com/?qt=s&c=257&wd=".$getName."&rn=50&pn=".$i."&ie=utf-8&oue=1&fromproduct=jsapi&res=api&callback=BMap._rd._cbk73861&ak=E4805d16520de693a3fe707cdc962045";

    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, $getUrl);
    curl_setopt($curl, CURLOPT_HEADER, 1);//为了调试阶段,显示Header头
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    //curl_setopt($curl, CURLOPT_COOKIEFILE, $cookies);
    //不需要cookies
    $data = curl_exec($curl);
    //var_dump($data); 
    $json=$data;
    //调试阶段,header头分离
    if (curl_getinfo($curl, CURLINFO_HTTP_CODE) == '200') {
        $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $header = substr($data, 0, $headerSize);
        $data = substr($data, $headerSize);
    }
    curl_close($curl);

//  去除Header 头
    $pattern = "~content([\s\S]*?),".'"'."current_city~";
    preg_match_all($pattern,$data,$result);  
    //$result = preg_match($pattern, $data);

    //var_dump($result);

    if(!empty($result[1][0])){//爸爸做个数组检查,怕越界
            if ($i==1) {
                echo "<h1>百度地图之采集系统</h1>";

                echo "<p>这一波数据还是稳得呀!</p><br />";
//更新日志
                echo "
/**
* Rem:
* 作者:陈沛杭
* 参考文献:PHP手册 — cURL 函数
* URL:http://www.php.net/manual/zh/ref.curl.php 
* 暂时只爬取以广州市为基点的 250 条酒店信息,可更改参数
* @return void
*/
----------------------------------
            UPDATE
----------------------------------
2018年3月26日13:38:25 [第三版]
----------------------------------
更新内容:
1）更新API接口.
2）修复关键字查询BUG
3）更新最多为 50 条信息的BUG
4）添加更多实际型信息

待更新:不同平台的价格,坐标系统
----------------------------------
2018年3月25日21:30:11 [第二版]
----------------------------------
更新内容：
1）修复unicode编码多页转码问题
2）修复Json转码出错问题
3）取消Cookies,尝试使用其他接口

待更新：api接口存在不稳定因素
----------------------------------
2018年3月24日23:44:53 [第一版]
----------------------------------
更新内容：
1）修复UTF-8编码混乱问题
2）接口Cookies更新
----------------------------------
             OUTPUT
----------------------------------

";
//更新日志
            }
            
            $result[1][0] = '{"current_city'. $result[1][0].'}';
            //var_dump($result[1][0]);

        }

        //json处理 
        //decode
        $json=json_decode($result[1][0], true);
        //echo "OUTPUT:".$json['current_city'];
        //var_dump($json);
        if (!empty($json['current_city'])) {
            //var_dump($json['current_city']);
            $arrLength=count($json['current_city']);

            echo "</pre>";
            for($x=0;$x<$arrLength;$x++)
            {
                $nowX=($i-1)*50 + $x + 1;
                echo "数据 ". $nowX .":"."--------------------"."<br />";
                if(!empty($json['current_city'][$x]['name'])){echo "酒店名称:".$json['current_city'][$x]['name']."<br />";}
                if(!empty($json['current_city'][$x]['ext']['detail_info']['pc_realtime_price'])){echo "真实价格:￥".$json['current_city'][$x]['ext']['detail_info']['pc_realtime_price']."<br />";}
                if(!empty($json['current_city'][$x]['ext']['detail_info']['service_rating'])){echo "服务评分:".$json['current_city'][$x]['ext']['detail_info']['service_rating']."<br />";}
                if(!empty($json['current_city'][$x]['link']['level'])){echo "酒店等级:".$json['current_city'][$x]['link']['level']."<br />";}
                if(!empty($json['current_city'][$x]['addr'])){echo "地址:".$json['current_city'][$x]['addr']."<br />";}
                if(!empty($json['current_city'][$x]['tel'])){echo "电话:".$json['current_city'][$x]['tel']."<br />";}
                if(!empty($json['current_city'][$x]['ext']['detail_info']['short_comm'])){echo "评价:".$json['current_city'][$x]['ext']['detail_info']['short_comm']."<br />";}

                echo "<br />";
            }
        }
    }
}

 ?>