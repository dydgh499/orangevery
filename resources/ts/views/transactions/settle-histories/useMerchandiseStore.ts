import { Header } from '@/views/headers'
import { Searcher } from '@/views/searcher'
import { getUserLevel } from '@axios'

export const useSearchStore = defineStore('transSettlesHistoryMchtSearchStore', () => {    
    const store = Searcher('transactions/settle-histories/merchandises')
    const head  = Header('transactions/settle-histories/merchandises', '가맹점 정산이력')
    const headers:Record<string, string | object> = {
        'id': 'NO.',
        'user_name' : '가맹점 ID',
        'mcht_name' : '상호',
        'settle_fee': '입금 수수료',
        'total_amount': '매출액',
        'appr_amount': '승인액',
        'cxl_amount': '취소액',
        'deduct_amount': '추가차감액',
        'settle_amount': '정산액',
        'settle_dt': '정산일',
        'deposit_dt': '입금일',
        'deposit_status': '입금상태',
        'acct_bank_name': '은행',
        'acct_bank_code': '은행코드',
        'acct_name': '예금주',
        'acct_num': '계좌번호',
        'created_at': '생성시간',
    };
    if(getUserLevel() >= 35) {
        headers['extra_col'] = '더보기'
    }
    
    head.main_headers.value = [];
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.setFlattenHeaders()

    const exporter = async (type: number) => {      
        const keys = Object.keys(headers);
        const r = await store.get(store.base_url, { params:store.getAllDataFormat()})
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
