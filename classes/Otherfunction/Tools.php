<?php
namespace Otherfunction;

class Tools
{
    public function Forminputcheck($forminput)
    {
        $forminput=stripslashes($forminput);
        $forminput=strip_tags($forminput);
        $forminput=trim($forminput);
        return $forminput;

    }

    public function checkCoordinate($value)
    {
        if (is_numeric($value)) {
            if ($value>=1 && $value<=100) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function trimSpeed($speed)
    {
        if (is_numeric($speed)) {
            if ($speed>0 && $speed<=100) {
                return $speed;
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }

    public function resourcecheck($value, $resource) 
    {
        if (is_numeric($resource)) {
            $resource=(double) $resource;
            if ($resource>0) {
                if ($resource>$value) {
                    return $value;
                } else {
                    return $resource;
                }
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }
    
    public function calculateDistance($lat1, $long1, $lat2, $long2)
    {
        if ($long1>$long2) {
            $dlong=$long1-$long2;
        } else {
            $dlong=$long2-$long1;
        }
        if ($dlong>50) {
            $dlong=100-$dlong;
        }
        $return=round(sqrt(pow($dlong, 2) + pow($lat1-$lat2, 2)), 2);
        return $return;
         
    }
    public function calculateCourse($lat1, $long1, $lat2, $long2)
    {
        if ($long1==$long2) {
            // Longitude navigation
            if ($lat1>$lat2) {
                $co=0;
            } elseif ($lat1<$lat2) {
                $co=180;
            } else {
                $co=0;
            }
        } elseif ($lat1==$lat2) {
            // Latitude navigation
            if ($long1>$long2) {
                $co=270;
            } elseif ($long1<$long2) {
                $co=90;
            } else {
                $co=0;
            }
        } else {
            // Mean latitude navigation
            // Calculate Dlat
            $dlat=deg2rad(abs($lat1-$lat2));      
            // Calculate Dlong
            $dlong=deg2rad(abs($long1-$long2));
            $arc=rad2deg(atan($dlong/$dlat));

            if ($lat1>$lat2 && $long1<$long2) {
                $co=$arc;
            }
            if ($lat1<$lat2 && $long1<$long2) {
                $co=180-$arc;
            }
            if ($lat1<$lat2 && $long1>$long2) {
                $co=180+$arc;
            }
            if ($lat1>$lat2 && $long1>$long2) {
                $co=360-$arc;
            }
        }
        return round($co, 2);
    }
    public function calculatePositiontogo($lat1, $long1, $distance, $course)
    {
        if ($course==0 || $course==180) {
            // Latitude Nav.
            $dlat=floor($distance);
            if ($course==0) {
                $lat=$lat1-$dlat;
            }
            if ($course==180) {
                $lat=$lat1+$dlat;
            }
            $long=$long1;
        } elseif ($course==90 || $course==270) {
            // Longitude nav.
            $dlong=floor($distance);
            if ($course==90) {
                $long=$long1+$dlong;
            }
            if ($course==270) {
                $long=$long1-$dlong;
            }
            $lat=$lat1;
        } else {
            // Mean lat. nav.
            $lat1=deg2rad($lat1);
            $long1=deg2rad($long1);
            $distance=deg2rad($distance);
            $course=deg2rad($course);
            $dlat=cos($course)*$distance;
            $dlong=sin($course)*$distance;

            if ($course>0 && $course<90) {
                $lat=floor(rad2deg($lat1-$dlat));
                $long=floor(rad2deg($long1+$dlong));
            }
            if ($course>90 && $course<180) {
                $lat=floor(rad2deg($lat1+$dlat));
                $long=floor(rad2deg($long1+$dlong));
            }
            if ($course>180 && $course<270) {
                $lat=floor(rad2deg($lat1+$dlat));
                $long=floor(rad2deg($long1-$dlong));
            }
            if ($course>270 && $course<360) {
                $lat=floor(rad2deg($lat1-$dlat));
                $long=floor(rad2deg($long1-$dlong));
            }
        }
        $return= array($lat, $long);
        return $return;
    }

    public function calculateEta($speed, $distance)
    {
        $rtime=round((($distance/$speed)*3600)+time(), 0);
        return $rtime;
    }

    public function emptyValuecheck($value)
    {
        if (empty($value)) {
            return 0;
        } else {
            if (is_numeric($value)) {
                return $value;
            } else {
                return 0;
            }
        }
    }

}


?>