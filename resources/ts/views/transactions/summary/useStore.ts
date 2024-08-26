import router from '@/router';
import { Header } from '@/views/headers';
import { useRequestStore } from '@/views/request';
import { Searcher } from '@/views/searcher';
import { useStore } from '@/views/services/pay-gateways/useStore';
import { getUserLevel, user_info } from '@axios';
import { Workbook } from 'exceljs';


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
    const { cus_filters } = useStore()
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
        const date = new Date().toISOString().split('T')[0];
        const wb = new Workbook();
        const worksheet = wb.addWorksheet("가맹점별 매출집계");

        const url = '/api/v1/manager/transactions/merchandises/groups'
        const r = await get(url, {params: router.currentRoute.value.query})
        const _headers = {
            'mcht_name': '가맹점 상호',
            'resident_num': '주민등록번호',
            'business_num': '사업자등록번호',
            'nick_name': '대표자명',
            'addr': '주소',
            'sector': '업종',
            'count': '결제건수',
            'appr_amount': '승인금액',
            'appr_count': '승인건수',
            'cxl_amount': '취소금액',
            'cxl_count': '취소건수',
            'total_amount': '거래금액',
            'trx_amount': '거래 수수료',
            'trx_supply_amount': '거래 수수료 공급가액',
            'trx_tax_amount': '거래 수수료 세액',
            'profit': '정산금액',
            'custom_id': '커스텀 필터',
        }
        const columns = []
        const keys = Object.keys(_headers)
        for (let i = 0; i < keys.length; i++) {
            columns.push({
                header: _headers[keys[i]],
                key: keys[i],
                width: 20,
            })            
        }
        worksheet.columns = columns

        for (let i = 0; i < r.data.length; i++) 
        {
            r.data[i]['cxl_amount'] = Number(r.data[i]['cxl_amount'])
            r.data[i]['appr_amount'] = Number(r.data[i]['appr_amount'])
            r.data[i]['profit'] = Number(r.data[i]['profit'])
            r.data[i]['total_amount'] = Number(r.data[i]['appr_amount'] + r.data[i]['cxl_amount'])
            r.data[i]['trx_amount'] = Number(r.data[i]['total_amount'] - r.data[i]['profit'])

            let row = {
                mcht_name: r.data[i]['mcht_name'],
                resident_num: r.data[i]['resident_num'],
                business_num: r.data[i]['business_num'],
                nick_name: r.data[i]['nick_name'],
                addr: r.data[i]['addr'],
                sector: r.data[i]['sector'],
                count: Number(r.data[i]['appr_count']) + Number(r.data[i]['cxl_count']),
                appr_amount: r.data[i]['appr_amount'],
                appr_count: r.data[i]['appr_count'],
                cxl_amount: r.data[i]['cxl_amount'],
                cxl_count: r.data[i]['cxl_count'],
                total_amount: r.data[i]['total_amount'],
                trx_amount: r.data[i]['trx_amount'],
                profit: r.data[i]['profit'],
                custom_id: cus_filters.find(cus => cus.id === r.data[i]['custom_id'])?.name,
            }
            row['trx_supply_amount'] = Number(Math.round(row['trx_amount']/1.1))
            row['trx_tax_amount']    = Number(row['trx_amount'] - row['trx_supply_amount'])
            console.log(row)
            worksheet.addRow(row)
        }
        head.setHeaderStyle(worksheet.getRow(1));

        worksheet.views = [{ state: 'frozen', ySplit: 1 }];
        try {
            const buffer = await wb.xlsx.writeBuffer(); // 버퍼로 엑셀 데이터 생성
            const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
            
            // 파일 다운로드 링크 생성
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `${"가맹점별 매출집계"}_${date}.xlsx`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        } catch (err) {
            console.error('Error creating Excel file:', err);
        }
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
        head.exportToExcel(datas)        
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
