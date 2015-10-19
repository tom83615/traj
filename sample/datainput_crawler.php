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