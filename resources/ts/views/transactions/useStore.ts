import { Header } from '@/views/headers';
import { Searcher } from '@/views/searcher';
import corp from '@corp';

export const useSearchStore = defineStore('transSearchStore', () => {    
    const store = Searcher('transactions')
    const head  = Header('transactions', '매출 관리')
    const levels = corp.pv_options.auth.levels
    const headers: Record<string, string> = {
        'id': 'NO.',
        'module_type': '거래 타입',
    }
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

    headers['settle_type'] = '정산일 수수료'
    headers['pay_cond_price'] = '입금 수수료'
    headers['pg_id'] = 'PG사 수수료'
    headers['ps_fee'] = '구간 수수료'

    headers['custom_id'] = '커스텀필터'
    headers['terminal_id'] = '단말기타입'
    headers['amount'] = '거래 금액'
    headers['trx_amount'] = '거래 수수료'
    headers['profit'] = '정산 수수료'

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
        let convert = r.data.content;
        for (let index = 0; index <convert.length; index++) {
        
        }
        type == 1 ? head.exportToExcel(convert) : head.exportToPdf(convert)        
    }
    return {
        store,
        head,
        exporter,
    }
})
