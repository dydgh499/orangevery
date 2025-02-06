import { getUserLevel } from "@/plugins/axios";
import corp from "@/plugins/corp";
import { SALES_LEVEL_SIZE, useSalesFilterStore } from "../salesforces/useStore";
import { useStore } from "../services/pay-gateways/useStore";


export const useFeeCalculatorStore = defineStore('useFeeCalculatorStore', () => {
    const { hintSalesSettleFee } = useSalesFilterStore()
    const { pss } = useStore()
    
    const getBrandSettleFee = (mcht: any, sales_settle_info: any) => {
        if(mcht.payment_modules.length)
        {
            for(let i=0; i< mcht.payment_modules.length; i++)
            {            
                let ps_fee = Number(pss.find(ps => ps.id === mcht.payment_modules[0].ps_id)?.trx_fee)
                return {
                    ps_fee: ps_fee,
                    settle_fee: Number((sales_settle_info.sales_root_fee - ps_fee).toFixed(5))
                }
            }
        }
        return {
            ps_fee: 0,
            settle_fee: 0,
        }
    }

    const getSalesSettleInfo = (mcht: any) => {
        const sales_settle_fees = []
        const sales_fees = []
        for(let i = SALES_LEVEL_SIZE - 1; i>=0; i--)
        {
            if(mcht['sales'+i+'_id']) {
                if(corp.pv_options.paid.fee_input_mode) {
                    sales_settle_fees.push(Number(mcht[`sales${i}_fee`]))
                }
                else {
                    hintSalesSettleFee(mcht, i)
                    sales_settle_fees.push(Number(mcht[`sales${i}_settlement_fee`]))
                }
                sales_fees.push(Number(mcht[`sales${i}_fee`]))
            }
        }
        return {
            sales_total_fee: Number(sales_settle_fees.reduce((ac, cr_val) => ac + cr_val, 0).toFixed(5)),
            sales_root_fee: sales_fees.length ? sales_fees[0] : Number(mcht.trx_fee.toFixed(5))
        }
    }

    const settleFeeValidate = (mcht: any) => {
        if(getUserLevel() >= 35) {
            const sales_settle_info = getSalesSettleInfo(mcht)
            const brand_settle_info = getBrandSettleFee(mcht, sales_settle_info)
            if(corp.pv_options.paid.fee_input_mode)
                return Number((sales_settle_info.sales_total_fee + brand_settle_info.settle_fee).toFixed(5)) === Number(mcht.trx_fee.toFixed(5))
                ? true : false
            else
                return Number((sales_settle_info.sales_total_fee + brand_settle_info.settle_fee + brand_settle_info.ps_fee).toFixed(5)) === Number(mcht.trx_fee.toFixed(5))
                    ? true : false
        }
        else
            return true
    }

    return {
        getBrandSettleFee,
        getSalesSettleInfo,
        settleFeeValidate,
    }
});
