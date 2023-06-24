import { Header } from '@/views/headers';
import { Searcher } from '@/views/searcher';

export const useSearchStore = defineStore('transSettlesHistoryMchtSearchStore', () => {    
    const store = Searcher('transactions/settle-histories/merchandises')
    const head  = Header('transactions/settle-histories/merchandises', '가맹점 정산이력')
    const headers = {
        'id': 'NO.',
        'user_name' : '가맹점 ID',
        'mcht_name' : '상호',
        'total_amount': '매출액',
        'appr_amount': '승인액',
        'cxl_amount': '취소액',
        'deduct_amount': '추가차감액',
        'settle_amount': '정산액',
        'settle_dt': '정산일',
        'deposit_dt': '입금일',
        'deposit_status': '입금상태',
        'acct_bank_nm': '은행',
        'acct_bank_cd': '은행코드',
        'acct_nm': '예금주',
        'acct_num': '계좌번호',
        'created_at': '생성시간',
        'extra_col': '더보기',
    };
    head.main_headers.value = [];
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.setFlattenHeaders()

    const exporter = async (type: number) => {      
        const r = await store.get(store.getAllDataFormat())
        let datas = r.data.content;
        for (let i = 0; i <datas.length; i++) 
        {
            datas[i]['deposit_status'] = datas[i]['deposit_status'] ? '입금완료' : '미입금';
        }
        type == 1 ? head.exportToExcel(datas) : head.exportToPdf(datas)
    }
    
    return {
        store,
        head,
        exporter,
    }
})
