import { payModFilter, usePayModFilterStore } from '@/views/merchandises/pay-modules/useStore'
import { useMchtFilterStore } from '@/views/merchandises/useStore'
import { pay } from '@/views/pay/pay'
import { useStore } from '@/views/services/pay-gateways/useStore'
import type { Merchandise, PayGateway, PayModule } from '@/views/types'

export const payTest = (module_type:number) => {
    const { pgs } = useStore()
    const { pay_modules, getAllPayModules } = usePayModFilterStore()
    const { merchandises, getAllMerchandises } = useMchtFilterStore()
    const { pgTypeToPath, pmod_id, pg_id, is_old_auth, installment, merchandise, pg_type, pay_url } = pay(module_type)

    const mcht_id = ref()
    const return_url = new URL(window.location.href).origin + '/transactions/pay-test/result'

    getAllMerchandises(module_type)
    getAllPayModules()

    const filterPayMod = computed(() => {
        const filter = pay_modules.filter((obj: PayModule) => { return obj.mcht_id == mcht_id.value && obj.module_type == 2 })
        pmod_id.value = payModFilter(pay_modules, filter, pmod_id.value as number) ?? 0
        return filter
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
    watchEffect(() => {
        if (pgs.length > 0 && pg_id.value) {
            const pg: PayGateway = pgs.find(obj => obj.id == pg_id.value)
            pg_type.value = pgTypeToPath(pg.pg_type ?? 1)
        }
    })
    return {
        mcht_id, pmod_id, pg_id, pg_type, installment, return_url, pay_url,
        merchandise, merchandises, is_old_auth, filterPayMod, pgs
    }
}
