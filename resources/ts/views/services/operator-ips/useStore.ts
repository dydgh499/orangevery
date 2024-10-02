import { OperatorIp } from '@/views/types';
import { axios } from '@axios';

export const useOperatorIpStore = defineStore('OperatorIpStore', () => {
    const operator_ips = ref(<OperatorIp[]>([]))

    onMounted(async () => { 
        const r = await axios.get('/api/v1/manager/services/operator-ips', {
            params: {
                page: 1,
                page_size: 999,
            }
        })
        operator_ips.value = r.data.content
    })

    return { operator_ips }
})
