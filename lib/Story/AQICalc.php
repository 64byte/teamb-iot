<?php

namespace Story;

class AQICalc
{
    private static function AQIFormulaCalc($concObs, $concMax, $concMin, $aqiMax, $aqiMin)
    {
        return (($concObs - $concMin) / ($concMax - $concMin)) * ($aqiMax - $aqiMin) + $aqiMin;
    }

    public static function AQIPM25($concObs)
    {
        $AQIValue = null;
        if ($concObs >= 0 && $concObs < 12.1) {
            $AQIValue = self::AQIFormulaCalc($concObs, 12.0, 0, 50, 0);
        } else if ($concObs >= 12.1 && $concObs < 35.5) {
            $AQIValue = self::AQIFormulaCalc($concObs, 35.4, 12.1, 100, 51);
        } else if ($concObs >= 35.5 && $concObs < 55.5) {
            $AQIValue = self::AQIFormulaCalc($concObs, 55.4, 35.5, 150, 101);
        } else if ($concObs >= 55.5 && $concObs < 150.5) {
            $AQIValue = self::AQIFormulaCalc($concObs, 150.4, 55.5, 200, 151);
        } else if ($concObs >= 150.5 && $concObs < 250.5) {
            $AQIValue = self::AQIFormulaCalc($concObs, 250.4, 150.5, 300, 200);
        } else if ($concObs >= 250.5 && $concObs < 350.5) {
            $AQIValue = self::AQIFormulaCalc($concObs, 350.4, 250.5, 400, 300);
        } else if ($concObs >= 350.5 && $concObs < 500.5) {
            $AQIValue = self::AQIFormulaCalc($concObs, 500.4, 350.5, 500, 400);
        }

        return $AQIValue;
    }

    public static function AQICO($concObs)
    {
        $AQIValue = null;
        if ($concObs >= 0 && $concObs < 4.5) {
            $AQIValue = self::AQIFormulaCalc($concObs, 4.4, 0, 50, 0);
        } else if ($concObs >= 4.5 && $concObs < 9.5) {
            $AQIValue = self::AQIFormulaCalc($concObs, 9.4, 4.5, 100, 51);
        } else if ($concObs >= 9.5 && $concObs < 12.5) {
            $AQIValue = self::AQIFormulaCalc($concObs, 12.4, 9.5, 150, 101);
        } else if ($concObs >= 12.5 && $concObs < 15.5) {
            $AQIValue = self::AQIFormulaCalc($concObs, 15.4, 12.5, 200, 151);
        } else if ($concObs >= 15.5 && $concObs < 30.5) {
            $AQIValue = self::AQIFormulaCalc($concObs, 30.4, 15.5, 300, 200);
        } else if ($concObs >= 30.5 && $concObs < 40.5) {
            $AQIValue = self::AQIFormulaCalc($concObs, 40.4, 30.5, 400, 300);
        } else if ($concObs >= 40.5 && $concObs < 50.5) {
            $AQIValue = self::AQIFormulaCalc($concObs, 50.4, 40.5, 500, 400);
        }

        return $AQIValue;
    }

    public static function AQISO2($concObs)
    {
        $AQIValue = null;
        if ($concObs >= 0 && $concObs < 36) {
            $AQIValue = self::AQIFormulaCalc($concObs, 35, 0, 50, 0);
        } else if ($concObs >= 36 && $concObs < 76) {
            $AQIValue = self::AQIFormulaCalc($concObs, 75, 36, 100, 51);
        } else if ($concObs >= 76 && $concObs < 186) {
            $AQIValue = self::AQIFormulaCalc($concObs, 185, 76, 150, 101);
        } else if ($concObs >= 186 && $concObs < 305) {
            $AQIValue = self::AQIFormulaCalc($concObs, 304, 186, 200, 151);
        } else if ($concObs >= 305 && $concObs < 605) {
            $AQIValue = self::AQIFormulaCalc($concObs, 604, 305, 300, 200);
        } else if ($concObs >= 605 && $concObs < 805) {
            $AQIValue = self::AQIFormulaCalc($concObs, 804, 605, 400, 300);
        } else if ($concObs >= 805 && $concObs < 1005) {
            $AQIValue = self::AQIFormulaCalc($concObs, 1004, 805, 500, 400);
        }

        return $AQIValue;
    }

    public static function AQINO2($concObs)
    {
        $AQIValue = null;
        if ($concObs >= 0 && $concObs < 54) {
            $AQIValue = self::AQIFormulaCalc($concObs, 53, 0, 50, 0);
        } else if ($concObs >= 54 && $concObs < 101) {
            $AQIValue = self::AQIFormulaCalc($concObs, 100, 54, 100, 51);
        } else if ($concObs >= 101 && $concObs < 361) {
            $AQIValue = self::AQIFormulaCalc($concObs, 360, 101, 150, 101);
        } else if ($concObs >= 361 && $concObs < 650) {
            $AQIValue = self::AQIFormulaCalc($concObs, 649, 361, 200, 151);
        } else if ($concObs >= 650 && $concObs < 1250) {
            $AQIValue = self::AQIFormulaCalc($concObs, 1249, 650, 300, 200);
        } else if ($concObs >= 1250 && $concObs < 1650) {
            $AQIValue = self::AQIFormulaCalc($concObs, 1649, 1250, 400, 300);
        } else if ($concObs >= 1650 && $concObs < 2050) {
            $AQIValue = self::AQIFormulaCalc($concObs, 2049, 1650, 500, 400);
        }

        return $AQIValue;
    }

    public static function AQIO3($concObs)
    {
        $AQIValue = null;
        if ($concObs >= 0 && $concObs < 55) {
            $AQIValue = self::AQIFormulaCalc($concObs, 54, 0, 50, 0);
        } else if ($concObs >= 55 && $concObs < 71) {
            $AQIValue = self::AQIFormulaCalc($concObs, 70, 55, 100, 51);
        } else if ($concObs >= 71 && $concObs < 86) {
            $AQIValue = self::AQIFormulaCalc($concObs, 85, 71, 150, 101);
        } else if ($concObs >= 86 && $concObs < 106) {
            $AQIValue = self::AQIFormulaCalc($concObs, 105, 86, 200, 151);
        } else if ($concObs >= 106 && $concObs < 201) {
            $AQIValue = self::AQIFormulaCalc($concObs, 200, 106, 300, 200);
        }

        return $AQIValue;
    }
}