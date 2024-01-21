<?php

namespace App\Http\Controllers\Log\DifferenceSettlement;
use Carbon\Carbon;

interface DifferenceSettlementInterface
{
    public function request(Carbon $date, $brand_business_num, $trans);
    public function response(Carbon $date);
}
