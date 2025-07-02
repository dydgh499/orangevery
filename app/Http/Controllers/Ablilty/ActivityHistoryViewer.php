<?php
namespace App\Http\Controllers\Ablilty;

use App\Http\Controllers\Ablilty\Ablilty;
use App\Models\Operator;
use App\Models\Salesforce;
use App\Models\Log\ActivityHistory;
use App\Models\Options\OvOptions;
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
        if($request->level)
        {
            if((int)$request->level >= 35)
                $query = self::operatorSelect($request)->orderBy('created_at')->get(array_merge($activity_cols, $oper_cols));
        }
        else
        {
            $oper_query = self::operatorSelect($request)->select(array_merge($activity_cols, $oper_cols));
            $query = $oper_query->orderBy('created_at')->get();
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
        $oper_query = self::operatorSelect($request)->select(array_merge($activity_cols, $oper_cols));
        return $oper_query->orderBy('created_at', 'desc')->limit(20)->get();
    }

    static private function authVisiable($request, $history_detail)
    {
        return $history_detail;
    }

    static public function paidOptionVisiable($request, $history_detail)
    {
        $brand = BrandInfo::getBrandByDNS($_SERVER['HTTP_HOST']);
        $ov_options = new OvOptions(json_encode($brand['ov_options']));
        if(strlen($history_detail))
        {
            $conv_history_detail = json_decode($history_detail, true);            
            unset($conv_history_detail['user_pw']);
            $conv_history_detail = self::authVisiable($request, $conv_history_detail);

            $history_detail = [];
            foreach($conv_history_detail as $key => $value)
            {                
                $history_detail[__('validation.attributes.'.$key)] = $conv_history_detail[$key];
            }
            return $history_detail;
        }
        else
            return [];
    }

    static public function userVisiable($request, $data)
    {
        return $data;
    }
}
