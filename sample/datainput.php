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
