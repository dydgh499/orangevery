import { Header } from '@/views/headers';
import { Searcher } from '@/views/searcher';

export const useSearchStore = defineStore('bonaejaSearchStore', () => {
    const store = Searcher('transactions/settle/merchandises/cancel-deposits')
    const head = Header('transactions/settle/merchandises/cancel-deposits', '입금내역 관리')
    const headers: Record<string, string> = {
        'id': 'NO.',
        'deposit_amount': '입금금액',
        'deposit_history': '입금내역',
        'cxl_dttm': '취소일자',
        'amount': '취소금액',
        'profit': '정산금',
        'total_trx_amount': '수수료',
        'trx_dttm': '원거래일자',
        'appr_num': '승인번호',
        'acquirer': '매입사',
        'installment': '할부',
    }
    head.main_headers.value = []
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.setFlattenHeaders()
    const metas = ref([])

    const exporter = async (type: number) => {
        const keys = Object.keys(headers);
        const r = await store.get(store.base_url, { params:store.getAllDataFormat()})
        let datas = r.data.content;
        for (let i = 0; i < datas.length; i++) {
            datas[i] = head.sortAndFilterByHeader(datas[i], keys)
        }
        type == 1 ? head.exportToExcel(datas) : head.exportToPdf(datas)
    }
    
    onMounted(async () => {

    })
    
    return {
        store,
        head,
        exporter,
        metas,
    }
})
