<?php

namespace App\Http\Controllers\Log\DifferenceSettlement\Manager;

use App\Models\Transaction;
use App\Http\Controllers\Log\DifferenceSettlement\Manager\DifferenceSettlementInterface;
use App\Http\Controllers\Log\DifferenceSettlement\Manager\DifferenceSettlementBase;
use App\Http\Traits\Log\DifferenceSettlement\FileRWTrait;
use App\Enums\DifferenceSettleHectoRecordType;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ksnet extends DifferenceSettlementBase implements DifferenceSettlementInterface
{
    use FileRWTrait;
    public $mcht_cards = [
        '06' => '국민',
        '16' => '농협',
        '11' => '비씨',
        '04' => '현대',
        '14' => '신한',
        '01' => '하나',
        '12' => '삼성',
        '03' => '롯데',
        '44' => '우리',
    ];
    private $host;
    private $date;
    CONST PORT           = 9800;
    CONST ENC_KEY        = '';
    CONST CLASSIFICATION = "PGTMS";

    public function __construct()
    {
        $this->host     = env('APP_ENV') === 'local' ? '210.181.28.134' : '210.181.28.137';
        $this->date     = date('Ymd');
    }

    // ksnet jar 모듈 호출
    public function execKSNetShell($save_path, $mid, $type)
    {
        $script_path    = "/home/weroute/different-settlement-modules/ksnet-$type.jar";
        $file_path      = base_path()."/storage/app/public".$save_path;
        // $params에 절때 지정되지 않은 외부 입력값이 들어가면 안됨
        $params         = [
            escapeshellarg($this->host), escapeshellarg(self::PORT),
            escapeshellarg($file_path), escapeshellarg(self::CLASSIFICATION),
            escapeshellarg($mid), escapeshellarg($this->date), 
            escapeshellarg(self::ENC_KEY),
        ];
        // https://ko.linux-console.net/?p=21394
        $shell  = "/opt/jdk-18/bin/java -jar $script_path ".implode(' ', $params);
        $shell  = escapeshellcmd($shell);
        $output = shell_exec($shell);
        return ['output' => $output, 'save_path' => $save_path];
    }

    public function moduleUpload($save_path, $mid, $full_record)
    {
        $log_base   = "ksnet\t main \t"."difference-settlement-request";
        // MID 입력값 검증
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $mid) || strlen($mid) !== 10)
            error(['Invalid MID'=>$mid], "$log_base (X)");
        else
        {
            Storage::disk('public')->makeDirectory('ksnet');
            if(Storage::disk('public')->put($save_path, $full_record))
            {
                $logs = $this->execKSNetShell($save_path, $mid, 'upload');
                if(strpos($logs['output'], 'FILE UPLOAD SUCCESS') !== false)
                {
                    $logs['line-count'] = count(explode("\n", $full_record));
                    Storage::disk('public')->delete($save_path);
                    logging($logs, "$log_base (O)");
                    return true;
                }
                else
                {
                    [$code, $message] = $this->getModuleResultMessage($logs['output']);
                    error($logs, "$log_base $message (X)"); 
                }
            }
            else
                error(['save_path'=>$save_path], "$log_base (X)");
        }
        return false;
    }

    public function moduleDownload($save_path, $rep_mid)
    {
        $log_base = "ksnet\t main \t"."difference-settlement-response";        
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $rep_mid)) 
            error(['Invalid MID'=>$rep_mid], "$log_base (X)");
        else
        {
            $logs = $this->execKSNetShell($save_path, $rep_mid, 'download');
            if(strpos($logs['output'], 'FILE DOWNLOAD SUCCESS') !== false)
            {
                logging($logs, "$log_base (O)");
                $contents = Storage::disk('public')->get($save_path);
                Storage::disk('public')->delete($save_path);
                return $this->getDataRecord($contents);
            }
            else
            {
                [$code, $message] = $this->getModuleResultMessage($logs['output']);
                error($logs, "$log_base $message (X)"); 
            }
        }
        return [];
    }
    
    public function getModuleResultMessage($output)
    {
        $is_matched = preg_match("/오류코드 :\[(\d+)\]/", $output, $matches);
        if($is_matched === 1)
        {
            $codes = [
                '0000' => '정상',
                '0100' => '서버측 파일 생성 실패',
                '0101' => '서버측 파일 쓰기 실패',
                '0102' => '송신측과 수신측의 송/수신 개수가 맞지 않음',
                '0103' => '실제 파일이 없음',
                '0104' => '해당 파일을 생성한 적이 없음',
                '0105' => '서버측 DB에 쓰기 실패',
                '0106' => '서버측 파일 읽기 실패',
                '0107' => '매입 요청일자가 오늘 일자보다 작음',
                '0108' => '등록되지 않은 상점 아이디 송신',
                '0110' => 'DB ERROR',
                '0502' => '인증오류 (인증 key값 등 확인 필요)',
                '0999' => '기타오류',
                '9999' => '기타오류',
            ];
            if(isset($codes[$matches[1]]))
                return [$matches[1], $codes[$matches[1]]];
        }
        else
            return [-1, '알수없는 코드'];
    }

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
                $amount = abs($trans[$i]->amount);
                if($trans[$i]->is_cancel)
                {
                    $trx_dt = date('Ymd', strtotime($trans[$i]->cxl_dt));
                    $record_classification = $trans[$i]->cxl_seq ? '3' : '1';
                    $cxl_seq = $trans[$i]->cxl_seq ? $trans[$i]->cxl_seq : "0";
                }
                else
                {
                    $trx_dt = date('Ymd', strtotime($trans[$i]->trx_dt));
                    $record_classification = '0';
                    $cxl_seq = "0";
                }
                $total_amount += $trans[$i]->amount;

                $data_record = 
                    $this->setAtypeField("D", 1).",".
                    $this->setAtypeField("PG", 3).",".
                    $this->setAtypeField($mid, 10).",".
                    $this->setAtypeField($record_classification, 1).",".
                    $this->setAtypeField($trx_dt, 8).",".
                    $this->setAtypeField($brand_business_num, 10).",".
                    $this->setAtypeField($business_num, 10).",".
                    $this->setAtypeField($trans[$i]->trx_id, 30).",".
                    $this->setAtypeField($cxl_seq, 2).",".
                    $this->setAtypeField($amount, 15).",".
                    $this->setAtypeField($amount, 15).",".
                    $this->setAtypeField($trans[$i]->ord_num, 50).",".
                    $this->setAtypeField($trans[$i]->id, 50);

                $data_records .= $data_record."\n";
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
        $lines = explode("\n", $contents);
        $datas = array_values(array_filter($lines, function($line) {
            return substr($line, 0, 1) === "R";
        }));
        for ($i=0; $i < count($datas); $i++) 
        {
            $data = explode(',', $datas[$i]);

            $is_cancel  = $data[3];
            $trx_id     = $data[12];
            $settle_result_code = $data[13];
            $mcht_section_code  = $data[14];
            $settle_amount  = (int)$data[16];
            $settle_dt = $data[17];

            $supply_amount = round($settle_amount/1.1);
            $vat_amount = $settle_amount - $settle_amount;

            $record = $this->getSettlementResponseObejct($trx_id, $settle_result_code, $this->getSettleMessage($settle_result_code), $mcht_section_code, $cur_date);
            if($settle_result_code === '00')
            {
                $record = array_merge(
                    $record,
                    $this->getSettlementResponseSuccess($settle_dt, $supply_amount, $vat_amount, $settle_amount)
                );
            }
            $records = $this->setSettlementResponseList($records, $record, 'ksnet');
        }
        return $records;
    }

    public function getSettleMessage($code)
    {
        $settle_codes = [
            '00' => '정상',
            '01' => '카드사별 구분값 오류', // (미존재 또는 불일치)
            '02' => '매출금액 오류',    // (원매출금액과 하위사업자 매출액 SUM의 불일치)
            '03' => '중복접수',         // (기 처리된 내역을 전송)
            '04' => '원매입 반송',      // (원매출 미존재 또는 매출금액 오류 등)
            '05' => '매입취소구분 오류',
            '06' => '매입전송일자 오류',// (원매출과 하위매출의 정상/취소 불일치)
            '07' => '승인일자 오류',
            '08' => '승인번호 오류',    // (원승인번호에 해당하는 매출 미존재)
            '09' => '가맹점번호 오류1', // (가맹점번호가 SPACE이거나 미등록가맹점)
            '10' => '가맹점번호 오류2',
            '11' => '카드번호 오류',
            '12' => '중간하위사업자 오류',  // (전자금융업자 미해당사업자 등)
            '13' => '차액정산 지연접수',
            '14' => '카드사별 구분값 정상건 반송',  // (A,B,C로 구성된 장바구니 거래에서 C의 카드사별 구분값이 오류인 경우 C는 01번 코드로 회신하나, A,B는 카드사별 구분값이 정상임에도 C로 인해 반송되는 것이므로 14번 코드로 구별하여 회신)
            '15' => '재정산대상이 아닌 매출내역이 들어올 경우 반송',
            '16' => '거래구분 오류',
            '17' => '재정산 대상 일자 아님',
            '18' => '2차 서브몰 신규사업자 재정산 대상 아님',
            '19' => '원매출이 정상처리되지 않음',
            '20' => '최종하위사업자가 일반사업자',
            '21' => 'BF116의 서브몰사업자번호와 원매출의 서브몰사업자가 다른 경우',
            'XX' => '등록실패', //  (거래내역에서 해당건 조회 실패)
            'CC' => '승인취소건',
            '99' => '기타',
        ];
        return isset($settle_codes[$code]) ? $settle_codes[$code] : '알수없는 코드';
    }

    public function registerRequest($brand, $req_date, $mchts, $sub_business_regi_infos)
    {
        return '';
    }

    public function registerResponse($content)
    {
        return '';
    }
}
