<?php

namespace App\Http\Controllers\Log\DifferenceSettlement\Component;

use App\Models\Transaction;
use App\Http\Controllers\Log\DifferenceSettlement\Component\ComponentInterface;
use App\Http\Controllers\Log\DifferenceSettlement\Component\ComponentBase;
use App\Http\Traits\Log\DifferenceSettlement\FileRWTrait;
use App\Enums\DifferenceSettleHectoRecordType;
use Carbon\Carbon;

class danal extends ComponentBase implements ComponentInterface
{
    use FileRWTrait;
    public $mcht_cards = [
        '006' => '하나',
        '016' => '국민',
        '018' => '농협',
        '026' => '비씨',
        '027' => '현대',
        '029' => '신한',
        '031' => '삼성',
        '047' => '롯데',
    ];
    public function setDataRecord($trans, $brand_business_num, $mid)
    {
        $brand_business_num = trim(str_replace('-', '', $brand_business_num));
        $data_histories = [];
        $data_records   = '';
        $total_amount   = 0;
        $total_count    = 0;
        for ($i=0; $i < count($trans); $i++)
        {
            $business_num = trim(str_replace('-', '', $trans[$i]->business_num));
            if(strlen($business_num) === 10)
            {
                $appr_type  = $trans[$i]->is_cancel ? "1" : "0";
                $part_cxl_type = $trans[$i]->is_cancel ? $trans[$i]->cxl_seq : '0'; // 부분취소 차수 (승인:0, N회차: N)
                // amount
                $total_amount += $trans[$i]->amount;
                $amount = abs($trans[$i]->amount);
    
                $record_type    = $this->setAtypeField(DifferenceSettleHectoRecordType::DATA->value, 2);
                $appr_type      = $this->setAtypeField($appr_type, 1);
                $trx_dt         = $this->setNtypeField(date('Ymd', strtotime($trans[$i]->is_cancel ? $trans[$i]->cxl_dt : $trans[$i]->trx_dt)), 8);
                $brand_business_num = $this->setAtypeField($brand_business_num, 10);
                $business_num   = $this->setAtypeField($business_num, 10);
                $trx_id         = $this->setAtypeField($trans[$i]->trx_id, 30);
                $ori_trx_id     = $this->setAtypeField($trans[$i]->is_cancel ? $trans[$i]->ori_trx_id : $trans[$i]->trx_id, 30);
                $ord_num        = $this->setAtypeField($trans[$i]->ord_num, 255);
                $amount         = $this->setNtypeField($amount, 15);
                $ori_amount     = $this->setNtypeField($amount, 15);
                $ori_trx_dt     = $this->setNtypeField(date('Ymd', strtotime($trans[$i]->trx_dt)), 8);

                $installment    = $this->setNtypeField($part_cxl_type, 2);
                $add_field      = $this->setAtypeField($trans[$i]->id, 40);
                $filter         = $this->setAtypeField('', 76);
    
                $data_record = 
                    $record_type.$appr_type.$trx_dt.$brand_business_num.$business_num.
                    $trx_id.$ori_trx_id.$ord_num.$amount.$ori_amount.$ori_trx_dt.
                    $add_field.$filter;

                $data_records .= $data_record."\r\n";
                $total_count += 1;
                array_push($data_histories, $this->getSettlementHistoryObejct($trans[$i]->id));
            }
            else
                array_push($data_histories, $this->getSettlementHistoryObejct($trans[$i]->id, '-100'));
        }
        return [$data_records, $total_count, $total_amount, $data_histories];
    }

    public function getDataRecord($contents)
    {
        $records = [];
        $cur_date = date('Y-m-d H:i:s');
        $datas = $this->getDataLines($contents, DifferenceSettleHectoRecordType::DATA->value);

        for ($i=0; $i < count($datas); $i++) 
        {
            $data = $datas[$i];
            $is_cancel  = $this->getNtypeField($data, 2, 1);    //원래는 A타입으로 읽어야함 내부 로직상 변경
            $req_dt     = $this->getNtypeField($data, 3, 8);
            $add_field  = (int)$this->getAtypeField($data, 384, 40);
            $mcht_section_code = $this->getAtypeField($data, 424, 1);
            $supply_amount  = $this->getNtypeField($data, 427, 15);
            $vat_amount     = $this->getNtypeField($data, 442, 15);
            $settle_amount  = $this->getNtypeField($data, 457, 15);
            $settle_dt = $this->getNtypeField($data, 472, 8);
            $settle_result_code = $this->getAtypeField($data, 480, 2);

            if($is_cancel)
            {
                $supply_amount *= -1;
                $vat_amount *= -1;
                $settle_amount *= -1;
            }
            
            $record = $this->getSettlementResponseObejct($add_field, $settle_result_code, $this->getSettleMessage($settle_result_code), $mcht_section_code, $cur_date);
            if($settle_result_code === '00')
            {
                $record = array_merge(
                    $record,
                    $this->getSettlementResponseSuccess($settle_dt, $supply_amount, $vat_amount, $settle_amount)
                );
            }
            $records = $this->setSettlementResponseList($records, $record, 'danal');
        }
        return $records;
    }

