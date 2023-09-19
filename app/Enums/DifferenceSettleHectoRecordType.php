<?php
namespace App\Enums;

enum DifferenceSettleHectoRecordType: string {
    case START  = "01";
    case HEADER = "10";
    case DATA   = "11";
    case TOTAL  = "12";
    case END    = "02";
}
