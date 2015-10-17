<?php //get from sql
    $user = "USER"; $pwd = "PASSWORD"; //please input your DB user & password here
    $con = mysql_connect('HOST',$user,$pwd);
    if (!$con)
        die('Could not connect DB: ' . mysql_error());
    mysql_query("SET NAMES UTF8");
    mysql_select_db('DATABASE', $con) or die('Could not use db: ' . mysql_error());
    //please input your DB data here
    //connect

    function getsite($date,$time,$average = false)
    {
        $query = " SELECT * FROM `wind` ".
            " INNER JOIN site ON `wind`.`windSite`=`site`.`siteName`".
            " WHERE `windDate`='".$date."' AND `windtime`='".$time."'";
        if(!$result = mysql_query($query))
            return false;
        $returndata = array();
        while($data = mysql_fetch_array($result))
        {
            if($average)
            {
                $arr['ws'] = $data['windAWS'];
                $arr['wd'] = $data['windAWD'];
            }
            else
            {
                $arr['ws'] = $data['windWS'];
                $arr['wd'] = $data['windWD'];
            }
            $arr['lat'] = $data['siteTWD97Lat'];
            $arr['lon'] = $data['siteTWD97Lon'];
            $arr['alt'] = $data['siteAltitude'];
            $arr['sph'] = $data['siteSPH'];
            $returndata[$data['windSite']] = $arr;
        }
        return $returndata;
    }
    function realdata($date,$time)
    {
        $query = " SELECT * FROM `wind` ".
            " INNER JOIN site ON `wind`.`windSite`=`site`.`siteName`".
            " WHERE `windDate`='".$date."' AND `windtime`='".$time."'";
        if(!$result = mysql_query($query))
            return false;
        $returndata = array();
        while($data = mysql_fetch_array($result))
        {
            $arr['lat'] = $data['siteTWD97Lat'];
            $arr['lon'] = $data['siteTWD97Lon'];
            $arr['ws'] = $data['windWS'];
            $arr['wd'] = $data['windWD'];
            $arr['sph'] = $data['siteSPH'];
            $arr['add'] = $data['siteAddress'];
            $returndata[$data['siteName']] = $arr;
        }
        return $returndata;
    }
?>

<?php //get from url

    function getsite_web()
    {
        $url = 'http://opendata.epa.gov.tw/ws/Data/AQX/?$select=SiteName,WindSpeed,WindDirec,PublishTime&format=json';
        $content_wind = file_get_contents($url);
        $site = 'http://opendata.epa.gov.tw/ws/Data/AQXSite/?$select=SiteName,TWD97Lon,TWD97Lat&format=json';
        $content_site = file_get_contents($site);
        //get data from epa.gov.tw

        $content_wind = json_decode($content_wind);
        $content_site = json_decode($content_site);
        $return = array();
        foreach ($content_wind as $wind)
        {
            if( $wind->WindSpeed=='' || $wind->WindDirec=='')
                continue;// null data
            $key = findsamename($wind->SiteName,$content_site);
            $return[ $wind->SiteName ] = array(
                'ws' => $wind->WindSpeed,
                'wd' => $wind->WindDirec,
                'lat' => $content_site[$key]->TWD97Lat,
                'lon' => $content_site[$key]->TWD97Lon,
                'alt' => 0,
                'sph' => 10
            );
        }
        return $return;
    }

    function findsamename($name,$array)
    {
        foreach ($array as $key => $value)
        {
            if ($name == $value->SiteName)
            {
                return $key;
            }
        }
        return false;
    }

    function get_web_time()
    {
        $url = 'http://opendata.epa.gov.tw/ws/Data/AQX/?$select=SiteName,WindSpeed,WindDirec,PublishTime&format=json';
        $content_wind = json_decode(file_get_contents($url));
        $pub_time = strtotime($content_wind[0]->PublishTime);
        return $pub_time;
    }
?>