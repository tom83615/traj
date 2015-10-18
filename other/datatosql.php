<!doctype html>
<html lang="zh_TW">
    <head>
        <meta content ="text/html; charset=utf8" http-equiv ="Content-type">
        <title>datatosql</title>
    </head>
    <body>
        <?php
            function findsiteN($sitelist,$site)// 取的site 編號
            {
                foreach($sitelist as $key => $value)
                {
                    if($site == $value)
                        return $key;
                }
                return -1;
            }
            function runnext($sitelist,$siteN,$year)//執行下一項
            {
                if((count($sitelist)-1) == $siteN)//最後一項
                {
                    $year++;
                    $siteN = 0;
                }
                else
                    $siteN++;
                if( $year > 2014)
                    return true; //結束囉!!
                else
                {
                    $path ="http://localhost/traj/datatosql.php?site=".$sitelist[$siteN]."&year=".$year;
                    echo "<script>window.open('".$path."');".
                    "setTimeout('window.close()',10000);".
                    "</script>";//10秒
                }
            }
            function puredata($arr)//解決 11#x .1 -.1 的問題 等等人工修改的問題
            {
                if(!is_array($arr))//讓foreach 可以用
                    $arr = array($arr);
                $num_accept = "/^(?:\+|(\-))?([0-9]*)(.[0-9]+)?[#|X|x]?$/m";
                foreach($arr as $key => $value)
                {
                    if(is_numeric($value))
                    {
                        $arr[$key] = $value; //不用修正
                        continue;
                    }
                    if(!preg_match($num_accept,$value,$matches))
                    {
                        echo "無法判定為目標數字 ";
                         $arr[$key] = -1;
                    }
                    else
                    {
                        if (!isset($matches[3])) //整數會沒matches[3] 要補給他
                        {
                            $matches[3] = "";
                        }
                        if($matches[3] != "" && $matches[2] == "" ) //有小數點沒個位數
                            $matches[2] = 0 ;
                        $return = $matches[1].$matches[2].$matches[3];
                        $arr[$key] = (double)$return;
                    }
                }//foreach
                return $arr;
            }
            function combined($wd,$ws,$awd,$aws,$site,$date)//結合成進資料庫所需
            {
                $arr = array(
                    'wd' => $wd,
                    'ws' => $ws,
                    'awd' => $awd,
                    'aws' => $aws,
                    'site' => $site,
                    'date' => $date,
                    );
                return $arr;
            }
            function todb($data)//進資料庫
            {
                set_time_limit ( 0 ); //不得已跑太久，必須重設最長時間
                $kind = array(
                    'wd' => '風向',
                    'ws' => '風速',
                    'awd' => '平均風向',
                    'aws' => '平均風速',
                    'site' => '站點',
                    'date' => '日期'
                    );
                foreach ($kind as $key => $value)
                {
                    if(!isset($data[$key]))
                    {
                        echo ('缺失'.$value);
                        return false;
                    }
                    if (is_array($data[$key]))
                        if($data[$key] == array() )
                        {
                            echo ('缺失'.$value);
                            return false;
                        }
                }
                for($i = 0 ; $i < 24 ; $i++)
                {
                    if(strtotime($data['date']) < strtotime("2013/1/1")) //2010~2012
                    {
                        $time = $i+1;
                        if ($time == 24)
                        {
                            $date = date("Y/m/d",strtotime($data['date'])+24*3600); //如果 time= 24 會加一天
                            $time = 0;
                        }
                        else
                            $date = $data['date'];
                    }
                    else
                    {
                        $time = $i;
                        $date = $data['date']; //都不用改
                    }
                    //做日期判定 因為環保署資料不齊
                    if($data['wd'][$i] == ""||$data['ws'][$i] == ""||$data['awd'][$i] == ""||$data['aws'][$i] == "")
                    {
                        echo "  確認".$data['site'].$date." ".$time.":00  ...資料缺失，跳過</br>";
                        continue;
                    }
                    //缺失項跳出
                    $where = " WHERE".
                            "`windSite`="."'".$data['site']."' AND ".
                            "`windDate`="."'".$date."' AND ".
                            "`windTime`="."'".$time."'";
                    $select = "SELECT `windSite` FROM wind";
                    echo"  確認".$data['site'].$date." ".$time.":00";
                    $result = mysql_query($select.$where);
                    $result = mysql_fetch_array($result);
                    if($result)//有資料 update
                    {
                        $update = "UPDATE wind SET".
                            "`windWD`='".$data['wd'][$i]."',".
                            "`windWS`='".$data['ws'][$i]."',".
                            "`windAWD`='".$data['awd'][$i]."',".
                            "`windAWS`='".$data['aws'][$i]."'";
                        echo"  ...更新</br>";
                        mysql_query($update.$where) or die(mysql_error());
                    }
                    else//無資料 insert
                    {
                        $insert = "INSERT INTO wind (`windSite`,`windDate`,`windTime`,`windWD`,`windWS`,`windAWD`,`windAWS`) VALUES (".
                            "'".$data['site']."',".
                            "'".$date."',".
                            "'".$time."',".
                            "'".$data['wd'][$i]."',".
                            "'".$data['ws'][$i]."',".
                            "'".$data['awd'][$i]."',".
                            "'".$data['aws'][$i]."'".") ";
                        echo"  ...插入</br>";
                        mysql_query($insert) or die(mysql_error());
                    }
                }
                return true;
            }
            //site data 依序執行
            $site = array(
                '三義','三重','中壢','中山','二林',
                '仁武','冬山','前金','前鎮','南投',
                '古亭','善化','嘉義','土城','埔里',
                '基隆','士林','大同','大園','大寮',
                '大里','安南','宜蘭','小港','屏東',
                '崙背','左營','平鎮','彰化','復興',
                '忠明','恆春','斗六','新店','新港',
                '新營','新竹','新莊','朴子','松山',
                '板橋','林口','林園','桃園','楠梓',
                '橋頭','永和','汐止','沙鹿','淡水',
                '湖口','潮州','竹山','竹東','線西',
                '美濃','臺南','臺東','臺西','花蓮',
                '苗栗','菜寮','萬華','萬里','西屯',
                '觀音','豐原','金門','關山','陽明',
                '頭份','馬公','馬祖','鳳山','麥寮',
                '龍潭');// 76
            $filedata = array(
                    '2010' => array(
                            'header' => '2010/99年',
                            'footer' => '站_20110329.csv',
                    ),
                    '2011' => array(
                            'header' => '2011/100年',
                            'footer' => '站_20120409.csv',
                    ),
                    '2012' => array(
                            'header' => '2012/101年',
                            'footer' => '站_20130424.csv',
                    ),
                    '2013' => array(
                            'header' => '2013/102年',
                            'footer' => '站_20140417.csv',
                    ),
                    '2014' => array(
                            'header' => '2014/103年',
                            'footer' => '站_20150324.csv',
                    )
                );// 5 year
            //
            //main function
            //
            $siteN = findsiteN($site,$_GET['site']);
            $sitename = $site[$siteN];
            $year = $_GET['year'];

            $user = "使用者"; $pwd = "密碼";
            $con = mysql_connect('主機',$user,$pwd);
            if (!$con)
                die('Could not connect DB: ' . mysql_error());
            mysql_query("SET NAMES UTF8");
            mysql_select_db('資料庫', $con) or die('Could not use db : ' . mysql_error());
            //請填入自己的DB資料
            //comnnect;
            if($sitename == '麥寮' && $year == '2010')//2010沒有麥寮站
                continues;
            echo "進行".$sitename."站</br>";
            $path = $filedata[$year]['header'].$sitename.$filedata[$year]['footer'];
            $path = iconv("UTF-8","Big5",$path);
            $file = fopen($path,"r");
            $date = 0;
            $timer = time();
            $wd = array();
            $ws = array();
            $awd = array();
            $aws = array();
            //初始化
            while(! feof($file))
            {
                $csv = fgetcsv($file);//讀csv的 ,
                if(!$csv)
                    break;//錯誤時跳出(最後一筆一定是false)
                foreach ($csv as $key => $value)
                {
                    $csv[$key] = iconv("Big5","UTF-8",$value);
                }//轉換編碼(windows)
                if($csv[0] == "日期")
                    continue;
                if($csv[0] != $date)
                {
                    if ($date != 0)//第一次不用進DB
                    {
                        $data = combined($wd,$ws,$awd,$aws,$sitename,$date);// value為測站名稱
                        todb($data);
                        $wd = array();
                        $ws = array();
                        $awd = array();
                        $aws = array();
                        //重設 避免缺值再跑一次
                    }
                    $date = $csv[0];//指定
                }
                $kind = $csv[2];
                unset($csv[0],$csv[1],$csv[2]);
                $csv = array_values($csv);
                //去除 日期 測站 類別3項
                switch($kind)
                    {
                        case "WD_HR":
                            $awd = puredata($csv);
                        break;
                        case "WIND_DIREC":
                            $wd = puredata($csv);
                        break;
                        case "WS_HR":
                            $aws = puredata($csv);
                        break;
                        case "WIND_SPEED":
                            $ws = puredata($csv);
                        break;
                    }//各自進入array
            }
            $data = combined($wd,$ws,$awd,$aws,$sitename,$date);
            todb($data);
            //最後一次要進db
            $spent = getdate(time()-$timer);
            echo "完成 花了 ".$spent["minutes"]."分".$spent["seconds"]."秒"."</br>";
            die(); // 要繼續請拿掉本行
            runnext($site,$siteN,$year);//進行下一個
        ?>
    </body>
</html>