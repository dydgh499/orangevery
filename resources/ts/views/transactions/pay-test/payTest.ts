import { getAllPayModules, payModFilter } from '@/views/merchandises/pay-modules/useStore'
import { pay } from '@/views/pay/pay'
import type { Merchandise, PayModule } from '@/views/types'
import { axios } from '@axios'

export const payTest = (module_type:number) => {
    const pay_modules = reactive<PayModule[]>([])
    const merchandises = reactive<Merchandise[]>([])
    const { pmod_id, pg_id, is_old_auth, installment, merchandise, pay_url, pgs } = pay(module_type)

    const mcht_id = ref()
    const return_url = new URL(window.location.href).origin + '/transactions/pay-test/result'


    const filterPayMod = computed(() => {
        const filter = pay_modules.filter((obj: PayModule) => { return obj.mcht_id == mcht_id.value && obj.module_type == module_type })
        pmod_id.value = payModFilter(pay_modules, filter, pmod_id.value as number) ?? 0
        return filter
    })
    
    const getAllMerchandises = async(module_type:number|null = null) => {
        const url = '/api/v1/manager/merchandises/all' + (module_type != null ? '?module_type='+module_type : '')
        const r = await axios.get(url)
        return r.data.content.sort((a:Merchandise, b:Merchandise) => a.mcht_name.localeCompare(b.mcht_name))
    }
    
    watchEffect(async() => { 
        Object.assign(pay_modules, await getAllPayModules())
        Object.assign(merchandises, await getAllMerchandises(module_type))
    })

    watchEffect(() => { 
        const pmod = pay_modules.find(obj => obj.id == pmod_id.value)
        if (pmod) {
            merchandise.value = merchandises.find(obj => obj.id == mcht_id.value) as Merchandise
            is_old_auth.value = Boolean(pmod.is_old_auth)
            installment.value = pmod.installment
            pg_id.value = pmod.pg_id ?? 0
        }
    })
    return {
        mcht_id, pmod_id, pg_id, installment, return_url, pay_url,
        merchandise, merchandises, is_old_auth, filterPayMod, pgs
    }
}
