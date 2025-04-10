<?php
namespace App\Http\Controllers\Ablilty;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\StoresTrait;

use App\Enums\HistoryType;
use App\Http\Controllers\Manager\Service\BrandInfo;

class ActivityHistoryBase
{
    use ManagerTrait, StoresTrait;
    protected $current_time;
    protected $activity_histories;

    protected function __construct($activity_histories)
    {
        $this->current_time = date("Y-m-d H:i:s");
        $this->activity_histories = $activity_histories;
    }

    protected function getLoginFormat($user, $after_data)
    {
        $before_data = [
            'last_login_at' => $user->last_login_at,
            'last_login_ip' => $user->last_login_ip,
        ];
        return array_merge(
            $this->getLogFormat(HistoryType::LOGIN, '', $before_data, $after_data, '', $user->id),
            $this->getUserFormat($user)
        );
    }

    protected function getDestoryFormat($history_type, $target, $title_key, $before_datas)
    {
        $datas = [];
        foreach($before_datas as $before_data)
        {
            $datas[] = array_merge(
                $this->getLogFormat($history_type, $target, $before_data, [], $before_data[$title_key], $before_data['id']),
                $this->getUserFormat(request()->user())
            );
        }
        return $datas;
    }

    protected function getUpdateFormat($history_type, $target, $title_key, $after_data, $before_datas)
    {
        $datas = [];
        foreach($before_datas as $before_data)
        {
            $datas[] = array_merge(
                $this->getLogFormat($history_type, $target, $before_data, $after_data, $before_data[$title_key], $before_data['id']),
                $this->getUserFormat(request()->user())
            );
        }
        return $datas;
    }

    protected function getAddFormat($target, $after_datas, $title_key, $target_ids)
    {
        $idx = 0;
        $datas = [];
        foreach($after_datas as $after_data)
        {
            $datas[] = array_merge(
                $this->getLogFormat(HistoryType::CREATE, $target, [], $after_data, $after_data[$title_key], $target_ids[$idx]),
                $this->getUserFormat(request()->user())
            );
            $idx++;
        }
        return $datas;
    }

    protected function getUserFormat($user)
    {
        return [
            'brand_id'  => $user->brand_id,
            'user_id'   => $user->id,
            'level'     => $user->level,
        ];
    }

    protected function getLogFormat(HistoryType $history_type, $history_target, $before_history_detail, $after_history_detail, $history_title, $target_id)
    {
        $current    = date("Y-m-d H:i:s");
        $brand      = BrandInfo::getBrandByDNS($_SERVER['HTTP_HOST']);
        $before_history_detail = self::s3ImageLinkConvert(json_decode(json_encode($before_history_detail), true));
        return [
            'history_type' => $history_type->value,
            'history_target' => $history_target,
            'before_history_detail' => json_encode($before_history_detail, JSON_UNESCAPED_UNICODE),
            'after_history_detail' => json_encode($after_history_detail, JSON_UNESCAPED_UNICODE),
            'history_title'  => $history_title,
            'target_id' => $target_id,
            'created_at' => $this->current_time,
            'updated_at' => $this->current_time,
        ];
    }

    protected function getBeforeData($keys, $title_key, $query, $parent_table='')
    {
        $datas = [];
        $keys[] = $parent_table ? $parent_table.".".$title_key : $title_key;
        if(isset($update_keys['id']) === false)
            $keys[] = $parent_table ? $parent_table.".id" : 'id';
        return (clone $query)->get($keys)->toArray();
    }

    protected function s3ImageLinkConvert($before_history_detail) 
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

}
