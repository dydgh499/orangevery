import { Header } from '@/views/headers'
import { Searcher } from '@/views/searcher'
import { getUserLevel } from '@axios'
import corp from '@corp'

const getMchtHeaders = (head :any) => {
    const getSalesforceCols = () => {
        const levels = corp.pv_options.auth.levels
        const headers_2:Record<string, string> = {}
        if (levels.sales5_use && getUserLevel() >= 30) {
            headers_2['sales5_name'] = levels.sales5_name
        }
        if (levels.sales4_use && getUserLevel() >= 25) {
            headers_2['sales4_name'] = levels.sales4_name
        }
        if (levels.sales3_use && getUserLevel() >= 20) {
            headers_2['sales3_name'] = levels.sales3_name
        }
        if (levels.sales2_use && getUserLevel() >= 17) {
            headers_2['sales2_name'] = levels.sales2_name
        }
        if (levels.sales1_use && getUserLevel() >= 15) {
            headers_2['sales1_name'] = levels.sales1_name
        }
        if (levels.sales0_use && getUserLevel() >= 13) {
            headers_2['sales0_name'] = levels.sales0_name
        }
        return headers_2
    }

    const getFeeInputCols = () => {
        const levels = corp.pv_options.auth.levels
        const headers_2:Record<string, string> = {}
        if (levels.sales5_use && getUserLevel() >= 30) {
            headers_2['sales5_fee'] = levels.sales5_name
        }
        if (levels.sales4_use && getUserLevel() >= 25) {
            headers_2['sales4_fee'] = levels.sales4_name
        }
        if (levels.sales3_use && getUserLevel() >= 20) {
            headers_2['sales3_fee'] = levels.sales3_name
        }
        if (levels.sales2_use && getUserLevel() >= 17) {
            headers_2['sales2_fee'] = levels.sales2_name
        }
        if (levels.sales1_use && getUserLevel() >= 15) {
            headers_2['sales1_fee'] = levels.sales1_name
        }
        if (levels.sales0_use && getUserLevel() >= 13) {
            headers_2['sales0_fee'] = levels.sales0_name
        }
        return headers_2
    }


    const getEtcCols = () => {
        const headers_5:Record<string, string> = {}
        headers_5['extra_cols'] = '작업하기'
        return headers_5
    }
    const headers1:any = getSalesforceCols()
    const headers2:any = getFeeInputCols()
    const headers4:any = getEtcCols()

    const headers: Record<string, string> = {
        ...headers1,
        ...headers2,
        ...headers4,
    }
    const sub_headers: any = []
    head.getSubHeaderCol('영업점 정보', headers1, sub_headers)
    head.getSubHeaderCol('수수료율 정보', headers2, sub_headers)
    head.getSubHeaderCol('', headers4, sub_headers)
    return [headers, sub_headers]
}

export const useSearchStore = defineStore('feeTableSearchStore', () => {
    const store     = Searcher('salesforces/fee-table')
    const head      = Header('salesforces/fee-table', '수수료 테이블')

    const [headers, sub_headers] = getMchtHeaders(head)
    head.sub_headers.value = sub_headers
    head.headers.value = head.initHeader(headers, {})

    head.flat_headers.value = head.flatten(head.headers.value)
    const metas = ref(<any>([]))
 
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
        metas,
    }
})
