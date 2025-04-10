<?php
namespace App\Http\Controllers\Ablilty;

use App\Http\Controllers\Ablilty\Ablilty;
use App\Models\Operator;
use App\Models\Salesforce;
use App\Models\Log\ActivityHistory;
use App\Models\Options\PvOptions;
use App\Http\Controllers\Manager\Service\BrandInfo;
use Illuminate\Support\Facades\DB;

class ActivityHistoryViewer
{   // add column: nick_name, level, target_id
    static public function getSelectCols()
    {
        return [
            'activity_histories.user_id',
            DB::raw("SUM(history_type = 0) AS add_count"),
            DB::raw("SUM(history_type = 1) AS modify_count"),
            DB::raw("SUM(history_type = 3) AS remove_count"),
            DB::raw("SUM(history_type = 4) AS login_count"),
            DB::raw("SUM(history_type = 5) AS book_change_count"),
            DB::raw("SUM(history_type = 6) AS book_remove_count"),
            DB::raw("SUM(history_type = 7) AS history_remove_count"),
            DB::raw("MIN(activity_histories.created_at) AS activity_s_at"),
            DB::raw("MAX(activity_histories.created_at) AS activity_e_at"),
            DB::raw("GROUP_CONCAT(DISTINCT CASE WHEN history_target != '' THEN history_target END ORDER BY history_target SEPARATOR ',') AS history_target")
        ];
    }

    static public function commonSelect($query, $request)
    {
        $search = $request->input('search', '');
        if($request->id)
            $query = $query->where('activity_histories.user_id', $request->id);
        if($request->target_id)
            $query = $query->where('activity_histories.target_id', $request->target_id);
        if($request->history_target)
            $query = $query->where('activity_histories.history_target', $request->history_target);
        if($request->history_type !== null)
            $query = $query->where('activity_histories.history_type', $request->history_type);
        if($search !== '')
        {
            $query = $query->where(function ($query) use ($search) {
                return $query->where('activity_histories.history_title', 'like', "%$search%")
                    ->orWhere('activity_histories.after_history_detail', 'like', "%$search%");
            });
        }
        if($request->has('s_dt'))
        {
            $s_dt = strlen($request->s_dt) === 10 ? date($request->s_dt." 00:00:00") : $request->s_dt;
            $query = $query->where('activity_histories.created_at', '>=', $s_dt);
        }
        if($request->has('e_dt'))
        {
            $e_dt = strlen($request->e_dt) === 10 ? date($request->e_dt." 23:59:59") : $request->e_dt;
            $query = $query->where('activity_histories.created_at', '<=', $e_dt);
        }

        return $query;
    }
    static public function operatorSelect($request)
    {

        $search = $request->input('search', '');
        $query  = Operator::join('activity_histories', 'operators.id', '=', 'activity_histories.user_id')
            ->where('activity_histories.level', '>=', 35)
			->where('activity_histories.brand_id', $request->user()->brand_id);

        if($search !== '')
            $query = $query->where('operators.nick_name', 'like', "%$search%");

        return self::commonSelect($query, $request);
    }

    static public function salesforceSelect($request)
    {
        $search = $request->input('search', '');
        $query  = Salesforce::join('activity_histories', 'salesforces.id', '=', 'activity_histories.user_id')
            ->where('activity_histories.level', '>=', 13)
            ->where('activity_histories.level', '<=', 30)
            ->where('activity_histories.brand_id', $request->user()->brand_id);

        if($search !== '')
            $query = $query->where('salesforces.sales_name', 'like', "%$search%");
        return self::commonSelect($query, $request);
    }

    static public function getDetailSelect($request)
    {
        $activity_cols = [
            'activity_histories.id',
            'activity_histories.history_type',
            'activity_histories.history_title',
            'activity_histories.history_target',
            'activity_histories.created_at',
            'activity_histories.after_history_detail',
            'activity_histories.before_history_detail',
            'activity_histories.level',
        ];
        $oper_cols = [
            "operators.nick_name",
        ];
        $sales_cols = [
            DB::raw("salesforces.sales_name as nick_name"),
        ];
        if($request->level)
        {
            if((int)$request->level >= 35)
                $query = self::operatorSelect($request)->orderBy('created_at')->get(array_merge($activity_cols, $oper_cols));
            else
                $query = self::salesforceSelect($request)->orderBy('created_at')->get(array_merge($activity_cols, $sales_cols));
        }
        else
        {
            $oper_query = self::operatorSelect($request)->select(array_merge($activity_cols, $oper_cols));
            $sales_query = self::salesforceSelect($request)->select(array_merge($activity_cols, $sales_cols));
            $query = $oper_query->unionAll($sales_query)->orderBy('created_at')->get();
        }
        return $query;
    }

    static public function getRecentSelect($request)
    {
        $activity_cols = [
            'activity_histories.id',
            'activity_histories.history_type',
            'activity_histories.history_title',
            'activity_histories.history_target',
            'activity_histories.created_at',
            'activity_histories.level',
        ];
        $oper_cols = [
            "operators.nick_name",
            'operators.profile_img',
        ];
        $sales_cols = [
            DB::raw("salesforces.sales_name as nick_name"),
            'salesforces.profile_img',
        ];
        $oper_query = self::operatorSelect($request)->select(array_merge($activity_cols, $oper_cols));
        $sales_query = self::salesforceSelect($request)->select(array_merge($activity_cols, $sales_cols));
        return $oper_query->unionAll($sales_query)->orderBy('created_at', 'desc')->limit(20)->get();
    }

