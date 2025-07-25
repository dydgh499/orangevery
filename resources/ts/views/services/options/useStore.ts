import { axios, getUserLevel } from '@/plugins/axios'
import corp from '@/plugins/corp';
import { useRequestStore } from '@/views/request'
import type { BillKey, FinanceVan, Options, PayGateway, PayModule, PaySection } from '@/views/types'

export const getDeliveryModeInfo = () => {
    const { pgs } = useStore()
    if(corp.id === 1) {
        return {
            pg_id: pgs.find(obj => obj.pg_type === 1)?.id || null
        }
    }
    else {
        return {
            pg_id: null
        }        
    }
}

export const module_types = <Options[]>([
    { id: 4, title: "빌키결제" }
])

export const useStore = defineStore('payGatewayStore', () => {
    const snackbar = <any>(inject('snackbar'))
    const pgs = ref<PayGateway[]>([])
    const pss = ref<PaySection[]>([])
    const finance_vans  = ref<FinanceVan[]>([])
    const pay_modules   = ref<PayModule[]>([])
    const bill_keys     = ref<BillKey[]>([])

    const pg_companies = [
        {id:1, name:'루트업', rep_name:'길민홍', company_name:'루트업', business_num:'456-81-01313', phone_num:'1811-7717', addr:'서울특별시 금천구 디지털로9길 99, 스타밸리 1013호'},
    ]
    const finance_companies = <Options[]>([
        {id:1, title:'쿠콘'},
    ])
    
    onMounted(async () => {
        try {
            const r = await axios.get('/api/v1/manager/services/detail', {})
            Object.assign(pgs.value, r.data.pay_gateways.sort((a:PayGateway, b:PayGateway) => a.pg_name.localeCompare(b.pg_name)))
            Object.assign(pss.value, r.data.pay_sections.sort((a:PaySection, b:PaySection) => a.name.localeCompare(b.name)))
            Object.assign(finance_vans.value, r.data.finance_vans.sort((a:FinanceVan, b:FinanceVan) => a.nick_name.localeCompare(b.nick_name)))
            Object.assign(pay_modules.value, r.data.pay_modules.sort((a:FinanceVan, b:FinanceVan) => a.nick_name.localeCompare(b.nick_name)))
            Object.assign(bill_keys.value, r.data.bill_keys.sort((a:FinanceVan, b:FinanceVan) => a.nick_name.localeCompare(b.nick_name)))
            getFianaceVansBalance()
        }
        catch(e) {
            console.error(e)
        }
    })

    const updateFinanceVan = (fin_id: number) => {
        const finance_van = finance_vans.value.find(obj => obj.id === fin_id)
        if(finance_van)
            getFinanceVan(finance_van)
    }

    const getFinanceVan = async (finance_van: FinanceVan) => {
        let res = await axios.post('/api/v1/manager/services/cms-transactions/get-balance', finance_van, false)
        let data = res.data
        if(data.code == 1) {
            finance_van.balance = <number>(parseInt(data['data']['WDRW_CAN_AMT']))
        } 
        else {
            finance_van.balance = 0
            const message = finance_van.nick_name+'의 잔고를 불러오는 도중 에러가 발생하였습니다.<br><br>'+data['message']+'('+data['code']+')'
            snackbar.value.show(message, 'error')
        }
    }

    const getFianaceVansBalance = async () => {
        if(getUserLevel() >= 35) {
            for (let i = 0; i < finance_vans.value.length; i++)  {
                getFinanceVan(finance_vans.value[i])
            }    
        }
    }

    const setFee = (items: PaySection[], id: number | null) => {
        if(id != null)
        {
            const item = items.find(item => item.id === id)
            return item != undefined ? "수수료율: " + (item.trx_fee * 1).toFixed(3) + "%" : ''    
        }
        else
            return ''
    }
    
    const psFilter = (filter:PaySection[], ps_id:number|null) => {
        if (pss.value.length > 0) {
            if (filter.length > 0) {
                let item = pss.value.find((item:PaySection) => item.id === ps_id)
                if (item != undefined && filter[0].pg_id != item.pg_id) {
                    if (ps_id != null)
                        ps_id = null
                }
            }
            else {
                if (ps_id != null)
                    ps_id = null
            }
        }
        return ps_id
    }
    return {
        pgs, pss, finance_vans, 
        pay_modules, bill_keys, 
        pg_companies, finance_companies,
        psFilter, setFee, 
        updateFinanceVan, getFianaceVansBalance,
    }
})
