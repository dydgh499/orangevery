import router from '@/router'
import { useRequestStore } from '@/views/request'
import { Settle } from '@/views/types'
import { getUserLevel } from '@axios'
import corp from '@corp'
import { cloneDeep } from 'lodash'

export function SettlementFunctionCollect(store: any) {
    const { post } = useRequestStore()
    const alert = <any>(inject('alert'))
    const snackbar = <any>(inject('snackbar'))
    const errorHandler = <any>(inject('$errorHandler'))

    const getSettleFormat = (item:Settle, is_mcht:boolean) => {
        return {            
            id: item.id,
            acct_name: item.acct_name,
            acct_num: item.acct_num,
            acct_bank_code: item.acct_bank_code,
            acct_bank_name: item.acct_bank_name,
            total_amount: item.amount,            // 총 매출액
            cxl_amount: item.cxl.amount,          // 총 취소액
            appr_amount: item.appr.amount,        // 총 승인액
            deduct_amount: item.deduction.amount, // 추가차감금
            settle_amount: item.settle.amount,    // 정산액
            trx_amount: item.total_trx_amount,    // 총 거래 수수료(매출)
            level: is_mcht ? 10 : item.level,
            settle_fee: is_mcht ? item.settle_fee : 0,
        }
    }
    
    const batchSettle = async(selected:number[], is_mcht: boolean) => {
        if (await alert.value.show('정말 일괄 정산을 하시겠습니까?')) {
            const params = cloneDeep(store.params)
            const datas = [];
            for (let i = 0; i < selected.length; i++) {
                const item:Settle = store.items.find(obj => obj['id'] === selected[i])
                if(item)
                    datas.push(getSettleFormat(item, is_mcht))
            }
            try {
                const page = is_mcht ? 'merchandises' : 'salesforces'
                const r = await post('/api/v1/manager/transactions/settle-histories/'+page+'/batch', Object.assign(params, {datas:datas}))
                console.log(r.status)
                if(r.status === 201) {
                    snackbar.value.show('성공하였습니다.', 'success')
                    store.setChartProcess()
                    store.setTable()    
                }
                else {
                    snackbar.value.show(r.data.message, 'error')
                }
            }
            catch (e: any) {
                snackbar.value.show(e.response.data.message, 'error')
                const r = errorHandler(e)
            }
        }
    }
    
    const settle = async (name:string, item:Settle, is_mcht: boolean) => {
        if (await alert.value.show('정말 ' + name + '님을(를) 정산 하시겠습니까?')) {
            const params = cloneDeep(store.params)
            const p = getSettleFormat(item, is_mcht)
            const page = is_mcht ? 'merchandises' : 'salesforces'
            const r = await post('/api/v1/manager/transactions/settle-histories/' + page, Object.assign(params, p))
            if(r.status == 201) {
                snackbar.value.show('성공하였습니다.', 'success')
                store.setChartProcess()
                store.setTable()    
            }
            else
                snackbar.value.show(r.data.message, 'error')
        }
    }
    
    const getSettleStyle = (parent_key: string) => {
        if (parent_key === 'appr')
            return 'color: rgb(var(--v-theme-primary));'
        else if (parent_key === 'cxl')
            return 'color: red;'
        else if (parent_key === 'settle')
            return 'font-weight: bold;'
        else
            return '' // 기본 스타일 또는 다른 스타일을 지정하고 싶은 경우 여기에 작성
    }
    const isSalesCol = (key: string) => {
        const sales_cols = ['count', 'amount', 'trx_amount', 'settle_fee', 'hold_amount', 'total_trx_amount', 'profit']
        return sales_cols.find(obj => obj === key) ? true : false
    }
    
    const movePartSettle = (id: number, is_mcht: boolean) => {    
        const page = is_mcht ? 'merchandises' : 'salesforces'
        router.push('/transactions/settle/'+page+'/part/' + id + '?s_dt=' + store.params.s_dt + "&e_dt=" + store.params.e_dt)
    }

    const isAbleMchtDepositCollect = (is_mcht: boolean) => {
        return getUserLevel() >= 35 && corp.pv_options.paid.use_realtime_deposit && is_mcht
    }

    const settleCollect = async(name:string, item:Settle) => {
        if (await alert.value.show('정말 '+name+'님에게 정산금을 이체한 후 정산 하시겠습니까?')) {
            const params = cloneDeep(store.params)
            const p = getSettleFormat(item, true)
            const r = await post('/api/v1/manager/transactions/settle-histories/merchandises/settle-collect', Object.assign(params, p))
            if(r.data.result_cd == "0000")
            {
                snackbar.value.show('성공하였습니다.', 'success')
                store.setChartProcess()
                store.setTable()    
            }
            else
                snackbar.value.show(r.data.result_msg, 'error')
        }
    }
    return {
        batchSettle, settle, getSettleStyle, isSalesCol, movePartSettle, isAbleMchtDepositCollect, settleCollect
    }
}

