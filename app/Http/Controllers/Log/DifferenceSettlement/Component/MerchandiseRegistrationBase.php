<?php
namespace App\Http\Controllers\Log\DifferenceSettlement\Component;

use Carbon\Carbon;

class MerchandiseRegistrationBase
{
    public function getBaseSettleCodes()
    {
        return [
            '-101'  => 'MID 누락',
            '-100'  => '가맹점 사업자번호 오기입',
            '50'    => '업로드 완료',
            '51'    => '재업로드',
        ];
    }

    public function getMchtSectionName($code)
    {
        $mcht_sections = [
            '0' => '영세',
            '1' => '중소1',
            '2' => '중소2',
            '3' => '중소3',
            '4' => '일반',
        ];
        return isset($mcht_sections[$code]) ? $mcht_sections[$code] : '알수없는 코드';        
    }

    public function getDataLines($content, $prefix)
    {
        $lines = explode("\n", $content);
        return array_values(array_filter($lines, function($line) {
            return substr($line, 0, 2) === "DT";
        }));
    }

    public function getRegisterTotalFormat()
    {
        $upload = [
            'new_count' => 0, 
            'remove_count' => 0, 
            'modify_count' => 0,
            'total_count' => 0,
        ];
        return ['', $upload, []];
    }

    public function setRegisterCount($sub_business_regi_info, $upload)
    {
        if($sub_business_regi_info->registration_type === 0)
            $upload['new_count']++;
        else if($sub_business_regi_info->registration_type === 1)
            $upload['remove_count']++;
        else if($sub_business_regi_info->registration_type === 2)
            $upload['modify_count']++;
        $upload['total_count']++;    
        return $upload;
    }

    public function getRegisterRequestObject($id, $registration_code = '50')
    {
        $settle_result_codes = $this->getBaseSettleCodes();
        return [
            'id' => $id,
            'registration_code'   => $registration_code,
            'registration_msg'     => $settle_result_codes[$settle_result_code],
        ];
    }

    public function getRegisterResponseSuccess($registration_dt)
    {
        $record = [
            'registration_code' => '00',
        ];
        try
        {
            $record['registration_dt']  = Carbon::createFromFormat('Ymd', (string)$registration_dt)->format('Y-m-d');
        }
        catch(\Throwable $e)
        {
            $record['registration_dt'] = null;
        }
        return $record;
    }

    public function setRegisterResponseList($datas, $data, $service_name)
    {
        if($data['id'] !== 0)
            $datas[] = $data;
        else
            error(['message' => 'id is empty'], $service_name."\t main \t"."merchandise-registration-response");
        return $datas;
    }
}
