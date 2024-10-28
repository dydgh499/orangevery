<?php

namespace App\Http\Controllers\Manager\BatchUpdater;

use App\Models\Merchandise\PaymentModuleColumnApplyBook;
use App\Models\Merchandise\MerchandiseColumnApplyBook;
use App\Models\Salesforce\SalesforceColumnApplyBook;

use App\Models\Merchandise\PaymentModule;
use App\Models\Merchandise;
use App\Models\Salesforce;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;

use App\Http\Requests\Manager\IndexRequest;
use App\Http\Controllers\Ablilty\AbnormalConnection;
use App\Http\Controllers\Ablilty\Ablilty;
use App\Http\Controllers\Auth\AuthOperatorIP;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class ApplyBookController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;

    public static function getApplyWaitCount($orm, $apply_at)
    {
        return $orm->where('change_status', 0)->where('apply_at', $apply_at)->count();
    }

    public static function getApplyWaitDatas($book_orm, $dest_orm, $dest_key, $apply_at)
    {
        $update_result = [
            'total_count' => 0,
            'datas' => []
        ];
        $apply_books = $book_orm->where('change_status', 0)->where('apply_at', $apply_at)->get();
        $grouped  = $apply_books->groupBy('apply_data');

        foreach ($grouped as $apply_data => $group) 
        {
            $update_data = json_decode($apply_data, true);            
            if (json_last_error() !== JSON_ERROR_NONE) 
            {
                error(['apply_data' => $apply_data], 'apply-book-column-scheduler: json_decode error');
                continue;
            }

            $dest_ids = $group->pluck($dest_key)->toArray();
            $primary_ids = $group->pluck('id')->toArray();

            $book_row = DB::transaction(function () use($dest_orm, $book_orm, $dest_ids, $primary_ids, $update_data) {
                if(count($dest_ids) > 0)
                {
                    $dest_row = $dest_orm->whereIn('id', $dest_ids)->update($update_data);
                    $book_row = $book_orm->whereIn('id', $primary_ids)->update(['change_status' => 1]);
                    return $book_row;    
                }
                else
                    return 0;
            });
            $update_result['datas'] = ['update_data' => $update_data, 'count' => $book_row];
            $update_result['total_count'] += $book_row;
        }
        return $update_result;
    }

    public static function updateApplyWaitDatas($book_orm, $dest_orm, $dest_key, $apply_at)
    {
        if(self::getApplyWaitCount($book_orm, $apply_at))
            return self::getApplyWaitDatas($book_orm, $dest_orm, $dest_key, $apply_at);
        else
            return 0;
    }

    public function __invoke()
    {
        if(Carbon::now()->format('H') === '00')
            sleep(5);

        $apply_at = Carbon::now()->format('Y-m-d H:00:00');
        $sales_result = self::updateApplyWaitDatas(new SalesforceColumnApplyBook, new Salesforce, 'sales_id', $apply_at);
        $mcht_result = self::updateApplyWaitDatas(new MerchandiseColumnApplyBook, new Merchandise, 'mcht_id', $apply_at);
        $pmod_result = self::updateApplyWaitDatas(new PaymentModuleColumnApplyBook, new PaymentModule, 'pmod_id', $apply_at);

        logging([
            'salesforces' => $sales_result,
            'merchandises' => $mcht_result,
            'payment_modules' => $pmod_result,
        ], 'apply-book-column-scheduler');
    }

    public function index(IndexRequest $request)
    {
        $search = $request->input('search', '');
        $dest_type = (int)$request->dest_type;
        if($dest_type === 0)
        {
            $query = SalesforceColumnApplyBook::join('salesforces', 'salesforce_column_apply_books.sales_id', '=', 'salesforces.id')
                ->where(function ($query) use ($search) {
                    return $query->where('salesforces.sales_name', 'like', "%$search%")
                        ->orWhere('salesforce_column_apply_books.apply_data', 'like', "%$search%");
                });
            $parent = 'salesforce_column_apply_books.';
            $cols = [
                $parent."*",
                "salesforces.sales_name as dest_name"
            ];
        }
        else if($dest_type === 1)
        {
            $query = MerchandiseColumnApplyBook::join('merchandises', 'merchandise_column_apply_books.mcht_id', '=', 'merchandises.id')
                ->where(function ($query) use ($search) {
                    return $query->where('merchandises.mcht_name', 'like', "%$search%")
                        ->orWhere('merchandise_column_apply_books.apply_data', 'like', "%$search%");
                });
            $parent = 'merchandise_column_apply_books.';
            $cols = [
                $parent."*",
                "merchandises.mcht_name as dest_name"
            ];
        }
        else if($dest_type === 2)
        {
            $query = PaymentModuleColumnApplyBook::join('payment_modules', 'payment_module_column_apply_books.pmod_id', '=', 'payment_modules.id')
                ->join('merchandises', 'payment_modules.mcht_id', '=', 'merchandises.id')
                ->where(function ($query) use ($search) {
                    return $query->where('merchandises.mcht_name', 'like', "%$search%")
                        ->orWhere('payment_modules.note', 'like', "%$search%")
                        ->orWhere('payment_module_column_apply_books.apply_data', 'like', "%$search%");
                });
            $parent = 'payment_module_column_apply_books.';
            $cols  = [
                $parent."*",
                "merchandises.mcht_name as dest_name",
                "payment_modules.note as pmod_note",
            ];
        }
        else
            return $this->extendResponse(1999, '잘못된 변경대상');

        $query  = $query->where($parent.'brand_id', $request->user()->brand_id);
        if($request->change_status !== null)
            $query = $query->where($parent.'change_status', $request->change_status);

        $data = $this->getIndexData($request, $query, $parent."id", $cols, $parent."apply_at");
        foreach($data['content'] as $content)
        {
            $encode_data = [];
            $apply_datas = json_decode($content->apply_data, true);
            foreach($apply_datas as $key => $value)
            {
                $encode_data[__('validation.attributes.'.$key)] = $apply_datas[$key];
            }
            $content->apply_data = $encode_data;
        }
        return $this->response(0, $data);
    }

    /**
     * 단일삭제
     *
     * @urlParam id integer required 유저 PK
     */
    public function destroy(Request $request, string $dest_type, int $id)
    {
        if($dest_type === 'salesforces')
            $query = new SalesforceColumnApplyBook;
        else if($dest_type === 'merchandises')
            $query = new MerchandiseColumnApplyBook;
        else if($dest_type === 'payment_modules')
            $query = new PaymentModuleColumnApplyBook;
        else
            return $this->extendResponse(1999, '잘못된 변경대상');

        $res = $query->where('id', $id)->delete();
        return $this->response($res ? 1 : 990, ['id'=>$id]);
    }
}
