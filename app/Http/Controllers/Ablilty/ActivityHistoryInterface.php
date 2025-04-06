<?php
namespace App\Http\Controllers\Ablilty;
use App\Enums\HistoryType;
use App\Models\Log\OperatorHistory;
use App\Http\Controllers\Manager\Service\BrandInfo;

class ActivityHistoryInterface
{   // add column: nick_name, level, target_id
    // del column: oper_id
    static public function login($target)
    {
        self::logging(HistoryType::LOGIN, $target, [], [], '');
    }

    static public function add($target, $after_datas, $title_key)
    {
        foreach($after_datas as $after_data)
        {
            self::logging(HistoryType::CREATE, $target, [], $after_data, $after_data[$title_key]);
        }
    }

    static public function update($target, $query, $after_data, $title_key)
    {
        $update_keys = array_keys($after_data);
        $update_keys[] = $title_key;
        $before_datas = (clone $query)->get($update_keys)->toArray();
        foreach($before_datas as $before_data)
        {
            self::logging(HistoryType::UPDATE, $target, $before_data, $after_data, $before_data[$title_key]);
        }
    }

    static public function book($target, $query, $after_data, $title_key)
    {
        $update_keys = array_keys($after_data);
        $update_keys[] = $title_key;
        $before_datas = (clone $query)->get($update_keys)->toArray();
        foreach($before_datas as $before_data)
        {
            self::logging(HistoryType::BOOK, $target, $before_data, $after_data, $before_data[$title_key]);
        }
    }

    static public function destory($target, $query, $title_key)
    {
        $before_datas = (clone $query)->get([$title_key])->toArray();
        foreach($before_datas as $before_data)
        {
            self::logging(HistoryType::DELETE, $target, $before_data, [], $before_data[$title_key]);
        }
    }
    static public function bookDestory($target, $query, $title_key)
    {
        $before_datas = (clone $query)->get()->toArray();
        foreach($before_datas as $before_data)
        {
            self::logging(HistoryType::BOOK_DELETE, $target, $before_data, [], $before_data[$title_key]);
        }
    }

    static private function logging(HistoryType $history_type, $history_target, $before_history_detail, $after_history_detail, $history_title)
    {
        $brand = BrandInfo::getBrandByDNS($_SERVER['HTTP_HOST']);
        $before_history_detail = s3ImageLinkConvert(json_decode(json_encode($before_history_detail), true));
        $data = [
            'history_type' => $history_type->value,
            'history_target' => $history_target,
            'before_history_detail' => json_encode($before_history_detail, JSON_UNESCAPED_UNICODE),
            'after_history_detail' => json_encode($after_history_detail, JSON_UNESCAPED_UNICODE),
            'history_title'  => $history_title,
            'brand_id' => request()->user() ? request()->user()->brand_id : $brand['id'],
            'oper_id' => request()->user() ? request()->user()->id : 0,
        ];
        return OperatorHistory::create($data);    
    }
}
