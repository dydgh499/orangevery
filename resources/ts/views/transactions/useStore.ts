import { Header } from '@/views/headers';
import { Searcher } from '@/views/searcher';
import type { Transaction } from '@/views/types';
import corp from '@corp';
import { transactionHeader } from './transacitonsHeader';

export const useSearchStore = defineStore('transSearchStore', () => {
    const snackbar = <any>(inject('snackbar'))
    const store = Searcher('transactions')
    const head  = Header('transactions', '매출 관리')    
    const table = transactionHeader('transactions')
    const headers: Record<string, string> = {
        ...table.headers0,
        ...table.headers1,
        ...table.headers2,
        ...table.headers3,
        ...table.headers4,
        ...table.headers5,
        ...table.headers6,
    }
    const sub_headers: any = []
    head.getSubHeaderCol('거래 정보', table.headers0, sub_headers)
    head.getSubHeaderCol('가맹점 정보', table.headers1, sub_headers)
    head.getSubHeaderCol('PG사 정보', table.headers2, sub_headers)
    head.getSubHeaderCol('수수료 정보', table.headers3, sub_headers)
    head.getSubHeaderCol('개인 정보', table.headers4, sub_headers)
    head.getSubHeaderCol('결제 정보', table.headers5, sub_headers)
    head.getSubHeaderCol('기타 정보', table.headers6, sub_headers)

    head.sub_headers.value = sub_headers
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.flatten(head.headers.value)
    const metas = ref(table.chart)


    const exporter = async () => {      
        let count = metas.value[2]['subtitle'].replaceAll('건', '')
        if(Number(count.replaceAll(',', '')) > 100000) {
            snackbar.value.show('10만개 이상 다운로드 할 수 없습니다. 검색 폭을 줄여주세요.', 'warning')
            return
        }
        const r = await store.get(store.base_url, { params:store.getAllDataFormat()})
        printer(r.data.content)
    }
    
    const printer = (datas: Transaction[]) => {
        const keys = Object.keys(head.flat_headers.value)
        for (let i = 0; i <datas.length; i++) {
            datas[i] = table.dataToExcelFormat(datas[i], store.params.level)
            datas[i] = head.sortAndFilterByHeader(datas[i], keys)
        }
        head.exportToExcel(datas)        
    }

    return {
        store,
        head,
        exporter,
        metas,
        printer,
    }
})

export const defaultItemInfo = () => {
    const path = 'transactions'
    const item = reactive<Transaction>({
        id: 0,
        mcht_id: null,
        sales5_id: null,
        sales5_fee: 0,
        sales5_settle_id: null,
        sales4_id: null,
        sales4_fee: 0,
        sales4_settle_id: null,
        sales3_id: null,
        sales3_fee: 0,
        sales3_settle_id: null,
        sales2_id: null,
        sales2_fee: 0,
        sales2_settle_id: null,
        sales1_id: null,
        sales1_fee: 0,
        sales1_settle_id: null,
        sales0_id: null,
        sales0_fee: 0,
        sales0_settle_id: null,
        custom_id: null,
        mcht_fee: 0,
        hold_fee: 0,
        mcht_settle_id: null,
        mid: '',
        tid: '',
        module_type: null,
        pg_id: null,
        pmod_id: null,
        ps_id: null,
        terminal_id: null,
        ps_fee: 0,
        mcht_settle_type: null,
        mcht_settle_fee: 0,
        trx_dt: null,
        trx_tm: null,
        cxl_dt: null,
        cxl_tm: null,
        is_cancel: 0,
        amount: 0,
        ord_num: '',
        trx_id: '',
        ori_trx_id: '',
        card_num: '',
        installment: null,
        issuer: '',
        acquirer: '',
        appr_num: '',
        buyer_name: '',
        buyer_phone: '',
        item_name: '',
        dev_settle_type: corp.dev_settle_type,
        dev_realtime_fee: 0,
        dev_fee: (corp.dev_fee * 100).toFixed(3),
        mcht_settle_amount: 0,
        pg_settle_type: 1
    })  
    return {
        path, item
    }
}
