import { getAllPayModules } from '@/views/merchandises/pay-modules/useStore'
import { pay } from '@/views/pay/pay'
import { useSalesFilterStore } from '@/views/salesforces/useStore'
import { useStore } from '@/views/services/pay-gateways/useStore'
import type { PayModule } from '@/views/types'

export const payTest = (module_type:number) => {
    const mcht_id = ref()
    const pmod_id = ref()
    const { pgs } = useStore()
    const pay_modules = ref<PayModule[]>([])
    const { mchts } = useSalesFilterStore()
    const merchandises = mchts
    
    const { merchandise, pay_module, pay_url, updateMerchandise } = pay(module_type)
    const return_url = new URL(window.location.href).origin + '/transactions/pay-test/result'

    watchEffect(async () => { 
        //가맹점 선택시 호출
        if(mcht_id.value) {
            pmod_id.value = null
            Object.assign(pay_modules.value, await getAllPayModules(mcht_id.value, module_type))
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
