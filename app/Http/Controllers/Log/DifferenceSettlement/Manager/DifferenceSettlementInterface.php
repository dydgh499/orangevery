<?php

namespace App\Http\Controllers\Log\DifferenceSettlement\Manager;

interface DifferenceSettlementInterface
{
    public function setDataRecord($trans, $brand_business_num);
    public function getDataRecord($contents);
}
