import { Header } from '@/views/headers';
import { installments, module_types } from '@/views/merchandises/pay-modules/useStore';
import { Searcher } from '@/views/searcher';
import { useStore } from '@/views/services/pay-gateways/useStore';
import type { Transaction } from '@/views/types';
import { getUserLevel } from '@axios';

export const useSearchStore = defineStore('transSettlesHistoryDifferenceSearchStore', () => {    
    const store = Searcher('transactions/settle-histories/difference')
    const head  = Header('transactions/settle-histories/difference', '차액정산 이력')
    const headers: Record<string, string> = {
        'id': 'NO.',
        'module_type': '거래 타입',
    }
    const { pgs, pss, terminals } = useStore()
    if(getUserLevel() >= 35) {
        headers['pg_id'] = 'PG사'
        headers['ps_id'] = '구간'
        headers['ps_fee'] = '구간 수수료'
    }
    headers['mcht_name'] = '가맹점'
    if(getUserLevel() >= 35)
    {
        headers['custom_id'] = '커스텀필터'
        headers['terminal_id'] = '장비타입'
    }
    headers['amount'] = '거래금액'
    headers['supply_amount'] = '공급가액'
    headers['vat_amount'] = '부가세'
    headers['settle_amount'] = '차액 정산금'

    headers['trx_dttm'] = '거래 시간'
    headers['cxl_dttm'] = '취소 시간'
    headers['installment'] = '할부'
    if(getUserLevel() >= 13)
    {
        headers['mid'] = 'MID'
        headers['tid'] = 'TID'    
    }
    headers['appr_num'] = '승인번호'    
    headers['issuer'] = '발급사'
    headers['acquirer'] = '매입사'

    headers['card_num'] = '카드번호'
    headers['buyer_name'] = '구매자명'
    headers['buyer_phone'] = '구매자 연락처'
    
    headers['item_name'] = '상품명'
    if(getUserLevel() >= 13)
    {
        headers['ord_num'] = '주문번호'
        headers['trx_id'] = '거래번호'
    }    
    headers['created_at'] = '생성시간'
    headers['updated_at'] = '업데이트시간'
    
    head.main_headers.value = [];
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.setFlattenHeaders()
    
    const exporter = async (type: number) => {      
        const r = await store.get(store.base_url, { params:store.getAllDataFormat()})
        printer(type, r.data.content)
    }
    const printer = (type:number, datas: Transaction[]) => {
        const keys = Object.keys(headers);
        for (let i = 0; i <datas.length; i++) {
            datas[i]['module_type'] = module_types.find(module_type => module_type['id'] === datas[i]['module_type'])?.title as string
            datas[i]['installment'] = installments.find(inst => inst['id'] === datas[i]['installment'])?.title as string
            datas[i]['pg_id'] = pgs.find(pg => pg['id'] === datas[i]['pg_id'])?.pg_name as string
            datas[i]['ps_id'] =  pss.find(ps => ps['id'] === datas[i]['ps_id'])?.name as string
            datas[i]['terminal_id'] = terminals.find(terminal => terminal['id'] === datas[i]['terminal_id'])?.name as string
            datas[i] = head.sortAndFilterByHeader(datas[i], keys)
        }
        type == 1 ? head.exportToExcel(datas) : head.exportToPdf(datas)        
    }
    return {
        store,
        head,
        exporter,
        printer,
    }
})
