import { getUserLevel } from '@/plugins/axios'
import { Header } from '@/views/headers'
import { Searcher } from '@/views/searcher'

export const useSearchStore = defineStore('salesFeeHistorySearchStore', () => {
    const store = Searcher('salesforces/fee-change-histories')
    const head  = Header('salesforces/fee-change-histories', '영업라인 수수료율 변경이력')

    const headers: Record<string, string> = {
        'id' : 'NO.',
        'mcht_name' : '가맹점 상호',
        'created_at' : '생성시간',
        'apply_dt': '적용예정일',
        'change_status' : '변경상태',
        'level': '등급',
        'bf_sales_name' : '영업라인 상호',
        'bf_trx_fee' : '수수료',
        'aft_sales_name' : '영업라인 상호',
        'aft_trx_fee' : '수수료',
    }
    head.sub_headers.value = [
        head.getSubHeaderFormat('적용정보', 'id', 'level', 'string', 6),
        head.getSubHeaderFormat('이전 값', 'bf_sales_name', 'bf_trx_fee', 'string', 2),
        head.getSubHeaderFormat('변경 값', 'aft_sales_name', 'aft_trx_fee', 'string', 2),
    ]
    if(getUserLevel() >= 35) {
        headers['remove'] = '삭제'
        head.sub_headers.value.push(head.getSubHeaderFormat('', 'remove', 'remove', 'string', 1))
    }
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
    return {
        store,
        head,
        exporter,
    }
})
