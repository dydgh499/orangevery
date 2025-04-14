import { Header } from '@/views/headers';
import { Searcher } from '@/views/searcher';

export const useSearchStore = defineStore('WithdrawHistoryStore', () => {    
    const store = Searcher('virtual-accounts/withdraw-histories')
    const head  = Header('virtual-accounts/withdraw-histories', '출금 상세이력')
    const getOwnerHeader = () => {
        return {
            'id': 'NO.',
            'user_name': '상호',
            'account_name': '계좌별칭',
            'account_code': '계좌코드',
        }
    }
    const geWithdrawHeader = () => {
        return {
            'va_history_id': '입출금 NO.',
            'result_code': '응답코드',
            'request_type': '요청타입',
            'note': '응답메세지',
            'amount': '거래금액',
        }
    }

    const getAccountHeader = () => {
        return {
            'acct_num': '계좌번호',
            'acct_bank_name': '입금은행명',
            'acct_bank_code': '은행코드',
            'trans_seq_num': '요청번호',
        }
    }
    const getEtcHeader = () => {
        return {
            'created_at': '생성시간',    
            'updated_at': '업데이트시간',
        }
    }

    const headers: Record<string, string> = {
        ...getOwnerHeader(),
        ...geWithdrawHeader(),
        ...getAccountHeader(),
        ...getEtcHeader(),
    }
    const sub_headers: any = []
    head.getSubHeaderCol('소유자 정보', getOwnerHeader(), sub_headers)
    head.getSubHeaderCol('출금 정보', geWithdrawHeader(), sub_headers)
    head.getSubHeaderCol('개인 정보', getAccountHeader(), sub_headers)
    head.getSubHeaderCol('기타 정보', getEtcHeader(), sub_headers)

    head.sub_headers.value = sub_headers
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.flatten(head.headers.value)

    const exporter = async () => {
        const r = await store.get(store.base_url, { params:store.getAllDataFormat()})
        let datas = r.data.content        
        head.exportToExcel(datas)
    }
    return {
        store,
        head,
        exporter,
    }
});
