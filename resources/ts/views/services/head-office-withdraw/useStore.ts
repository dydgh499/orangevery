
import type { HeadOffceAccount } from '@/views/types'
import { axios } from '@axios'

export const useHeadOfficeAccountStore = defineStore('useHeadOfficeAccountStore', () => {
    const head_office_accounts = ref(<HeadOffceAccount[]>([]))

    const getHeadOfficeAccount = async() => {
        const url = '/api/v1/manager/services/head-office-accounts'
        const r = await axios.get(url)
        Object.assign(head_office_accounts.value, r.data.content)
    }
    onMounted(async () => { 
        await getHeadOfficeAccount() 
    })
    return {head_office_accounts}
})
