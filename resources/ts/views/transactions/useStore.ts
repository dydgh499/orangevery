import { Header } from '@/views/headers';
import { installments, module_types } from '@/views/merchandises/pay-modules/useStore';
import { Searcher } from '@/views/searcher';
import { useStore } from '@/views/services/pay-gateways/useStore';
import type { Transaction } from '@/views/types';
import corp from '@corp';

export const useSearchStore = defineStore('transSearchStore', () => {    
    const store = Searcher('transactions')
    const head  = Header('transactions', '매출 관리')
    const levels = corp.pv_options.auth.levels
    const headers: Record<string, string> = {
        'id': 'NO.',
        'module_type': '거래 타입',
    }
    const { pgs, pss, settle_types, terminals, cus_filters } = useStore()
    if(levels.sales5_use)
    {
        headers['sales5_name'] = levels.sales5_name+' ID'
        headers['sales5_fee'] = '수수료'
    }
    if(levels.sales4_use)
    {
        headers['sales4_name'] = levels.sales4_name+' ID'
        headers['sales4_fee'] = '수수료'
    }
    if(levels.sales3_use)
    {
        headers['sales3_name'] = levels.sales3_name+' ID'
        headers['sales3_fee'] = '수수료'
    }
    if(levels.sales2_use)
    {
        headers['sales2_name'] = levels.sales2_name+' ID'
        headers['sales2_fee'] = '수수료'
    }
    if(levels.sales1_use)
    {
        headers['sales1_name'] = levels.sales1_name+' ID'
        headers['sales1_fee'] = '수수료'
    }
    if(levels.sales0_use)
    {
        headers['sales0_name'] = levels.sales0_name+' ID'
        headers['sales0_fee'] = '수수료'
    }

    headers['user_name'] = '가맹점 ID'
    headers['mcht_name'] = '상호'
    headers['mcht_fee'] = '수수료'
    headers['hold_fee'] = '유보금 수수료'

    headers['pg_id'] = 'PG사 수수료'
    headers['ps_fee'] = '구간 수수료'

    headers['custom_id'] = '커스텀필터'
    headers['terminal_id'] = '단말기타입'
    headers['amount'] = '거래 금액'
    headers['trx_amount'] = '거래 수수료'
    headers['hold_amount'] = '유보금'
    headers['mcht_settle_fee'] = '입금 수수료'
    headers['total_trx_amount'] = '총 거래 수수료'
    headers['profit'] = '정산금'

    headers['trx_dttm'] = '거래 시간'
    headers['cxl_dttm'] = '취소 시간'
    headers['installment'] = '할부'
    headers['mid'] = 'MID'
    headers['tid'] = 'TID'
    headers['issuer'] = '발급사'
    headers['acquirer'] = '발행사'

    headers['card_num'] = '카드번호'
    headers['card_name'] = '카드명'
    headers['buyer_name'] = '구매자명'
    headers['buyer_phone'] = '구매자 연락처'
    
    headers['item_name'] = '상품명'
    headers['ord_num'] = '주문번호'
    headers['trx_id'] = '거래번호'
    headers['ori_trx_id'] = '원거래번호'

    headers['created_at'] = '생성시간'
    headers['extra_col'] = '더보기'
    headers['ori_trx_id'] = '원거래번호'
    
    head.main_headers.value = [];
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.setFlattenHeaders()
    
    const exporter = async (type: number) => {      
        const r = await store.get(store.getAllDataFormat())
        printer(type, r.data.content)
    }
    const printer = (type:number, datas: Transaction[]) => {
        for (let i = 0; i <datas.length; i++) {
            datas[i]['module_type'] = module_types.find(module_type => module_type['id'] === datas[i]['module_type'])?.title as string
            datas[i]['installment'] = installments.find(inst => inst['id'] === datas[i]['installment'])?.title as string
            datas[i]['pg_id'] = pgs.find(pg => pg['id'] === datas[i]['pg_id'])?.pg_nm as string
            datas[i]['ps_id'] =  pss.find(ps => ps['id'] === datas[i]['ps_id'])?.name as string
            datas[i]['settle_type'] = settle_types.find(settle_type => settle_type['id'] === datas[i]['settle_type'])?.name as string
            datas[i]['terminal_id'] = terminals.find(terminal => terminal['id'] === datas[i]['terminal_id'])?.name as string
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
