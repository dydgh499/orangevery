import { axios, getUserLevel } from '@/plugins/axios'
import corp from '@/plugins/corp'
import { Header } from '@/views/headers'
import { Searcher } from '@/views/searcher'
import { useStore } from '@/views/services/pay-gateways/useStore'
import type { Options, VirtualAccount } from '@/views/types'

export const fin_trx_delays = <Options[]>([
    {id: 0, title:'즉시입금'},
    {id: 1, title:'1분'},
    {id: 5, title:'5분'},
    {id: 15, title:'15분'},
    {id: 30, title:'30분'}, 
    {id: 45, title:'45분'},
    {id: 60, title:'60분'},
])

export const withdraw_limit_types = <Options[]>([
    {id: 0, title:'설정안함'},
    {id: 1, title:'주말 출금금지'},
    {id: 2, title:'공휴일 출금금지'},
    {id: 3, title:'주말+공휴일 출금금지'},
])

export const withdraw_types = <Options[]>([
    {id: 0, title:'모아서출금'},
    {id: 1, title:'즉시출금'},
])

if(corp.pv_options.paid.use_collect_withdraw_scheduler) {
    fin_trx_delays.unshift(<Options>{id: 31, title: '자동정산(30분)'})
}

export const useSearchStore = defineStore('WalletStore', () => {    
    const store = Searcher('virtual-accounts/wallets')
    const head  = Header('virtual-accounts/wallets', '지갑 관리')
    const { finance_vans } = useStore()

    const getNoHeader = () => {
        return {
            'id': 'NO.',
        }
    }

    const getSalesHeader = () => {
        const levels = corp.pv_options.auth.levels
        const headers_4:Record<string, string> = {}
        if (levels.sales5_use && getUserLevel() >= 30) {
            headers_4['sales5_name'] = levels.sales5_name
        }
        if (levels.sales4_use && getUserLevel() >= 25) {
            headers_4['sales4_name'] = levels.sales4_name
        }
        if (levels.sales3_use && getUserLevel() >= 20) {
            headers_4['sales3_name'] = levels.sales3_name
        }
        if (levels.sales2_use && getUserLevel() >= 17) {
            headers_4['sales2_name'] = levels.sales2_name
        }
        if (levels.sales1_use && getUserLevel() >= 15) {
            headers_4['sales1_name'] = levels.sales1_name
        }
        if (levels.sales0_use && getUserLevel() >= 13) {
            headers_4['sales0_name'] = levels.sales0_name
        }
        return headers_4
    }

    const getOwnerHeader = () => {
        return {
            'user_name': '상호',
            'account_name': '계좌별칭',
            'account_code': '계좌코드',
            'balance': '지갑잔액',
        }
    }
    const geWalletHeader = () => {
        return {
            'fin_id': '이체모듈 타입',    
            'fin_trx_delay': '출금 딜레이',
            'withdraw_type': '출금타입',
        }
    }

    const getLimitHeader = () => {
        return {
            'withdraw_limit_type': '출금제한타입',    
            'withdraw_business_limit': '일 출금한도(영업일)',
            'withdraw_holiday_limit': '일 출금한도(휴무일)',    
            'withdraw_fee': '출금 수수료',    
        }
    }
    const getEtcHeader = () => {
        let cols = {
            'created_at': '생성시간',    
            'updated_at': '업데이트시간',
        }
        if(getUserLevel() >= 35)
            cols['all_withdraw'] = '기타'
        return cols
    }

    const headers: Record<string, string> = {
        ...getNoHeader(),
        ...getSalesHeader(),
        ...getOwnerHeader(),
        ...geWalletHeader(),
        ...getLimitHeader(),
        ...getEtcHeader(),
    }
    const sub_headers: any = []
    head.getSubHeaderCol('', getNoHeader(), sub_headers)
    head.getSubHeaderCol('영업라인 정보', getSalesHeader(), sub_headers)
    head.getSubHeaderCol('소유자 정보', getOwnerHeader(), sub_headers)
    head.getSubHeaderCol('출금 정보', geWalletHeader(), sub_headers)
    head.getSubHeaderCol('제한 정보', getLimitHeader(), sub_headers)
    head.getSubHeaderCol('기타 정보', getEtcHeader(), sub_headers)

    head.sub_headers.value = sub_headers
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.flatten(head.headers.value)

    const exporter = async () => {
        const r = await store.get(store.base_url, { params:store.getAllDataFormat()})
        let datas = r.data.content

        for (let i = 0; i < datas.length; i++) {
            datas[i]['fin_id'] = finance_vans.find(obj => obj.id === datas[i]['fin_id'])?.nick_name as string
            datas[i]['fin_trx_delay'] = fin_trx_delays.find(obj => obj.id === datas[i]['fin_trx_delay'])?.title as string
            datas[i]['withdraw_type'] = withdraw_types.find(obj => obj.id === datas[i]['withdraw_type'])?.title as string
            datas[i]['withdraw_limit_type'] = withdraw_limit_types.find(obj => obj.id === datas[i]['withdraw_limit_type'])?.title as string                
        }
        
        head.exportToExcel(datas)
    }
    return {
        store,
        head,
        exporter,
    }
});


export const useWalletFilterStore = defineStore('useWalletFilterStore', () => {
    const mcht_wallets = ref(<VirtualAccount[]>([]))
    const sales_wallets = ref(<VirtualAccount[]>([]))
    const errorHandler = <any>(inject('$errorHandler'))
    const snackbar = <any>(inject('snackbar'))

    onMounted(() => { 
        getAllWallets()
    })
    const getAllWallets = async() => {
        try {
            const r = await axios.get('/api/v1/manager/virtual-accounts/wallets/all')
            Object.assign(mcht_wallets.value, r.data.mchts.filter(obj => obj.level === 10).sort(
                (a:VirtualAccount, b:VirtualAccount) => a.account_name.localeCompare(b.account_name))
            )
            Object.assign(sales_wallets.value, r.data.sales.filter(obj => obj.level !== 10).sort(
                (a:VirtualAccount, b:VirtualAccount) => a.account_name.localeCompare(b.account_name))
            )
            return true
        }
        catch (error: any) {
            await nextTick()
            snackbar.value?.show(error.response?.data?.message || 'Error occurred', 'error')
            errorHandler?.(error)
            return false
        }    
    }

    const walletFilter = (user_id: number | null, level: number) => {
        if(level === 10) 
            return mcht_wallets.value.filter(obj => obj.user_id === user_id)
        else 
            return sales_wallets.value.filter(obj => obj.user_id === user_id)
    }

    return {
        walletFilter,
        mcht_wallets,
        sales_wallets
    }
})
