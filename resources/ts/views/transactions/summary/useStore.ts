import router from '@/router';
import { Header } from '@/views/headers';
import { useRequestStore } from '@/views/request';
import { Searcher } from '@/views/searcher';
import { useStore } from '@/views/services/pay-gateways/useStore';
import { getUserLevel, user_info } from '@axios';
import * as XLSX from 'xlsx';


export const useSearchStore = defineStore('transGroupSearchStore', () => {    
    const store = Searcher('transactions/summary')
    const head  = Header('transactions/summary', '매출 관리')
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
    const { pgs, pss, settle_types, terminals, cus_filters } = useStore()
    const { get } = useRequestStore()
    
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


    const mchtGroup = async() => {        
        const url = '/api/v1/manager/transactions/merchandises/groups'
        const r = await get(url, {params: router.currentRoute.value.query})
        const datas = []
        datas.push([
            'NO.',
            '가맹점 상호',
            '주민등록번호',
            '사업자등록번호',
            '대표자명',
            '주소',
            '업종',
            '결제건수',
            '승인금액',
            '승인건수',
            '취소금액',
            '취소건수',
            '거래금액',
            '거래 수수료',
            '거래 수수료 공급가액',
            '거래 수수료 세액',
            '정산금액',
            '커스텀 필터',
        ])
        for (let i = 0; i < r.data.length; i++) 
        {
            r.data[i]['cxl_amount'] = Number(r.data[i]['cxl_amount'])
            r.data[i]['appr_amount'] = Number(r.data[i]['appr_amount'])
            r.data[i]['profit'] = Number(r.data[i]['profit'])
            r.data[i]['count'] = Number(r.data[i]['appr_count']) + Number(r.data[i]['cxl_count'])
            r.data[i]['total_amount'] = r.data[i]['appr_amount'] + r.data[i]['cxl_amount']
            r.data[i]['trx_amount'] = r.data[i]['total_amount'] - r.data[i]['profit']
            r.data[i]['trx_supply_amount'] = Math.round(r.data[i]['trx_amount']/1.1)
            r.data[i]['trx_tax_amount'] = r.data[i]['trx_amount'] - r.data[i]['trx_supply_amount']
            
            datas.push([
                r.data[i]['id'],
                r.data[i]['mcht_name'],
                r.data[i]['resident_num'],
                r.data[i]['business_num'],
                r.data[i]['nick_name'],
                r.data[i]['addr'],
                r.data[i]['sector'],
                r.data[i]['count'],
                r.data[i]['appr_amount'],
                r.data[i]['appr_count'],
                r.data[i]['cxl_amount'],
                r.data[i]['cxl_count'],
                r.data[i]['total_amount'],
                r.data[i]['trx_amount'],
                r.data[i]['trx_supply_amount'],
                r.data[i]['trx_tax_amount'],
                r.data[i]['profit'],
                cus_filters.find(cus => cus.id === r.data[i]['custom_id'])?.name,
            ])
        }

        const date = new Date().toISOString().split('T')[0];
        const ws: XLSX.WorkSheet = XLSX.utils.aoa_to_sheet(datas)
        const wb = XLSX.utils.book_new()
        XLSX.utils.book_append_sheet(wb, ws, "가맹점별 매출집계")
        XLSX.writeFile(wb, "가맹점별 매출집계_" + date + ".xlsx")
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
        mchtGroup,
    }
})