    public function getSettleMessage($code)
    {
        $settle_codes = [
            '00' => '정상',
            '01' => '매출정보 오류',
            '02' => '정보 오류 (사업자번호, 가맹점번호 불일치 등)',
            '99' => '기타 오류',
        ];
        return isset($settle_codes[$code]) ? $settle_codes[$code] : '알수없는 코드';
    }

    public function setRegistrationDataRecord($brand, $req_date, $sub_business_regi_infos)
    {
        $getHeader = function($brand, $req_date) {
            return 
                $this->setAtypeField("HD", 2).
                $this->setAtypeField(str_replace('-', '', $brand['business_num']), 10).
                $this->setAtypeField("DANAL", 10).
                $this->setNtypeField($req_date, 8).
                $this->setAtypeField('', 370)
                ."\r\n";
        };
        $getDatas = function($brand, $sub_business_regi_infos) {
            $seq = 1;
            [$data_records, $upload, $datas] = $this->getRegisterTotalFormat();
            $yesterday = Carbon::now()->subDay(1)->format('Ymd');
            
            for ($i=0; $i < count($sub_business_regi_infos); $i++) 
            { 
                $sub_business_regi_info = $sub_business_regi_infos[$i];
                // p_mid가 중복되지 않도록 필터링
                foreach($filtered_mchts as $mcht)
                {
                    $sub_business_regi_info = $sub_business_regi_infos[$i];
                    $business_num       = trim(str_replace('-', '', $sub_business_regi_info->business_num));
                    $phone_num          = trim(str_replace('-', '', $sub_business_regi_info->phone_num));

                    $records = $this->setAtypeField("DD", 2);
                    $records .= $this->setNtypeField($seq, 12);
                    $records .= $this->setNtypeField($sub_business_regi_info->registration_type, 2);
                    $records .= $this->setNtypeField("", 10);    //TODO: P MID 작성되어야함
                    $records .= $this->setNtypeField($sub_business_regi_info->card_company_code, 3);
                    $records .= $this->setNtypeField($business_num, 10);
                    $records .= $this->setAtypeField(iconv('UTF-8', 'EUC-KR//IGNORE', $sub_business_regi_info->sector), 20);
                    $records .= $this->setAtypeField(iconv('UTF-8', 'EUC-KR//IGNORE', $sub_business_regi_info->mcht_name), 40);
                    $records .= $this->setAtypeField(iconv('UTF-8', 'EUC-KR//IGNORE', $sub_business_regi_info->addr), 100);
                    $records .= $this->setAtypeField(iconv('UTF-8', 'EUC-KR//IGNORE', $sub_business_regi_info->nick_name), 40);
                    $records .= $this->setNtypeField($phone_num, 11);
                    $records .= $this->setAtypeField(iconv('UTF-8', 'EUC-KR//IGNORE', $sub_business_regi_info->email), 40);   //이메일 필드
                    $records .= $this->setAtypeField(iconv('UTF-8', 'EUC-KR//IGNORE', $sub_business_regi_info->website_url), 80);   //웹사이트 URL 필드
                    $records .= $this->setNtypeField($yesterday, 8);
                    $records .= $this->setAtypeField('', 22);    
                    $records .= "\r\n";
                    $data_records .= $records;

                    $upload = $this->setRegisterCount($sub_business_regi_info, $upload);
                    $datas[] = $this->getRegisterRequestObject($sub_business_regi_info->id);   
                    $seq++;
                }
            }
            return [$full_records, $upload];
        };
        $getTrailer = function($upload) {
            return 
                $this->setAtypeField("TR", 2).
                $this->setNtypeField($upload['total_count'], 10).
                $this->setNtypeField($upload['new_count'], 10).
                $this->setNtypeField($upload['remove_count'], 10).
                $this->setNtypeField($upload['modify_count'], 10).
                $this->setAtypeField('', 358)
                ."\r\n";
        };
        [$data_records, $upload, $datas] = $getDatas($brand, $sub_business_regi_infos);

        $records  = $getHeader($brand, $req_date);
        $records .= $data_records;
        $records .= $getTrailer($upload);

        return [$records, $datas];
    }

    public function getRegistrationDataRecord($content)
    {
        $pgResultMessage = function($code) {
            $result_codes = [
                '00' => '정상처리',
                '01' => '미정의 등록구분 값',
                '02' => '미정의 카드사코드 값',
                '03' => '필수 값 누락',
                '04' => '온라인 미등록 가맹점번호',
            ];
            return isset($result_codes[$code]) ? $result_codes[$code] : '알수없는 코드';
        };
        $records = $this->getDataLines($content, "RD");
        $datas   = [];

        for ($i=0; $i < count($records); $i++) 
        {
            $record = iconv('EUC-KR', 'UTF-8', $records[$i]);
            $id      = $this->getNtypeField($record, 466, 6);
            $req_dt  = $this->getAtypeField($record, 460, 6);
            $result_code  = $this->getAtypeField($record, 474, 2);

            $data = [
                'id'     => (int)$id,
                'registration_code' => $result_code,
                'registration_msg'  => $pgResultMessage($result_code),
            ];
            if($settle_result_code === '00')
                $data = array_merge($data, $this->getRegisterResponseSuccess("20".$req_dt));

            $datas = $this->setRegisterResponseList($datas, $data, 'secta9ine');
        }
        return $datas;
    }
}
