<html lang="zh_TW">
    <head>
        <meta content ="text/html; charset=utf8" http-equiv ="Content-type">
    </head>
    <body>
<?php
    include '../sample/datainput.php';
    include '../windmap/class.php';
    include '../windmap/windengine_lib.php';
    include '../windmap/statistics.php';
	    if(isset($_GET['num']))
        {
            $num  = $_GET['num'];
        }
        else
            $num = 100;
        $result = array();
        $timer = time();
        $factor = array(
            'earthRadius' => 6371000,//approximate radius of earth in meters (average value)
            'correction_factor' => 0.1,
            'wpp_law_exponent' => 1/7,
            'select_error' => 0.01
            );
        $start = mktime(0, 0, 0, 1, 1, 2010);
        $end = mktime(0, 0, 0, 12, 31, 2014);
        $r['speed'] = array(
                'real' =>array(),
                'simul' =>array()
            );
        $r['direction'] = array(
                'real' =>array(),
                'simul' =>array()
            );
        echo        '<table border="1">'.
                        '<thead>'.
                            '<th>站點-時間</th>'.
                            '<th>實際風速</th>'.
                            '<th>模擬風速</th>'.
                            '<th>實際風向</th>'.
                            '<th>模擬風向</th>'.
                        '</thead>'.
                        '<tbody>';
            for($i =0 ;$i<$num ;$i++)
            {
                set_time_limit ( 0 );
                $now = rand($start,$end);
                $date = date('Y/m/d',$now);
                $time = date('G',$now);
                $realdata = realdata($date,$time);
                $sitedata = getsite($date,$time);
                foreach($realdata as $key => $value)
                {
                    $center = array(
                    'date' => $date,
                    'time' => $time,
                    'lat' => $value['lat'],
                    'lon' => $value['lon'],
                    'alt' => 100,
                    'wh' => 10
                    );
                    $value['ws'] = wpp_law($value['ws'],$value['sph'],10,1/7);
                    $simulationdata = windengine($center,$sitedata,$factor,true);
                    $result[$key] = array(
                        'realws' => round($value['ws'],2),
                        'realwd' => round($value['wd'],2),
                        'simulws' => round($simulationdata['ws'],2),
                        'simulwd' => round($simulationdata['wd'],0)
                        );
                }

            $sum_s = 0;
            $sum_w = 0;
            foreach($result as $site => $data)
            {
                echo        '<tr>'.
                                '<td>'.$site.'-'.$date.' '.$time.':00</td>'.
                                '<td>'.$data['realws'].'</td>'.
                                '<td>'.$data['simulws'].'</td>'.
                                '<td>'.$data['realwd'].'</td>'.
                                '<td>'.$data['simulwd'].'</td>'.
                            '</tr>';
                $r['speed']['real'] = array_merge($r['speed']['real'],array($data['realws']));
                $r['speed']['simul'] = array_merge($r['speed']['simul'],array($data['simulws']));
                $r['direction']['real'] = array_merge($r['direction']['real'],array($data['realwd']));
                $r['direction']['simul'] = array_merge($r['direction']['simul'],array($data['simulwd']));
            }
        }
        $r_s = round(correlation_coefficient($r['speed']['real'],$r['speed']['simul']),3);
        $r_w = round(correlation_coefficient($r['direction']['real'],$r['direction']['simul']),3);
        echo            '<tr>'.
                            '<td>'.'相關係數'.'</td>'.
                            '<td>sf:'.$factor['select_error'].'</td>'.
                            '<td>cf:'.$factor['correction_factor'].'</td>'.
                            '<td>'.$r_s.'</td>'.
                            '<td>'.$r_w.'</td>'.
                        '</tr>';
        echo        '</tbody>'.
                '</table>';
        $spent = getdate(time()-$timer);
        echo "完成 花了 ".$spent["minutes"]."分".$spent["seconds"]."秒"."</br>";
?>

</body>