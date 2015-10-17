<?php
    function correlation_coefficient($x ,$y)
    {
        $len_x = count($x);
        $len_y = count($y);
        if($len_x != $len_y)
        {
            echo 'error:two array not in same length.';
            break;
        }
        $sum_x = array_sum($x);
        $sum_y = array_sum($y);
        $squsum_x = square_sum($x);
        $squsum_y = square_sum($y);
        $cross_sum = 0;
        foreach ($x as $key => $value)
        {
            $cross_sum += $x[$key] * $y[$key];
        }
        $r = ($len_x*$cross_sum - $sum_x*$sum_y)/
        (sqrt(
            ($len_x*$squsum_x - pow($sum_x,2)) * ($len_y*$squsum_y - pow($sum_y,2))
        ));
        return $r;
    }

    function square_sum($x)
    {
        $sum = 0;
        foreach ($x as $value)
        {
            $sum += pow($value,2);
        }
        return $sum;
    }
?>