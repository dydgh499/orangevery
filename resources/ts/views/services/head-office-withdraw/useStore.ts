
import { Header } from '@/views/headers'
import { Searcher } from '@/views/searcher'
import type { HeadOffceAccount } from '@/views/types'
import { axios } from '@axios'

export const useSearchStore = defineStore('useHeadOfficeSearchStore', () => {
    const store = Searcher('services/head-office-accounts')
    const head  = Header('services/head-office-accounts', '가상계좌 출금')
    const headers: Record<string, string> = {
        'id' : 'NO.',
        'result_code': '결과',
        'withdraw_amount': '출금금액',
        'acct_num': '계좌번호',
        'acct_name': '예금주',
        'acct_bank_name': '입금은행명',
        'acct_bank_code': '은행코드',
        'message'   : '출금사유',
        'created_at': '출금시간',
    }    
        
    head.sub_headers.value = []
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.flatten(head.headers.value)

    const exporter = async (type: number) => {
        const keys = Object.keys(head.flat_headers.value)
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

export const useHeadOfficeAccountStore = defineStore('useHeadOfficeAccountStore', () => {
    const head_office_accounts = ref(<HeadOffceAccount[]>([]))

    const getHeadOfficeAccount = async() => {
        const r = await axios.get('/api/v1/manager/services/head-office-accounts/all')
        Object.assign(head_office_accounts.value, r.data.content)
    }
    onMounted(async () => { 
        await getHeadOfficeAccount() 
    })
    return {head_office_accounts}
})
