<?php
namespace App\Enums;

enum RealtimeCustomFailCode: int {
    case ACCT_NOT_FOUND = -1;
    case ALREADY_CANCEL = -2;
    case ALREADY_WITHDRAW = -3;
    case AMOUNT_TOO_LOW = -4;
    case RESERVATION_CANCEL = -5;
}
