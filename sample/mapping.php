<?php
    include 'datainput.php';
    include '../windmap/class.php';
    include '../windmap/windengine_lib.php';
    if($_GET['t'] == 'now')
        $time = get_web_time();
    else
        $time = strtotime($_GET['t']);
    $center = array(
            'date' => date("Y/m/d",$time),
            'time' => date("G",$time),
            'alt' => 0,
            'wh' => 10
            );
    $factor = array(
        'earthRadius' => 6371000,//approximate radius of earth in meters (average value)
        'correction_factor' => 0.55,
        'wpp_law_exponent' => 1/7,
        'select_error' => 0.04
        );

    if($_GET['t'] == 'now')
        $sitedata = getsite_web();
    else
        $sitedata = getsite($center['date'],$center['time']);

    $lon_start = 118.5;
    $lon_end = 122;
    $lon_length = $lon_end - $lon_start;
    $lon_num = 20;

    $lat_start = 22;
    $lat_end = 25.3;
    $lat_length = $lat_end - $lat_start;
    $lat_num = 20;

    $arrows = '';
    for($lon_step = 0; $lon_step < $lon_num; $lon_step++)
    {
        for($lat_step = 0; $lat_step < $lat_num; $lat_step++)
        {
            $center['lon'] = $lon_start+$lon_step*($lon_length/$lon_num);
            $center['lat'] = $lat_start+$lat_step*($lat_length/$lat_num);

            $data = windengine($center,$sitedata,$factor,false);
            $nowpoint = array(
                'lon' => $center['lon'],
                'lat' => $center['lat'],
                'ws' => $data['ws'],
                'wd' => $data['wd']
                );
            $arrow_l = min($lon_length/$lon_num,$lat_length/$lat_num);
            $arrows .= toRarrows($nowpoint,$arrow_l).'<br/>';
        }
    }
    $base = 'library(RgoogleMaps);'.'<br/>'.
            'side_lons <- c(118.5, 122);'.'<br/>'.
            'side_lats <- c(22, 25.3);'.'<br/>'.
            'center <- c(mean(side_lats), mean(side_lons));'.'<br/>'.
            'zoom <- min(MaxZoom(range(side_lats), range(side_lons)));'.'<br/>'.
            'MyMap <- GetMap(center=center, zoom=zoom+1,destfile="'.date("Y-m-d",$time).'_'.date("G",$time).'.png",NEWMAP=FALSE);'.'<br/>'.
            'PlotOnStaticMap(MyMap);'.'<br/>';
    $print = 'dev.copy(png,"D:/xampp/htdocs/traj_test/img/'.date("Y-m-d",$time).'_'.date("G",$time).'.png"
            ,width=10,height=10,units="in",res=100)'.'<br/>'.
            "dev.off();";
    echo $base.$arrows.$print;
?>
<?php
function switchangle($angle)
{
    $angle=($angle*-1)+360;
    if($angle>=360)
        return ($angle-360)/360*2*pi();
    else
        return $angle/360*2*pi();
}

function toRarrows($arr,$arrow_l)
{
   $lonstart =  $arr['lon'];
   $latstart =  $arr['lat'];
   $angle = switchangle($arr['wd']);
   $length = $arr['ws']*0.25*$arrow_l;
   $str =   "PlotArrowsOnStaticMap(MyMap,lon0=".round($lonstart,3).
            ",lat0=".round($latstart,3).
            ",lon1=".round($lonstart+$length*cos($angle),3).
            ",lat1=".round($latstart+$length*sin($angle),3).
            ",lwd=2,length=".$length*0.8.",add = TRUE);";
   return $str;
}
?>