    static private function authVisiable($request, $history_detail)
    {
        $disableImages = function($history_detail) {
            unset($history_detail['contract_img']);
            unset($history_detail['id_img']);
            unset($history_detail['passbook_img']);
            unset($history_detail['bsin_lic_img']);
            return $history_detail;
        };

        if($request->history_target === '영업라인')
        {
            if(Ablilty::isMySalesforce($request, $request->target_id) === false)
                $history_detail = $disableImages($history_detail);
            if(Ablilty::isOperator($request) === false)
            {
                unset($history_detail['dns']);
                unset($history_detail['theme_css']);
                unset($history_detail['logo_img']);
                unset($history_detail['favicon_img']);
                unset($history_detail['og_img']);
                unset($history_detail['login_img']);
                unset($history_detail['og_description']);
                unset($history_detail['name']);
            }
            unset($history_detail['level']);
        }
        else if($request->history_target === '가맹점')
        {
            if(Ablilty::isSalesforce($request))
            {
                $history_detail = $disableImages($history_detail);
                foreach([13,15,17,20,25,30] as $level)
                {
                    if($request->user()->tokenCan($level) === false)
                    {
                        $idx = globalLevelByIndex($level);
                        $key = 'sales'.$idx;
                        unset($history_detail[$key."_id"]);
                        unset($history_detail[$key."_fee"]);
                    }
                }
            }
            if(Ablilty::isOperator($request) === false)
            {
                unset($history_detail['phone_auth_limit_s_tm']);
                unset($history_detail['phone_auth_limit_e_tm']);
                unset($history_detail['phone_auth_limit_count']);
                unset($history_detail['use_saleslip_prov']);
            }
        }
        else if($request->history_target === '결제모듈')
        {
            if(Ablilty::isOperator($request) === false)
            {
                unset($history_detail['api_key']);
                unset($history_detail['sub_key']);
                unset($history_detail['pg_id']);
                unset($history_detail['ps_id']);
                unset($history_detail['fin_id']);
            }
        }
        return $history_detail;
    }

    static public function paidOptionVisiable($request, $history_detail)
    {
        $brand = BrandInfo::getBrandByDNS($_SERVER['HTTP_HOST']);
        $pv_options = new PvOptions(json_encode($brand['pv_options']));
        if(strlen($history_detail))
        {
            $conv_history_detail = json_decode($history_detail, true);
            
            if($pv_options->paid->use_issuer_filter === false)
                unset($conv_history_detail['filter_issuers']);

            if($pv_options->paid->use_regular_card === false)
                unset($conv_history_detail['use_regular_card']);
            if($pv_options->paid->use_collect_withdraw === false)
            {
                unset($conv_history_detail['use_collect_withdraw']);  
                unset($conv_history_detail['collect_withdraw_fee']);
            }
            if($pv_options->paid->use_noti === false)
                unset($conv_history_detail['use_noti']);

            if(isset($conv_history_detail['brand_id']))
                unset($conv_history_detail['brand_id']);

            if($pv_options->paid->use_multiple_hand_pay === false)
                unset($conv_history_detail['use_multiple_hand_pay']);    
            if($pv_options->paid->use_pay_verification_mobile === false)
                unset($conv_history_detail['use_pay_verification_mobile']);

            if($pv_options->paid->use_noti === false)
                unset($conv_history_detail['use_noti']);
            
            if($pv_options->paid->use_pmid === false)
                unset($conv_history_detail['p_mid']);
            if($pv_options->paid->use_specified_limit === false)
            {
                unset($conv_history_detail['phone_auth_limit_s_tm']);
                unset($conv_history_detail['phone_auth_limit_e_tm']);
                unset($conv_history_detail['phone_auth_limit_count']);
                unset($conv_history_detail['single_payment_limit_s_tm']);
                unset($conv_history_detail['single_payment_limit_e_tm']);
                unset($conv_history_detail['specified_time_disable_limit']);
            }

            unset($conv_history_detail['user_pw']);
            $conv_history_detail = self::authVisiable($request, $conv_history_detail);

            $levels = $pv_options->auth->levels;
            $history_detail = [];
            foreach($conv_history_detail as $key => $value)
            {
                if(preg_match('/^sales[0-9]_/', $key))
                {
                    if(strpos($key, '_id') !== false)
                        $key_name = $levels[str_replace('_id', '', $key)."_name"];
                    else if(strpos($key, '_fee') !== false)
                    {
                        $key_name = $levels[str_replace('_fee', '', $key)."_name"]. " 수수료";
                        $conv_history_detail[$key] = round($conv_history_detail[$key], 7);
                    }
                    else if(strpos($key, '_settlement') !== false)
                        $key_name = $levels[str_replace('_settlement', '', $key)."_name"]. " 정산금";
                    else if(strpos($key, '_settle_id') !== false)
                        $key_name = $levels[str_replace('_settle_id', '', $key)."_name"]. " 정산번호";
                    else if(strpos($key, '_settle_amount') !== false)
                        $key_name = $levels[str_replace('_settle_amount', '', $key)."_name"]. " 정산금";
                    else
                        continue;
                    $history_detail[$key_name] = $conv_history_detail[$key];
                }
                else
                    $history_detail[__('validation.attributes.'.$key)] = $conv_history_detail[$key];
            }
            return $history_detail;
        }
        else
            return [];
    }

    static public function userVisiable($request, $data)
    {
        if(Ablilty::isSalesforce($request))
        {
            if($request->user()->level < $data->level)
            {
                $data->level = 0;
                $data->nick_name = '상위';
            }
        }
        if(Ablilty::isMerchandise($request))
        {
            $data->level = 0;
            $data->nick_name = '상위';
        }
        return $data;
    }
}
