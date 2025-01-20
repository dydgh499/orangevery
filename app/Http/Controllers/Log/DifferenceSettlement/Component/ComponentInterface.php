<?php

namespace App\Http\Controllers\Log\DifferenceSettlement\Component;

interface ComponentInterface
{
    public function setDataRecord($trans, $brand_business_num, $mid);
    public function getDataRecord($contents);
    public function setRegistrationDataRecord($brand, $req_date, $sub_business_regi_infos);
    public function getRegistrationDataRecord($content);
}
