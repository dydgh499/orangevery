import type { Classification, PayGateway, PaySection } from '@/views/types';
import { axios } from '@axios';

export const useStore = defineStore('payGatewayStore', () => {
    const pgs = ref<PayGateway[]>([])
    const pss = ref<PaySection[]>([])
    const ternimals   = ref<Classification[]>([])
    const pay_conds   = ref<Classification[]>([])
    const cus_filters = ref<Classification[]>([])
    const errorHandler = <any>(inject('$errorHandler'))
    
    onMounted(async () => {
        try {
            const r = await axios.get('/api/v1/manager/pay-gateways/detail')
            Object.assign(pgs.value, r.data.pay_gateways)
            Object.assign(pss.value, r.data.pay_sections)
            Object.assign(ternimals.value, r.data.ternimals)
            Object.assign(pay_conds.value, r.data.pay_conditions)
            Object.assign(cus_filters.value, r.data.custom_filters)
        }
        catch(e) {
            errorHandler(e)
        }
    })
    return {
        pgs, pss, ternimals, pay_conds, cus_filters,
    }
})
