<?php
    //Using the Wind profile power law
    //http://en.wikipedia.org/wiki/Wind_profile_power_law
    function wpp_law($known_windspeed,$known_height,$wanting_height,$alpha)
    {
        $wanting_windspeed = $known_windspeed*pow( ($wanting_height/$known_height) , $alpha);
        return $wanting_windspeed;
    }

    function concentrat($target_distance,$other_distance)
    {
        $sum = 0;
        foreach ($other_distance as $key => $value)
        {
            if($value <= $target_distance)
                $sum++;
        }
        if($sum == 0)
            return 1;
        else
            return 1/$sum;
    }

    function anglecorrect($angle)
    {
        if($angle < 0)
        {
            $angle+=360;
        }
        if($angle >= 360)
        {
            $angle-=360;
        }
        return $angle;
    }

    function windengine($center,$sitedata,$factor,$test = true )
    {
        foreach($sitedata as $key => $value)
        {
            $site[$key] = new Site($value,$sitedata,$factor);
        }
        $target = new Target($center,$sitedata,$factor);
        //initialization

        $selected_site = array();
        //save site who be selected
        foreach ($target->distance as $name => $value)
        {
            if( $value == 0 )
            {
                if($test)
                {
                    continue;
                }
                else
                {
                    $selected_site = array_merge($selected_site, array($name) );
                    $select_error = 0;
                    $site[$name]->weighting = 1;
                    break;
                }
            }

            $selected_site = array_merge($selected_site, array($name) );
            //data is sorted ,so first one = $nearest_site
            $nearest_site = $selected_site[0];
            $site[$nearest_site]->set_weighting($nearest_site,$site,$target->distance,$selected_site,$factor);
            if(isset($largest_weighting)) //in the fitst loop $largest_weighting = 0
            {
                $select_error = ( $largest_weighting - $site[$nearest_site]->weighting )/ $largest_weighting;
                if($select_error <= $factor['select_error'])
                {
                    $key = array_search($name, $selected_site);
                    unset($selected_site[$key]);
                    break;
                }
                //stop to select more site , it mean those site be enought to estimate target's wind
            }
            $largest_weighting = $site[$nearest_site]->weighting;
            //reset weighting until error < setting number
        }
        foreach ($selected_site as $key => $value)
        {
            if( count($selected_site) ==1)
                break;
            $site[$value]->set_weighting($value,$site,$target->distance,$selected_site,$factor);
        }
        //calculate weighting

        $target->set_wind($site,$selected_site,$factor);
        //calculate target's wind;

        $return = array(
            'ws' => $target->ws,
            'wd' => $target->wd,
            'num_of_site' => count($site),
            'sel_of_site' => count($selected_site),
            'sel_error' => $select_error,
            'all_site' =>array_keys($site),
            'selected_site' =>$selected_site
            );
        return $return;
    }
?>