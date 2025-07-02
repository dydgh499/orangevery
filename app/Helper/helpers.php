<?php
    use Illuminate\Support\Facades\Log;
    use App\Http\Controllers\Ablilty\Ablilty;
    use App\Http\Controllers\Ablilty\AbnormalConnection;

    function getPGType($pg_type)
    {
        $pgs = ['routeup'];
        return $pgs[$pg_type-1];
    }

    function globalAuthFilter($query, $request, $parent_table='')
    {
        $table = $parent_table !== "" ? $parent_table."." : "";
        if(Ablilty::isOperator($request))
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
        if($request->module_type !== null)
            $query = $query->where($table.'module_type', $request->module_type);
        return $query;
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
