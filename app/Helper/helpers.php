<?php
    use Illuminate\Support\Facades\Log;
    use App\Http\Controllers\Ablilty\AbnormalConnection;

    function getPGType($pg_type)
    {
        $pgs = ['routeup'];
        return $pgs[$pg_type-1];
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
