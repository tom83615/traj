<?php
    include('datainput.php');
    include('class.php');
    include('windengine_lib.php');

    $center = array(
            'date' => $_GET['date'],
            'time' => $_GET['time'],
            'lat' => $_GET['lat'],
            'lon' => $_GET['lon'],
            'alt' => $_GET['alt'],
            'wh' => $_GET['wh']
            );
    $factor = array(
        'earthRadius' => 6371000,//approximate radius of earth in meters (average value)
        'correction_factor' => 0.6,
        'wpp_law_exponent' => 1/7,
        'select_error' => 0.01
        );
    if($center['lat']<21.958069||$center['lat']>26.160469 || $center['lon']<118.312256||$center['lon']>121.792928)
        die('Error:out to limit');
    $sitedata = getsite($center['date'],$center['time']);
    // you can use your function ,it's a example

    //the input data will be like follow
    //$site =  array(
    //    $sitename_1 => array(
    //        'ws' = '', //wind speed
    //        'wd' = '', //wind direc
    //        'lat' = '', //Latitude
    //        'lon' = '', //longitude
    //        'alt' = '', //Altitude
    //        'sph' = '', //Sampling port height
    //        ),
    //    $sitename_2 => array(
    //        'ws' = '',
    //        'wd' = '',
    //        'lat' = '',
    //        'lon' = '',
    //        'alt' = '',
    //        'sph' = '',
    //        ),
    //        .
    //        .
    //        .
    //    );
    //$center =  array(
    //    'date' = '', // ex: 2015/01/01
    //    'time' = '', //  0 ~ 23
    //    'lat' = '', //Latitude
    //    'lon' = '',  //longitude
    //    'alt' = '',  //Altitude
    //    'wh' = '',   //simulation of wind-height
    //        );
    //$factor = array(
    //  'earthRadius' => , //approximate radius of earth in meters (average value)
    //  'correction_factor' => ,//correct length and concentrat in weighting
    //  'wpp_law_exponent' => , //use in wpp_law you can find in windengine_lib
    //  'select_error' => //use in break out to caculate more useless site
    //  );
    if(!isset($_GET['test']))
        $test = false;
    else
        $test = $_GET['test'];
    $data = json_encode( windengine($center,$sitedata,$factor,$test) );
    print_r($data);

?>