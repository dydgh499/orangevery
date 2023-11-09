import { getAllPayModules } from '@/views/merchandises/pay-modules/useStore'
import { pay } from '@/views/pay/pay'
import type { Merchandise, PayModule } from '@/views/types'
import { axios } from '@axios'

export const payTest = (module_type:number) => {
    const mcht_id = ref()
    const pmod_id = ref()
    const pay_modules = ref<PayModule[]>([])
    const merchandises = ref<Merchandise[]>([])
    const { merchandise, pay_module, pay_url, pgs, updateMerchandise } = pay(module_type)
    const return_url = new URL(window.location.href).origin + '/transactions/pay-test/result'

    axios.get('/api/v1/manager/merchandises/all?module_type=' + module_type).then((r) => {
        merchandises.value = r.data.content.sort((a:Merchandise, b:Merchandise) => a.mcht_name.localeCompare(b.mcht_name))
    })
    watchEffect(async () => { 
        //가맹점 선택시 호출
        if(mcht_id.value) {
            pmod_id.value = null
            Object.assign(pay_modules.value, await getAllPayModules(mcht_id.value))
            updateMerchandise(mcht_id.value)    
        }
    })
    
    watchEffect(() => { 
        //결제모듈 선택 시 호출
        if(pmod_id.value) {
            const pmod = pay_modules.value.find(obj => obj.id == pmod_id.value)
            if (pmod)
                pay_module.value = pmod
        }

    })
    return {
        mcht_id, pmod_id, return_url, pay_url,
        merchandise, merchandises, pay_modules, pay_module, pgs
    }
}
