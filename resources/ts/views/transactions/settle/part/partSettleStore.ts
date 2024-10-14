import { useDynamicTabStore } from "@/@core/utils/dynamic_tab"
import corp from "@/plugins/corp"
import { useRequestStore } from "@/views/request"
import { cloneDeep } from "lodash"

export const partSettleStore = (store: any, selected: any, dialog: any, table: any, head: any, user_type: string) => {
    const route = useRoute()
    const user = ref(<any>({}))
    const settle = ref({
        'total_amount': 0,
        'cxl_amount': 0,
        'cxl_count': 0,
        'appr_amount': 0,
        'appr_count': 0,
        'settle_amount': 0,
        'trx_amount': 0,
        'settle_fee': 0,
    })
    const { get, post } = useRequestStore()
    const { titleUpdate } = useDynamicTabStore()
    const tab_name = user_type === 'merchandises' ? '가맹점' : '영업점'
    const snackbar = <any>(inject('snackbar'))

    const init = async () => {
        store.params.dev_use = corp.pv_options.auth.levels.dev_use
        store.params.id = route.params.id
        store.params.level = Number(route.query.level)

        if(Number(route.query.level) === 10) {
            store.params.use_realtime_deposit = 0
            store.params.use_collect_withdraw = route.query.use_collect_withdraw    
        }
        else 
            store.params.is_base_trx = 1
        
        const res = await get(`/api/v1/manager/${user_type}/${store.params.id}`)
        user.value = res.data
        titleUpdate(user.value.id, `${tab_name} 정산 관리`, user_type === 'merchandises' ? user.value.mcht_name : user.value.sales_name)
    }

    const getPartSettleFormat = () => {
        const params = Object.assign(cloneDeep(store.params), settle.value)
        params.acct_name = user.value.acct_name
        params.acct_num = user.value.acct_num
        params.acct_bank_name = user.value.acct_bank_name
        params.acct_bank_code = user.value.acct_bank_code
    
        params.settle_transaction_idxs = selected.value
        params.deduct_amount = 0
        params.comm_settle_amount = 0
        params.under_sales_amount = 0
        params.cancel_deposit_amount = 0
        params.settle_pay_module_idxs = []
        params.cancel_deposit_idxs    = []
        return params
    }
    
    const partSettle = async () => {
        if (await dialog('정말 ' + selected.value.length + '개의 매출을 부분정산하시겠습니까?')) {
            const r = await post(`/api/v1/manager/transactions/settle-histories/${user_type}`, getPartSettleFormat(), true)
            if (r.status == 201) {
                snackbar.value.show('성공하였습니다.', 'success')
                store.setChartProcess()
                store.setTable()
            }
            else
                snackbar.value.show(r.data.message, 'error')
        }
    }
    
    const exporter = async () => {      
        const r = await store.get(store.base_url, { params:store.getAllDataFormat()})
        const datas = r.data.content
        const keys = Object.keys(head.flat_headers)
        for (let i = 0; i <datas.length; i++) {
            datas[i] = table.dataToExcelFormat(datas[i], store.params.level)
            datas[i] = head.sortAndFilterByHeader(datas[i], keys)
        }
        head.exportToExcel(datas)        
    }

    watchEffect(() => {
        const _settle = {
            'appr_amount': 0,
            'appr_count': 0,
            'cxl_amount': 0,
            'cxl_count': 0,
            'total_amount': 0,
            'settle_amount': 0,
            'trx_amount': 0,
            'settle_fee': 0,
        }
        for (let i = 0; i < selected.value.length; i++) {
            const trans: any = store.getItems.value.find(item => item['id'] == selected.value[i])
            if (trans) {
                if (trans['is_cancel']) {
                    _settle.cxl_amount += trans['amount']
                    _settle.cxl_count++
                }
                else {
                    _settle.appr_amount += trans['amount']
                    _settle.appr_count++
                }

                _settle.total_amount += trans['amount']
                _settle.settle_amount += trans['profit']
                _settle.trx_amount += trans['trx_amount']
                _settle.settle_fee += trans['mcht_settle_fee']
            }

        }
        settle.value = _settle
    })

    init()

    return {
        settle,
        partSettle,
        exporter,
    }
}
