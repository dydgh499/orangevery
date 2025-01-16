<?php
namespace App\Http\Traits\Log\DifferenceSettlement;

trait FileRWTrait
{    
    protected function setNtypeField($string, $length)
    {
        return sprintf("%0".$length."s", $string);
    }

    protected function setAtypeField($string, $length)
    {    
        return sprintf("%-".$length."s", $string);
    }
    
    protected function getNtypeField($data, $s_idx, $length)
    {
        return (int)substr($data, $s_idx, $length);
    }

    protected function getAtypeField($data, $s_idx, $length)
    {
        return ltrim(substr($data, $s_idx, $length));
    }
}
