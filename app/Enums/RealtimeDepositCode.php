<?php
namespace App\Enums;

enum RealtimeDepositCode: int {
    case DIRECT_V1 = 1;
    case DIRECT_V2 = 2;
    case DIRECT_OPERATER  = 3;
    case COLLECT_WITHDRAW = 4;
    case SETTLE_MCHT = 6;
    case SETTLE_SALES = 7;
    //5,8,9
}
