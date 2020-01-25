<?php

namespace App\Helpers;

class hoursConverter
{
    function transform($totaluse) {
        $hours = floor($totaluse / 3600);
        $minutes = floor(($totaluse - ($hours * 3600)) / 60);
        $seconds = $totaluse - ($hours * 3600) - ($minutes * 60);


        if(($hours == 0) && ($minutes == 0)){
            return $seconds . " segs";
        }else if($hours == 0){
            return $minutes . " mins";
        }else{
            return $hours . " h y " . $minutes . " mins";
        }
    }
}

