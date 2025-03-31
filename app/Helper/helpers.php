<?php
    use Illuminate\Support\Facades\Log;
    use Illuminate\Support\Facades\Http;
    use Illuminate\Http\Client\ConnectionException;
    use App\Models\Brand;
    use App\Models\Salesforce;
    use Carbon\Carbon;
    use App\Http\Controllers\Log\OperatorHistoryContoller;
    use App\Enums\HistoryType;
    use Illuminate\Support\Facades\DB;
    use App\Models\Options\PvOptions;
    use App\Http\Controllers\Ablilty\Ablilty;
    use App\Http\Controllers\Ablilty\AbnormalConnection;
    use App\Http\Controllers\Manager\Gmid\GmidInformation;

    function getPGType($pg_type)
    {
        $pgs = [
            'paytus', 'koneps', 'aynil', 'welcome', 'hecto', 'lumen',
            'payletter', 'wholebic', 'korpay', 'kppay', 'thepayone', 'ezpg',
            'secta9ine', 'kiwoom', 'wizzpay', 'nestpay', 'e2u','addone',
            'saminching','wgp', 'brightfixC3', 'danal', 'baumpns', 
            'passgo', 'buddypay', 'withpay', 'fixpay', 'galaxiamoneytree',
            'bkwinners', 'welcome1', 'toss', 'payup', 'wayup', 'nicepay', 
            'bonacamp', 'onoffkorea', 'ksnet', 'buddypayWZE', 'seedpayment',
            'weroute', 'ezpgWZE',
        ];
        return $pgs[$pg_type-1];
    }

    function globalAuthFilter($query, $request, $parent_table='')
    {
        $table = $parent_table !== "" ? $parent_table."." : "";
        if(Ablilty::isMerchandise($request))
        {   // 가맹점
            $col = $parent_table == 'merchandises' ? "id" : 'mcht_id';
            $query = $query->where($table.$col,  $request->user()->id);
        }
        else if(Ablilty::isSalesforce($request))
        {   // 영업자
            $idx = globalLevelByIndex($request->user()->level);            
            $query = $query->where($table."sales".$idx."_id",  $request->user()->id);
        }
        else if(Ablilty::isGmid($request))
        {
            $col = $parent_table === 'merchandises' ? "id" : 'mcht_id';
            $query = $query->whereIn($table.$col, GmidInformation::getMchtIds($request->user()->g_mid));
        }
        else if(Ablilty::isOperator($request))
        {   // all

        }
        else
            throw new Exception('알 수 없는 등급1');
        return $query;
    }

    function globalPGFilter($query, $request, $parent_table='')
    {
        $table = $parent_table != "" ? $parent_table."." : "";
        if($request->pg_id)
            $query = $query->where($table.'pg_id', $request->pg_id);
        if($request->ps_id)
            $query = $query->where($table.'ps_id', $request->ps_id);
        if($request->terminal_id)
            $query = $query->where($table.'terminal_id', $request->terminal_id);
        if($request->settle_type !== null)
            $query = $query->where($table.'settle_type', $request->settle_type);
        if($request->mcht_settle_type !== null)
            $query = $query->where($table.'mcht_settle_type', $request->mcht_settle_type);
        if($request->module_type !== null)
            $query = $query->where($table.'module_type', $request->module_type);
        return $query;
    }

    function globalSalesFilter($query, $request, $parent_table='')
    {
        $table = $parent_table != "" ? $parent_table."." : "";
        if($request->sales0_id)
            $query = $query->where($table.'sales0_id', $request->sales0_id);
        if($request->sales1_id)
            $query = $query->where($table.'sales1_id', $request->sales1_id);
        if($request->sales2_id)
            $query = $query->where($table.'sales2_id', $request->sales2_id);
        if($request->sales3_id)
            $query = $query->where($table.'sales3_id', $request->sales3_id);
        if($request->sales4_id)
            $query = $query->where($table.'sales4_id', $request->sales4_id);
        if($request->sales5_id)
            $query = $query->where($table.'sales5_id', $request->sales5_id);
        if($request->custom_id)
            $query = $query->where($table.'custom_id', $request->custom_id);

        return $query;
    }

    function globalIndexByLevel($index)
    {
        switch($index)
        {
            case 0:
                return 13;
            case 1:
                return 15;
            case 2:
                return 17;
            case 3:
                return 20;
            case 4:
                return 25;
            case 5:
                return 30;
            default:
                throw new Exception('알 수 없는 등급2');
                return "UNKNOWUN";
        }
    }

    function globalLevelByIndex($level)
    {
        switch($level)
        {
            case 10:
                return -1;
            case 13:
                return 0;
            case 15:
                return 1;
            case 17:
                return 2;
            case 20:
                return 3;
            case 25:
                return 4;
            case 30:
                return 5;
            case 40:
                return 6;
            case 50;
                return 6;
            default:
                throw new Exception('알 수 없는 등급3');
                return "UNKNOWUN";
        }
    }

    function globalGetUniqueIdsBySalesIds($contents)
    {
        return collect($contents)->flatMap(function ($content) {
            return [
                $content->sales0_id, $content->sales1_id, $content->sales2_id,
                $content->sales3_id, $content->sales4_id, $content->sales5_id,
            ];
        })->unique();
    }

    function globalGetSalesByIds($sales_ids, $is_all=true)
    {
        $globalGetIndexingByCollection = function($salesforces) {
            $sales_index_by_ids = [];
            foreach ($salesforces as $salesforce) {
                $sales_index_by_ids[$salesforce->id] = $salesforce;
            }
            return $sales_index_by_ids;
        };

        $query = Salesforce::whereIn('id', $sales_ids);
        if($is_all == false)
            $query = $query->where('is_delete', false);
        $salesforces = $query->get(['id', 'sales_name', 'settle_tax_type']);
        return $globalGetIndexingByCollection($salesforces);
    }

    function globalMappingSales($sales_index_by_ids, $contents)
    {
        $mappingSalesInfo = function($content, $sales_index_by_ids, $sales_id, $idx) {
            $sales = 'sales'.$idx;
            if(isset($sales_index_by_ids[$sales_id]))
            {
                if(isset($sales_index_by_ids[$sales_id]))
                {
                    $content[$sales] = $sales_index_by_ids[$sales_id];
                    $content[$sales."_name"] = $sales_index_by_ids[$sales_id]->sales_name;    
                }
                else
                    $content[$sales."_name"] = '삭제된 영업자';
            }
            else
            {
                $content[$sales] = null;
                $content[$sales."_name"] = '';
            }
            return $content;
        };

        foreach ($contents as $content) {
            $content = $mappingSalesInfo($content, $sales_index_by_ids, $content->sales0_id, 0);
            $content = $mappingSalesInfo($content, $sales_index_by_ids, $content->sales1_id, 1);
            $content = $mappingSalesInfo($content, $sales_index_by_ids, $content->sales2_id, 2);
            $content = $mappingSalesInfo($content, $sales_index_by_ids, $content->sales3_id, 3);
            $content = $mappingSalesInfo($content, $sales_index_by_ids, $content->sales4_id, 4);
            $content = $mappingSalesInfo($content, $sales_index_by_ids, $content->sales5_id, 5);
        }
        return $contents;
    }

    function logging($data, $msg='test')
    {
        if(isset($data['user_pw']))
            $data['user_pw'] = AbnormalConnection::masking($data['user_pw']);
        if(isset($data['current_pw']))
            $data['current_pw'] = AbnormalConnection::masking($data['current_pw']);
        if(isset($data['yymm']))
            $data['yymm'] = AbnormalConnection::masking($data['yymm'], 1);
        if(isset($data['card_num']))
            $data['card_num'] = AbnormalConnection::masking($data['card_num']);
        if(isset($data['auth_num']))
            $data['auth_num'] = AbnormalConnection::masking($data['auth_num']);
        Log::info($msg, $data);
    }

    function error($data, $msg='test')
    {
        Log::error($msg, $data);
    }

    function critical($msg, $data=[])
    {
        $logs = [
            'ip' => request()->ip(), 
            'url'=> request()->url(),
            'method' => request()->method(),
            'header' => request()->headers->all(),
            'input' => array_merge(request()->all(), $data)
        ];
        Log::critical($msg, $logs);
    }

    function s3ImageLinkConvert($before_history_detail) 
    {
        $keys = [
            'contract_img', 'id_img', 'passbook_img', 'bsin_lic_img', 
            'profile_img', 'favicon_img', 'og_img', 'login_img', 'logo_img',
            'logo_img', 'favicon_img', 'og_img',
        ];
        foreach($keys as $key)
        {
            if(isset($before_history_detail[$key]))
            {
                if(strpos($before_history_detail[$key], 'amazonaws.com') && strpos($before_history_detail[$key], '?X-Amz-Content-Sha256') !== false)
                {
                    $idx = strpos($before_history_detail[$key], '?X-Amz-Content-Sha256');
                    $before_history_detail[$key] = substr($before_history_detail[$key], 0, $idx);    
                }
            }
        }   
        
        return $before_history_detail;
    }

    function operLogging(HistoryType $history_type, $history_target, $before_history_detail, $after_history_detail, $history_title='', $brand_id='', $oper_id='')
    {
        $cond_1 = $history_type == HistoryType::LOGIN;
        $cond_2 = $history_type != HistoryType::LOGIN && Ablilty::isOperator(request());

        if($cond_1 || $cond_2)
        {
            $before_history_detail = s3ImageLinkConvert(json_decode(json_encode($before_history_detail), true));
            $request = request()->merge([
                'history_type' => $history_type->value,
                'history_target' => $history_target,
                'history_title'  => $history_title,
                'before_history_detail' => json_encode($before_history_detail, JSON_UNESCAPED_UNICODE),
                'after_history_detail' => json_encode($after_history_detail, JSON_UNESCAPED_UNICODE),
                'brand_id' => $brand_id,
                'oper_id' => $oper_id,
            ]);
            return OperatorHistoryContoller::logging($request);
        }
    }

    function getTargetInfo($level)
    {
        $level = (int)$level;
        if($level === 10)
        {
            $target_id = 'mcht_id';
            $target_settle_id = 'mcht_settle_id';
            $target_settle_amount = 'mcht_settle_amount';
        }
        else if($level === 11)
        {
            $target_id = '';
            $target_settle_id = 'mcht_settle_id';
            $target_settle_amount = 'mcht_settle_amount';            
        }
        else if($level < 35)
        {
            $idx = globalLevelByIndex($level);
            $target_id = 'sales'.$idx.'_id';
            $target_settle_id = 'sales'.$idx.'_settle_id';
            $target_settle_amount = 'sales'.$idx.'_settle_amount';
        }
        else
        {
            $target_id = 'brand_id';
            $target_settle_id = '';
            $target_settle_amount = ($level === 50 ? 'dev' :'brand')."_settle_amount";
        }
        return [$target_id, $target_settle_id, $target_settle_amount];
    }
