import router from '@/router'
import type { Merchandise, PayGateway, PayModule, SalesSlip } from '@/views/types'
import { axios } from '@axios'
import * as CryptoJS from 'crypto-js'

export const pay = (module_type: number) => {
    const route = useRoute()
    const merchandise = ref(<Merchandise>({}))
    const pay_module = ref(<PayModule>{})

    const return_url = new URL(window.location.href).origin + '/build/pay/result'
    const pay_url = ref(<string>(''))
    if (module_type == 2)
        pay_url.value = process.env.NOTI_URL + '/v2/online/pay/auth'
    else if (module_type == 3)
        pay_url.value = process.env.NOTI_URL + '/v2/online/pay/simple'

    const decryptQuery = () => {
        const encrypt = decodeURIComponent(route.query.e as string)
        const enc = CryptoJS.AES.decrypt(encrypt, '^^_masking_^^').toString(CryptoJS.enc.Utf8)
        const params = JSON.parse(enc)
        if (params.m && params.p) {
            pay_module.value.id = params.p
            pay_module.value.mcht_id = params.m
            pay_module.value.is_old_auth = params.o
            pay_module.value.installment = params.i
            pay_module.value.pg_id = params.g
        }
    }
    const updatePayModule = () => {
        decryptQuery()
        if (pay_module.value.mcht_id && pay_module.value.id) {
            updateMerchandise(pay_module.value.mcht_id as number)
        }
        else
            router.replace('/404')
    }

    const updateMerchandise = (mcht_id: number) => {
        axios.get('/api/v1/merchandises/' + mcht_id + '/sale-slip')
            .then(r => { Object.assign(merchandise.value, r.data as Merchandise) })
            .catch(e => { router.replace('/404') })
    }

    return {
        merchandise, pay_module, return_url, pay_url,
        updatePayModule, updateMerchandise,
    }
}

export const payResult = () => {
    const route = useRoute()
    const result_cd = route.query.result_cd as string
    const result_msg = route.query.result_msg as string
    const pmod_id   = Number(route.query.pmod_id)
    const pg_id     = Number(route.query.pg_id)
    const sale_slip = ref(<SalesSlip>({}))
    const pgs       = ref(<PayGateway[]>([]))

    const getData = async () => {
        try {
            const [response1, response2] = await Promise.all([
                axios.get('/api/v1/pay-modules/' + pmod_id + '/sale-slip'),
                axios.get('/api/v1/pay-gateways/' + pg_id + '/sale-slip')
            ]);
            sale_slip.value = {
                ...response1.data,
                ...route.query,
            }
            sale_slip.value.module_type = 2 // 인증결제
            sale_slip.value.pg_id       = pg_id
            sale_slip.value.amount      = Number(sale_slip.value.amount)
            sale_slip.value.is_cancel   = Number(route.query.is_cancel ?? false)
            sale_slip.value.trx_dttm    = (route.query.trx_dttm ?? new Date()) as string
            pgs.value = response2.data
        } catch (error) {
            console.log(error)
            throw error;
        }
    };
    return {
        sale_slip, pgs, result_cd, result_msg, getData, pmod_id
    }
}

