<?php
namespace App\Http\Traits\Log\DifferenceSettlement;

trait FileRWTrait
{    
    private function setNtypeField($string, $length)
    {
        return sprintf("%0".$length."s", $string);
    }

    private function setAtypeField($string, $length)
    {    
        return sprintf("%-".$length."s", $string);
    }
    
    private function getNtypeField($data, $s_idx, $length)
    {
        return (int)substr($data, $s_idx, $length);
    }

    private function getAtypeField($data, $s_idx, $length)
    {
        return ltrim(substr($data, $s_idx, $length));
    }

}
