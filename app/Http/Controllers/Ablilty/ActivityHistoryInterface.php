<?php
namespace App\Http\Controllers\Ablilty;

use App\Enums\HistoryType;
use App\Models\Log\ActivityHistory;
use App\Http\Controllers\Ablilty\ActivityHistoryBase;
use Illuminate\Support\Facades\DB;

class ActivityHistoryInterface extends ActivityHistoryBase
{
    public function __construct(ActivityHistory $activity_histories)
    {
        parent::__construct($activity_histories);
    }

    public function login($user, $after_data)
    {
        $this->manyInsert($this->activity_histories, [$this->getLoginFormat($user, $after_data)]);
    }

    public function batchAdd($target, $query, $datas, $title_key, $current, $brand_id)
    {
        return DB::transaction(function () use($target, $query, $datas, $title_key, $current, $brand_id) {
            if($this->manyInsert($query, $datas))
            {
                $ids = $query
                    ->where('brand_id', $brand_id)
                    ->where('created_at', $current)
                    ->pluck('id')
                    ->all();
                if(count($ids))
                {
                    $datas = $this->getAddFormat($target, $datas, $title_key, $ids);
                    $this->manyInsert($this->activity_histories, $datas);
                    return $ids;
                }
                else
                    return [];
            }
        });
    }

    public function add($target, $query, $data, $title_key)
    {
        return DB::transaction(function () use($target, $query, $data, $title_key) {
            $res = $query->create($data);
            if($res)
            {
                $datas = $this->getAddFormat($target, [$data], $title_key, [$res->id]);
                $this->manyInsert($this->activity_histories, $datas);        
            }
            return $res;
        });
    }

    public function update($target, $query, $after_data, $title_key, $parent_table='')
    {
        $before_datas = $this->getBeforeData(array_keys($after_data), $title_key, $query, $parent_table);
        $datas = $this->getUpdateFormat(HistoryType::UPDATE, $target, $title_key, $after_data, $before_datas);

        return DB::transaction(function () use($query, $datas, $after_data) {
            $row = $query->update($after_data);
            if($row)
                $this->manyInsert($this->activity_histories, $datas);
            return $row;
        });
    }

    public function book($target, $query, $after_data, $title_key, $orm, $book_datas)
    {
        $before_datas = $this->getBeforeData(array_keys($after_data), $title_key, $query);
        $datas = $this->getUpdateFormat(HistoryType::BOOK, $target, $title_key, $after_data, $before_datas);

        return DB::transaction(function () use($query, $datas, $after_data, $orm, $book_datas) {
            $result = $this->manyInsert($orm, $book_datas);
            if($result)
                $this->manyInsert($this->activity_histories, $datas);
            return $result;
        });
    }

    public function destory($target, $query, $title_key, $parent_table='', $history_type=HistoryType::DELETE, $is_delete=false)
    {
        $before_datas = $this->getBeforeData([], $title_key, $query, $parent_table);
        $datas = $this->getDestoryFormat($history_type, $target, $title_key, $before_datas);

        return DB::transaction(function () use($query, $datas, $parent_table, $is_delete) {
            if($is_delete)
                $row = $this->delete($query, $parent_table);
            else
                $row = $query->delete();

            if($row)
                $this->manyInsert($this->activity_histories, $datas);
            return $row;
        });
    }
}
