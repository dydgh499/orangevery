import { Header } from '@/views/headers'
import { Searcher } from '@/views/searcher'

export const useSearchStore = defineStore('salesFeeHistorySearchStore', () => {
    const store = Searcher('salesforces/fee-change-histories')
    const head  = Header('salesforces/fee-change-histories', '영업점 수수료율 변경이력')

    const headers: Record<string, string> = {
        'id' : 'NO.',
        'mcht_name' : '가맹점 상호',
        'level': '영업점 등급',
        'bf_sales_name' : '이전 영업점 상호',
        'aft_sales_name' : '변경 영업점 상호',
        'bf_trx_fee' : '이전 수수료',
        'aft_trx_fee' : '변경 수수료',
        'change_status' : '변경상태',
        'created_at' : '생성시간',
        'updated_at' : '업데이트시간',
        'extra_col': '더보기',
    }
    head.main_headers.value = [];
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.setFlattenHeaders()

    const exporter = async (type: number) => {
        const keys = Object.keys(headers);
        const r = await store.get(store.getAllDataFormat())
        let datas = r.data.content;
        for (let i = 0; i < datas.length; i++) {

            datas[i] = head.sortAndFilterByHeader(datas[i], keys)
        }
        type == 1 ? head.exportToExcel(datas) : head.exportToPdf(datas)
    }
    return {
        store,
        head,
        exporter,
    }
})
