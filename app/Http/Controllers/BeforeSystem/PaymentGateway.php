<?php

namespace App\Http\Controllers\BeforeSystem;

use App\Http\Traits\StoresTrait;
use App\Http\Traits\BeforeSystem\BeforeSystemTrait;

class PaymentGateway
{
    use StoresTrait, BeforeSystemTrait;

    public $pg_types, $paywell, $payvery, $paywell_to_payvery, $current_time;
    public function __construct()
    {
        $this->pg_types = [
            ["id" => 1, "pg_name" => '페이투스', "rep_name" => '서동균', "company_name" => '(주)페이투스', "business_num" => '810-81-00347', "phone_num" => '02-465-8800', "addr" => '서울특별시 금천구 가산디지털1로 168, C동 7층 701B호(가산동, 우림라이온스밸리)'],
            ["id" => 2, "pg_name" => '케이원피에스', "rep_name" => '강승구', "company_name" => '(주)케이원피에스', "business_num" => '419-88-00046', "phone_num" => '1855-1838', "addr" => '서울특별시 구로구 디지털로33길 27, 5층 513호, 514호(구로동, 삼성IT밸리)'],
            ["id" => 3, "pg_name" => '에이닐', "rep_name" => '이승철', "company_name" => '(주)에이닐에프앤피', "business_num" => '788-87-00950', "phone_num" => '1544-6872', "addr" => '서울 송파구 법원로11길 7 (문정동) 문정현대지식산업센터C동 1404~1406호'],
            ["id" => 4, "pg_name" => '웰컴페이먼츠', "rep_name" => '김기현', "company_name" => '웰컴페이먼츠(주)', "business_num" => '526-87-00842', "phone_num" => '02-838-2001', "addr" => '서울특별시 용산구 한강대로 148 웰컴금융타워 16층'],
            ["id" => 5, "pg_name" => '헥토파이넨셜', "rep_name" => '최종원', "company_name" => '(주)헥토파이낸셜', "business_num" => '101-81-63383', "phone_num" => '1600-5220', "addr" => '서울특별시 강남구 테헤란로34길 6, 9~10층(역삼동, 태광타워)'],
            ["id" => 6, "pg_name" => '루멘페이먼츠', "rep_name" => '김인환', "company_name" => '(주)루멘페이먼츠', "business_num" => '707-81-01787', "phone_num" => '02-1599-1873', "addr" => '서울특별시 동작구 상도로 13 4층 (프라임빌딩)'],
            ["id" => 7, "pg_name" => '페이레터', "rep_name" => '이성우', "company_name" => '페이레터(주)', "business_num" => '114-86-05588', "phone_num" => '1599-7591', "addr" => '서울시 강남구 역삼로 223 (역삼동 733-23, (대사빌딩) 페이레터(주)'],
            ["id" => 8, "pg_name" => '홀빅(페이스타)', "rep_name" => '김병규', "company_name" => '주식회사 홀빅', "business_num" => '136-81-35826', "phone_num" => '1877-5916', "addr" => '서울특별시 송파구 송파대로 167, B동 609호(문정동, 문정역테라타워)'],
            ["id" => 9, "pg_name" => '코페이', "rep_name" => '채수철', "company_name" => '주식회사 코페이', "business_num" => '206-81-90716', "phone_num" => '1644-3475', "addr" => '서울 성동구 성수일로 77 서울숲IT밸리 608-611호'],
            ["id" => 10, "pg_name" => '코리아결제시스템', "rep_name" => '박형석', "company_name" => '(주)코리아결제시스템', "business_num" => '117-81-85188', "phone_num" => '02-6953-6010', "addr" => '서울 강남구 도산대로1길 40 (신사동) 201호'],
            ["id" => 11, "pg_name" => '더페이원', "rep_name" => '이일호', "company_name" => '(주)더페이원', "business_num" => '860-87-00645', "phone_num" => '1670-1915', "addr" => '서울 송파구 송파대로 201 B동 1621~2호 (문정동, 테라타워2)'],
            ["id" => 12, "pg_name" => '이지피쥐', "rep_name" => '김도형', "company_name" => '주식회사 이지피쥐', "business_num" => '635-81-00256', "phone_num" => '02-1522-3434', "addr" => '서울 강남구 도산대로 157 (신사동) 신웅타워2 15층'],
            ["id" => 13, "pg_name" => 'CM페이', "rep_name" => '', "company_name" => '씨엠컴퍼니 주식회사', "business_num" => '', "phone_num" => '', "addr" => ''],
            ["id" => 14, "pg_name" => '키움페이', "rep_name" => '성백진', "company_name" => '(주)다우데이타', "business_num" => '220-81-01733', "phone_num" => '1588-5984', "addr" => '서울시 마포구 독막로 311 재화스퀘어 5층'],
            ["id" => 15, "pg_name" => '위즈페이', "rep_name" => '이용재', "company_name" => '(주)유니윌 위즈페이', "business_num" => '220-85-36623', "phone_num" => '1544-3267', "addr" => '서울 강남구 테헤란로 124, 5층 (역삼동, 삼원타워) (주)유니윌 위즈페이'],
            ["id" => 16, "pg_name" => '네스트페이', "rep_name" => '김찬수', "company_name" => '(주)페이네스트', "business_num" => '139-81-46088', "phone_num" => '02-431-8333', "addr" => '서울특별시 송파구 송파대로 201, 테라타워2 A동 905호 (문정동)'],
            ["id" => 17, "pg_name" => 'E2U', "rep_name" => '이용원', "company_name" => '(주)이투유', "business_num" => '383-87-01545', "phone_num" => '1600-4191', "addr" => '경기도 성남시 수정구 위례광장로 19 아이페리온, 10층 1001호'],
            ["id" => 18, "pg_name" => '애드원', "rep_name" => '', "company_name" => '', "business_num" => '', "phone_num" => '', "addr" => ''],
            ["id" => 19, "pg_name" => '삼인칭', "rep_name" => '', "company_name" => '', "business_num" => '', "phone_num" => '', "addr" => ''],
        ];
        $this->paywell = [];
        $this->payvery = [];
        $this->paywell_to_payvery = [];
        $this->current_time = date('Y-m-d H:i:s');
    }
    protected function getPGType($pg_name) 
    {
        if(strpos($pg_name, '페이투스') !== false)
            return array_filter($this->pg_types, function($item) {return $item['id'] == 1; });
        else if(strpos($pg_name, '케이원피에스') !== false)
            return array_filter($this->pg_types, function($item) {return $item['id'] == 2; });
        else if(strpos($pg_name, '에이닐') !== false || strpos($pg_name, '애드원') !== false)
            return array_filter($this->pg_types, function($item) {return $item['id'] == 3; });
        else if(strpos($pg_name, '웰컴') !== false)
            return array_filter($this->pg_types, function($item) {return $item['id'] == 4; });
        else if(strpos($pg_name, '헥토') !== false)
            return array_filter($this->pg_types, function($item) {return $item['id'] == 5; });
        else if(strpos($pg_name, '루멘') !== false || strpos($pg_name, '루먼') !== false)
            return array_filter($this->pg_types, function($item) {return $item['id'] == 6; });
        else if(strpos($pg_name, '페이레터') !== false)
            return array_filter($this->pg_types, function($item) {return $item['id'] == 7; });
        else if(strpos($pg_name, '홀빅') !== false)
            return array_filter($this->pg_types, function($item) {return $item['id'] == 8; });
        else if(strpos($pg_name, '코페이') !== false)
            return array_filter($pthis->g_types, function($item) {return $item['id'] == 9; });
        else if(strpos($pg_name, '결제시스템') !== false || strpos($pg_name, '결재시스템') !== false)
            return array_filter($this->pg_types, function($item) {return $item['id'] == 10; });
        else if(strpos($pg_name, '더페이원') !== false)
            return array_filter($this->pg_types, function($item) {return $item['id'] == 11; });
        else if(strpos($pg_name, '이지피쥐') !== false)
            return array_filter($this->pg_types, function($item) {return $item['id'] == 12; });
        else if(strpos($pg_name, 'cm페이') !== false || strpos($pg_name, 'CM페이') !== false)
            return array_filter($this->pg_types, function($item) {return $item['id'] == 13; });
        else if(strpos($pg_name, '키움') !== false)
            return array_filter($this->pg_types, function($item) {return $item['id'] == 14; });
        else if(strpos($pg_name, '애드원') !== false || strpos($pg_name, '에드원') !== false)
            return array_filter($this->pg_types, function($item) {return $item['id'] == 18; });
        else if(strpos($pg_name, '겔럭시아') !== false || strpos($pg_name, '삼인칭') !== false)
            return array_filter($this->pg_types, function($item) {return $item['id'] == 19; });
        else
            return [["PK" => 1, "pg_name" => $pg_name, "rep_name" => '', "company_name" => '', "business_num" => '', "phone_num" => '', "addr" => '', 'id'=>1]];
    }

    public function getPaywell($paywell_table, $brand_id, $before_brand_id)
    {
        $items = [];
        $pgs = $paywell_table
                ->where('DNS_PK', $before_brand_id)
                ->orderby('PK', 'DESC')
                ->get();

        foreach($pgs as $pg) {
            $item = array_values($this->getPGType($pg->PG_NM))[0];
            $item['pg_type']  = $item['id'];
            $item['pg_name']  = $pg->PG_NM;
            $item['brand_id'] = $brand_id;
            $item['PK'] = $pg->PK;
            $item['is_delete']  = !$pg->IS_USE;
            $item['created_at'] = $this->current_time;
            $item['updated_at'] = $this->current_time;
            unset($item['id']);
            array_push($items, $item);
        }
        $this->paywell = $items;
    }

    public function setPayvery($payvery_table, $brand_id)
    {
        $items = $this->getPayveryFormat($this->paywell);
        $res = $this->manyInsert($payvery_table, $items);
        if($res)
        {
            $this->payvery = $this->getPayvery($payvery_table, $brand_id, $this->current_time);
            $this->paywell_to_payvery = $this->connect($this->payvery, $this->paywell);
        }
    }
}
