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
        $date = $_GET['date'];
        $result = array();
        $factor = array(
            'earthRadius' => 6371000,//approximate radius of earth in meters (average value)
            'correction_factor' => 0.55,
            'wpp_law_exponent' => 1/7,
            'select_error' => 0.04
            );
        echo '<h2>'.$date.'</h2>';
        for($time =0 ;$time<24 ;$time++)
        {
            set_time_limit ( 0 );
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
                $result[$key][$time] = array(
                    'realws' => round($value['ws'],2),
                    'realwd' => $value['wd'],
                    'simulws' => round($simulationdata['ws'],2),
                    'simulwd' => round($simulationdata['wd'],0),
                    'errorws' => round(abs(round($simulationdata['ws'],2) / round($value['ws'],2)),2),
                    'errorwd' => round(abs($simulationdata['wd'] / round($value['wd'],2)),2)
                    );
            }
        }
        $sum_s = 0;
        $sum_w = 0;
        foreach($result as $site => $value_t)
        {
            echo '<table border="1">'.
                    '<thead>'.
                        '<th>'.$site.'</th>'.
                        '<th>實際風速</th>'.
                        '<th>模擬風速</th>'.
                        '<th>誤差</th>'.
                        '<th>實際風向</th>'.
                        '<th>模擬風向</th>'.
                    '<th>誤差</th>'.
                    '</thead>'.
                    '<tbody>';
            $r['speed'] = array(
                    'real' =>array(),
                    'simul' =>array()
                );
            $r['direction'] = array(
                    'real' =>array(),
                    'simul' =>array()
                );
            foreach($value_t as $time => $data)
            {
                echo    '<tr>'.
                            '<td>'.$time.'</td>'.
                            '<td>'.$data['realws'].'</td>'.
                            '<td>'.$data['simulws'].'</td>'.
                            '<td>'.$data['errorws'].'</td>'.
                            '<td>'.$data['realwd'].'</td>'.
                            '<td>'.$data['simulwd'].'</td>'.
                            '<td>'.$data['errorwd'].'</td>'.
                        '</tr>';
                $r['speed']['real'][$time] = $data['realws'];
                $r['speed']['simul'][$time] = $data['simulws'];
                $r['direction']['real'][$time] = $data['realwd'];
                $r['direction']['simul'][$time] = $data['simulwd'];
            }
            $r_s = round(correlation_coefficient($r['speed']['real'],$r['speed']['simul']),3);
            $r_w = round(correlation_coefficient($r['direction']['real'],$r['direction']['simul']),3);
            echo    '<tr>'.
                            '<td>'.'相關係數'.'</td>'.
                            '<td></td>'.
                            '<td></td>'.
                            '<td>'.$r_s.'</td>'.
                            '<td></td>'.
                            '<td></td>'.
                            '<td>'.$r_w.'</td>'.
                        '</tr>';
            echo    '</tbody>'.
                '</table>';
        $sum_s += $r_s;
        $sum_w += $r_w;
        }
        echo $sum_s.'  '.$sum_w ;
?>
</body>