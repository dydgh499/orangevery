<?php
namespace App\Enums;

enum DevSettleType: int {
    case NOT_APPLY          = 0;
    case HEAD_OFFICE_PROFIT = 1;
    case TOTAL_SALES        = 2;
    case DEDUCT_FEE         = 3;
}
