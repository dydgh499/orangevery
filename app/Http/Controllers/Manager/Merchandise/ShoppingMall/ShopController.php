<?php

namespace App\Http\Controllers\Manager\Merchandise\ShoppingMall;

use App\Models\Merchandise\ShoppingMall\ShoppingMall;
use App\Models\Merchandise\PaymentModule;
use App\Models\Transaction;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\Models\EncryptDataTrait;
use App\Http\Traits\StoresTrait;

use App\Http\Requests\Manager\IndexRequest;
use App\Http\Controllers\Ablilty\Ablilty;
use App\Http\Controllers\Ablilty\PayWindowInterface;
use App\Http\Controllers\Ablilty\ShoppingMallWindowInterface;

use App\Http\Controllers\Manager\Salesforce\UnderSalesforce;
use App\Http\Controllers\Manager\Transaction\TransactionFilter;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group Shopping Mall API
 *
 * 쇼핑몰 API입니다.
 */
class ShopController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, EncryptDataTrait;
    protected $merchandises;

    public function __construct()
    {
        $this->imgs = [];
    }
    
    /**
     * 카테고리 목록출력
     *
     */
    public function index(string $shop_window)
    {
        return $this->response(0, ShoppingMallWindowInterface::getShopInfo($shop_window));
    }

    /**
     * 상품 단일조회
     *
     */
    public function show(string $shop_window, int $id)
    {
        return $this->response(0, ShoppingMallWindowInterface::getProductInfo($shop_window, $id));
    }

    
    /**
     * 쇼핑몰 미리보기
     *
     */
    public function shopCode(Request $request, int $id)
    {
        $shop = ShoppingMall::where('mcht_id', $id)->first();
        if($shop)
            return $this->response(0, ['window_code' => $shop->window_code]);
        else
        {
            $shop = ShoppingMallWindowInterface::renew($id);
            $pmods = PaymentModule::where('mcht_id', $id)->with(['payWindows'])->get();
            foreach($pmods as $pmod)
            {
                if($pmod->payWindows === null || count($pmod->payWindows) === 0)
                    PayWindowInterface::renew($pmod->id);
            }
            return $this->response(0, ['window_code' => $shop['window_code']]);
        }
    }

    /**
     * 주문조회
     *
     */
    public function order(IndexRequest $request)
    {
        $cols = [
            'merchandises.mcht_name', 'merchandises.user_name', 
            'merchandises.nick_name', 'merchandises.contact_num',
            'merchandises.addr', 
            'merchandises.resident_num', 'merchandises.business_num', 
            'merchandises.tax_category_type', 'merchandises.is_show_fee',
            'merchandises.use_saleslip_prov',

            'transactions.is_cancel',
            'transactions.pg_id',
            'transactions.ps_id',
            'transactions.ps_fee',
            'transactions.trx_id',
            'transactions.trx_at',
            'transactions.appr_num',
            'transactions.amount',
            'transactions.item_name',
            'transactions.issuer',
            'transactions.card_num',

            'transactions.buyer_name',
            'transactions.buyer_phone',

            'orders.*',
        ];
        $search = $request->input('search', '');
        $query = Transaction::join('orders', 'transactions.id', '=', 'orders.trans_id')
            ->join('merchandises', 'transactions.mcht_id', '=', 'merchandises.id')
            ->where('transactions.is_cancel', 0)
            ->where(function ($query) use ($search) {
                return $query->where('transactions.buyer_phone', 'like', "%$search%")
                    ->orWhere('transactions.item_name', 'like', "%$search%")
                    ->orWhere('transactions.appr_num', 'like', "%$search%");
            });
        $query = TransactionFilter::date($request, $query);
        $data = TransactionFilter::pagenation($request, $query, $cols, 'transactions.trx_at', false);
        foreach($data['content'] as $content)
        {
            $content = UnderSalesforce::setViewableSalesInfos($request, $content);
            $content->append(['resident_num_front', 'resident_num_back']);
            $content->addr = $this->aes256_decode($content->addr);
            $content->detail_addr = $this->aes256_decode($content->detail_addr);
            $content->setHidden(['resident_num']);
        }        
        return $this->response(0, $data);
    }
}
