<?php

namespace App\Http\Controllers\Manager\Salesforce;

use App\Models\Salesforce;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;

use App\Http\Controllers\Manager\Service\BrandInfo;
use App\Http\Requests\Manager\IndexRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group fee table API
 *
 */
class FeeTableController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $salesforces;

    public function __construct(Salesforce $salesforces)
    {
        $this->salesforces = $salesforces;
    }

    public function getMaxSalesIdx($request)
    {
        $brand = BrandInfo::getBrandById($request->user()->brand_id);
        for ($i=5; $i >= 0; $i--) 
        {
            if($brand['pv_options']['auth']['levels']['sales'.$i."_use"])
                return $i;
        }
        return 0;
    }

    public function getLowSalesIdx($request)
    {
        $brand = BrandInfo::getBrandById($request->user()->brand_id);
        for ($i=0; $i < 6; $i++) 
        {
            if($brand['pv_options']['auth']['levels']['sales'.$i."_use"])
                return $i;
        }
        return 0;
    }

    public function flatSalesforce($sales, $child)
    {
        $idx = globalLevelByIndex($child['level']);
        $sales_key = "sales".$idx;

        $sales[$sales_key."_id"] = $child['id'];
        $sales[$sales_key."_fee"] = $child['sales_fee'];
        $sales[$sales_key."_name"] = $child['sales_name'];

        if($child['parent'])
            return $this->flatSalesforce($sales, $child['parent']);
        else
            return $sales;
        
    }

    public function index(Request $request)
    {
        $query  = $this->salesforces
            ->where('brand_id', $request->user()->brand_id)
            ->where('level', $request->level)
            ->with(['parent']);
        $data = $this->getIndexData($request, $query, 'id', ['id', 'level', 'sales_name', 'parent_id', 'sales_fee']);
        $data = json_decode(json_encode($data), true);
        foreach($data['content'] as &$content)
        {
            $sales = $this->flatSalesforce([], $content);
            $content = array_merge($content, $sales);
            unset($content['parent']);
        }
        return $this->response(0, $data);
    }

    public function update(Request $request)
    {
        for ($i=0; $i <6; $i++) 
        { 
            $key = 'sales'.$i;
            $sales_id = $request->input($key."_id", 0);
            $sales_fee = $request->input($key."_fee", 0);
            if($sales_id) 
            {
                $this->salesforces->where('id', $sales_id)->update(['sales_fee' => $sales_fee]);
            }
        }
        return $this->response(1);
    }
}
