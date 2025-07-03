
import { Header } from '@/views/headers'
import { Searcher } from '@/views/searcher'

export const useSearchStore = defineStore('useCMSTransactionSearchStore', () => {
    const store = Searcher('virtuals/bank-accounts')
    const head = Header('virtuals/bank-accounts', '은행계좌 관리')
    const headers: Record<string, string> = {
        'id' : 'NO.',
        'acct_bank_name': '은행명',
        'acct_num': '계좌번호',
        'acct_name': '예금주명',
        'acct_bank_code': '은행코드',
        'extra_col': '더보기',
    }

    head.sub_headers.value = []
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.flatten(head.headers.value)

    const exporter = async () => {
        const keys = Object.keys(head.flat_headers.value)
        const r = await store.get(store.base_url, { params:store.getAllDataFormat()})
        let datas = r.data.content;
        for (let i = 0; i < datas.length; i++) {
            datas[i] = head.sortAndFilterByHeader(datas[i], keys)
        }
        head.exportToExcel(datas)
    }

    const metas = ref([])
    
    return {
        store,
        head,
        exporter,
        metas,
    }
})
