import { Header } from '@/views/headers';
import { Searcher } from '@/views/searcher';
import { getUserLevel, user_info } from '@axios';


export const useSearchStore = defineStore('transGroupSearchStore', () => {    
    const store = Searcher('transactions/groups')
    const head  = Header('transactions/groups', '매출 관리')
    const headers: Record<string, string> = {
        'user_name': '상호',
        'total_count': '총 거래건수',
        'total_appr_count': '총 승인건수',
        'total_cxl_count': '총 취소건수',
        'total_amount': '총 거래액',
        'total_appr_amount': '총 승인액',
        'total_cxl_amount': '총 취소액',
        'total_profit': '총 정산금',
        'total_trx_amount': '총 거래 수수료',
    }

    head.sub_headers.value = []
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.flatten(head.headers.value)
    
    const metas = ref([
        {
            icon: 'ic-outline-payments',
            color: 'primary',
            title: '승인액 합계',
            stats: '0',
            percentage: 0,
            subtitle: '0건',
        },
        {
            icon: 'ic-outline-payments',
            color: 'error',
            title: '취소액 합계',
            stats: '0',
            percentage: 0,
            subtitle: '0건',
        },
        {
            icon: 'ic-outline-payments',
            color: 'success',
            title: '매출액 합계',
            stats: '0',
            percentage: 0,
            subtitle: '0건',
        },
    ])
    if((getUserLevel() == 10 && user_info.value.is_show_fee) || getUserLevel() >= 13) {
        metas.value.push({
            icon: 'ic-outline-payments',
            color: 'warning',
            title: '정산액 합계',
            stats: '0',
            percentage: 0,
            subtitle: '0건',
        })
    }

    const exporter = async (type: number) => {      
        const r = await store.get(store.base_url, { params:store.getAllDataFormat()})
        printer(type, r.data.content)
    }
    
    const printer = (type:number, datas: any[]) => {
        const keys = Object.keys(head.flat_headers.value)
        for (let i = 0; i <datas.length; i++) {
            datas[i]['total_count'] = (Number(datas[i]['total_appr_count']) +  Number(datas[i]['total_cxl_count']))
            datas[i]['total_amount'] = (Number(datas[i]['total_appr_amount']) +  Number(datas[i]['total_cxl_amount']))
            datas[i]['total_trx_amount'] = datas[i]['total_amount'] - Number(datas[i]['total_profit'])
            datas[i] = head.sortAndFilterByHeader(datas[i], keys)
        }
        type == 1 ? head.exportToExcel(datas) : head.exportToPdf(datas)        
    }

    return {
        store,
        head,
        exporter,
        metas,
        printer,
    }
})
