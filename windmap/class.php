<?php
class location
{
    public $lat;
    public $lon;
    public $ws;
    public $wd;
    public $distance = array();

    public function set_distance($allsite,$factor)
    {
        foreach ($allsite as $key => $value)
        {
            $earthRadius = $factor['earthRadius'];

            $lat1 = deg2rad($this->lat);
            $lat2 = deg2rad($value['lat']);
            $lon1 = deg2rad($this->lon);
            $lon2 = deg2rad($value['lon']);

            //Using the Haversine formula
            //http://en.wikipedia.org/wiki/Haversine_formula
            $diffLatitude = $lat2 - $lat1;
            $diffLongitude  = $lon2 - $lon1;
            $stepOne = pow(sin($diffLatitude/2), 2) + cos($lat1)*cos($lat2)*pow(sin($diffLongitude/2), 2);
            $stepTwo = 2 * asin( sqrt($stepOne) );
            $calculate = $earthRadius * $stepTwo;
            //reference:http://rosettacode.org/wiki/Haversine_formula#PHP
            $this->distance[$key] = $calculate;
        }
    }// set a map to all the other site
}

class Site extends location
{
    public $sph;
    public $weighting;
    function __construct($input,$allsite,$factor)
    {
        $this->ws = $input['ws'];
        $this->wd = $input['wd'];
        $this->lat = $input['lat'];
        $this->lon = $input['lon'];
        $this->sph = $input['sph'];
        $this->set_distance($allsite,$factor);
    }

    public function set_weighting($self,$sitedata,$target_distance,$selected_site,$factor)
    {
        $distance_factor_denominator = 0;
        $concentrat_factor_denominator = 0;
        $correction_factor = $factor['correction_factor']; //(0~1)
        foreach ($selected_site as $key => $value)
        {
            $distance_factor_denominator += 1/pow($target_distance[$value],2);
            $concentrat_factor_denominator += concentrat($target_distance[$value],$sitedata[$value]->distance);
        }
        $distance_factor_numerator = 1/pow($target_distance[$self],2);
        $concentrat_factor_numerator = concentrat($target_distance[$self],$sitedata[$self]->distance);
        $distance_factor = $correction_factor * ($distance_factor_numerator / $distance_factor_denominator);
        $concentrat_factor = (1-$correction_factor) * ($concentrat_factor_numerator / $concentrat_factor_denominator);
        $this->weighting = $distance_factor+$concentrat_factor;
        //***********************************************************************************************//
        // Let distance_factor = Dx = (1/(rx)^2)                                                         //
        // let concentrat_factor = Cx = 1 / (number of other site less distance between target and self )//
        //                                                                                               //
        //                                     Dx                                     Cx                 //
        // weight =  $correction_factor *------------- + (1-$correction_factor) *-------------           //
        //                                D1+D2+D3...                             C1+C2+C3...            //
        //***********************************************************************************************//
    }
}

class Target extends location
{
    public $wh;
    function __construct($input,$allsite,$factor)
    {
        $this->lat = $input['lat'];
        $this->lon = $input['lon'];
        $this->wh = $input['wh'];
        $this->set_distance($allsite,$factor);
        asort($this->distance);
    }
    public function set_wind($sitedata,$selected_site,$factor)
    {
        $this->ws = 0;
        $this->wd = 0;
        foreach ($selected_site as $name)
        {
            $windspeed = wpp_law($sitedata[$name]->ws,$sitedata[$name]->sph,$this->wh,$factor['wpp_law_exponent']);
            $this->ws += $sitedata[$name]->weighting * $windspeed;
            $this->wd += $sitedata[$name]->weighting * $sitedata[$name]->wd;
        }
        anglecorrect( $this->wd );

    }
}
?>