
import { Header } from '@/views/headers'
import { Searcher } from '@/views/searcher'
import { useStore } from '@/views/services/pay-gateways/useStore'
import { depositMessage } from '@/views/transactions/settle-histories/useCollectWithdrawHistoryStore'
import type { HeadOffceAccount } from '@/views/types'
import { axios } from '@axios'

export const useSearchStore = defineStore('useCMSTransactionSearchStore', () => {
    const store = Searcher('services/cms-transactions')
    const head  = Header('services/cms-transactions', '가상계좌 입출금')
    const { finance_vans } = useStore()
    const headers: Record<string, string> = {
        'id' : 'NO.',
        'result_code': '성공여부',
        'fin_id': '거래모듈',
        'is_withdraw': '거래타입',
        'trx_at': '거래시간',
        'amount': '거래금액',
        'trans_seq_num': '거래번호',
        'acct_num' : '계좌번호',
        'acct_name' : '계좌명',
        'acct_bank_name' : '은행명',
        'acct_bank_code' : '은행코드',
        'note': '메모사항',
    }

    head.sub_headers.value = [
        head.getSubHeaderFormat('거래정보', 'id', 'trans_seq_num', 'string', 7),
        head.getSubHeaderFormat('출금정보', 'acct_num', 'acct_bank_code', 'string', 4),
        head.getSubHeaderFormat('기타', 'note', 'note', 'string', 1),
    ]
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.flatten(head.headers.value)

    const exporter = async (type: number) => {
        const keys = Object.keys(head.flat_headers.value)
        const r = await store.get(store.base_url, { params:store.getAllDataFormat()})
        let datas = r.data.content;
        for (let i = 0; i < datas.length; i++) {
            datas[i]['fin_id'] = (finance_vans.find(obj => obj.id == datas[i]['fin_id']))?.nick_name
            datas[i]['is_withdraw'] = datas[i]['is_withdraw'] ? '출금' : '입금'
            datas[i]['result_code'] = depositMessage(datas[i]['result_code'])
            datas[i] = head.sortAndFilterByHeader(datas[i], keys)
        }
        head.exportToExcel(datas)
    }

    const metas = ref([])
    
    return {
        store,
        head,
        exporter,
        metas,
    }
})

export const useHeadOfficeAccountStore = defineStore('useHeadOfficeAccountStore', () => {
    const head_office_accounts = ref(<HeadOffceAccount[]>([]))

    const getHeadOfficeAccount = async() => {
        const r = await axios.get('/api/v1/manager/services/head-office-accounts')
        Object.assign(head_office_accounts.value, r.data.content)
    }
    onMounted(async () => { 
        await getHeadOfficeAccount() 
    })
    return {head_office_accounts}
})
