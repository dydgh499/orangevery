import router from '@/router'
import { useRequestStore } from '@/views/request'
import { Settle } from '@/views/types'
import { getUserLevel } from '@axios'
import corp from '@corp'
import { cloneDeep } from 'lodash'

export function settlementFunctionCollect(store: any) {
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
            total_amount: item.total.amount,    // 총 매출액
            cxl_amount: item.cxl.amount,        // 총 취소액
            cxl_count: item.cxl.count,
            appr_amount: item.appr.amount,      // 총 승인액
            appr_count: item.appr.count,
            deduct_amount: item.deduction.amount, // 추가차감금
            settle_amount: item.settle.amount,    // 정산액
            trx_amount: item.total.total_trx_amount,    // 총 거래 수수료(매출)
            level: is_mcht ? 10 : item.level,
            settle_fee: is_mcht ? item.total.settle_fee : 0,
            comm_settle_amount: item.terminal.amount,
            under_sales_amount: item.terminal.under_sales_amount,
            cancel_deposit_amount: item.settle.cancel_deposit_amount || 0,
            cancel_deposit_idxs: item.cancel_deposit_idxs,
            settle_transaction_idxs: item.settle_transaction_idxs,
            settle_pay_module_idxs: item.terminal.settle_pay_module_idxs,
        }
    }

    const isSettleHoldMcht = (item: Settle) => {
        if(corp.pv_options.paid.use_settle_hold && item.settle_hold_s_dt != '') {
            const settle_hold_s_dt = new Date(item.settle_hold_s_dt as string)
            const s_dt = new Date(store.params.s_dt as string)
            // 지급보류 시작일이 조회 시작일보다 같거나 클 때
            if(settle_hold_s_dt >= s_dt) {                
                snackbar.value.show('지급보류건이 선택되어 있어 진행할 수 없습니다.', 'error')
                return true
            }
        }
        return false
    }
    
    const batchSettle = async(selected:number[], is_mcht: boolean) => {
        if (await alert.value.show('정말 일괄 정산을 하시겠습니까?')) {
            const params = cloneDeep(store.params)
            const datas = [];
            for (let i = 0; i < selected.length; i++) {
                const item:Settle = store.items.find(obj => obj['id'] === selected[i])
                if(item) {
                    if(isSettleHoldMcht(item))
                        return
                    else {
                        if(item.settle.amount < 0) {
                            snackbar.value.show(`#${item.id} ${is_mcht ? item.mcht_name : item.sales_name}은 정산액이 0원 미만이기 때문에 정산할 수 없습니다.`, 'error')
                            return    
                        }
                        else
                            datas.push(getSettleFormat(item, is_mcht))
                    }
                }
            }
            try {
                const page = is_mcht ? 'merchandises' : 'salesforces'
                const r = await post('/api/v1/manager/transactions/settle-histories/'+page+'/batch', Object.assign(params, {datas:datas}))
                if(r.status === 201) {
                    store.setChartProcess()
                    store.setTable()    
                }
            }
            catch (e: any) {
                snackbar.value.show(e.response.data.message, 'error')
                const r = errorHandler(e)
            }
        }
    }
    
    const settle = async (name:string, item:Settle, is_mcht: boolean) => {
        if(isSettleHoldMcht(item))
            return
        if(item.settle.amount < 0) {
            snackbar.value.show(`#${item.id} ${item.mcht_name}은 정산액이 0원 미만이기 때문에 정산할 수 없습니다.`, 'error')
            return
        }
        if (await alert.value.show('정말 ' + name + '님을(를) 정산 하시겠습니까?')) {
            const params = cloneDeep(store.params)
            const p = getSettleFormat(item, is_mcht)
            const page = is_mcht ? 'merchandises' : 'salesforces'
            const r = await post('/api/v1/manager/transactions/settle-histories/' + page, Object.assign(params, p))
            if(r.status == 201) {
                store.setChartProcess()
                store.setTable()    
            }
        }
    }
    
    const getSettleStyle = (parent_key: string) => {
        if (parent_key === 'appr')
            return 'color: rgb(var(--v-theme-primary));'
        else if (parent_key === 'cxl')
            return 'color: red;'
        else if (parent_key === 'total')
            return 'font-weight: bold;'
        else if (parent_key === 'settle')
            return 'font-weight: bold;'
        else
            return '' // 기본 스타일 또는 다른 스타일을 지정하고 싶은 경우 여기에 작성
    }
    const isSalesCol = (key: string) => {
        const sales_cols = ['count', 'amount', 'trx_amount', 'settle_fee', 'hold_amount', 'total_trx_amount', 'profit']
        return sales_cols.find(obj => obj === key) ? true : false
    }
    
    const movePartSettle = (item: Settle, is_mcht: boolean) => {
        const page = is_mcht ? 'merchandises' : 'salesforces'
        let url = '/transactions/settle/'+page+'/part/' + item.id + '?s_dt=' + store.params.s_dt + "&e_dt=" + store.params.e_dt        
        url += (is_mcht ? "&use_collect_withdraw=" + item.use_collect_withdraw : "")
        url += "&level=" + (is_mcht ? 10 : store.params.level)
        router.push(url)
    }

    const isAbleMchtDepositCollect = (use_collect_withdraw: number) => {
        return (getUserLevel() == 10 || getUserLevel() >= 35) && corp.pv_options.paid.use_realtime_deposit && use_collect_withdraw
    }
    
    return {
        batchSettle, settle, getSettleStyle, isSalesCol, movePartSettle, isAbleMchtDepositCollect
    }
}

