import router from '@/router'
import { useStore } from '@/views/services/pay-gateways/useStore'
import type { Merchandise, PayGateway, PayModule, SalesSlip } from '@/views/types'
import { axios } from '@axios'
import * as CryptoJS from 'crypto-js'

export const pay = (module_type: number) => {
    const route = useRoute()
    const merchandise = ref(<Merchandise>({}))
    const pay_module = ref(<PayModule>{})
    const { pgs } = useStore()

    const return_url = new URL(window.location.href).origin + '/pay/result'
    const pay_url = ref(<string>(''))
    if (module_type == 2)
        pay_url.value = process.env.NOTI_URL + '/v2/online/pay/auth'
    else if (module_type == 3)
        pay_url.value = process.env.NOTI_URL + '/v2/online/pay/simple'

    const updatePayModule = () => {
        const encrypt = decodeURIComponent(route.query.e as string)
        const enc = CryptoJS.AES.decrypt(encrypt, '^^_masking_^^').toString(CryptoJS.enc.Utf8)
        const params = JSON.parse(enc)

        if (params.m && params.p) {
            pay_module.value.id = params.p
            pay_module.value.mcht_id = params.m
            pay_module.value.is_old_auth = params.o
            pay_module.value.installment = params.i
            pay_module.value.pg_id = params.g
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
        pgs, updatePayModule, updateMerchandise,
    }
}

export const payResult = () => {
    const route = useRoute()
    const result_cd = route.query.result_cd as string
    const result_msg = route.query.result_msg as string
    const pmod_id = route.query.pmod_id
    const pg_id = route.query.pg_id
    const sale_slip = ref(<SalesSlip>({}))
    const pgs = ref(<PayGateway[]>([]))

    const getData = async (pmod_id: number, pg_id: number) => {
        try {
            const [response1, response2] = await Promise.all([
                axios.get('/api/v1/manager/pay-modules/' + pmod_id + '/sales-slip'),
                axios.get('/api/v1/pay-gateways/' + pg_id + '/sale-slip')
            ]);
            sale_slip.value.addr = response1.data.addr
            sale_slip.value.business_num = response1.data.business_num
            sale_slip.value.resident_num = response1.data.resident_num
            sale_slip.value.mcht_name = response1.data.mcht_name
            sale_slip.value.nick_name = response1.data.nick_name
            sale_slip.value.is_show_fee = response1.data.is_show_fee
            sale_slip.value.use_saleslip_prov = response1.data.use_saleslip_prov
            sale_slip.value.use_saleslip_sell = response1.data.use_saleslip_sell            
            pgs.value = response2.data
        } catch (error) {
            console.log(error)
            throw error;
        }
    };
    
    (async () => {
        if (pmod_id && pg_id) {
            await getData(Number(pmod_id), Number(pg_id));
        }
    })();
    
    sale_slip.value.acquirer = (route.query.acquirer ?? '') as string
    sale_slip.value.issuer = (route.query.issuer ?? '') as string
    sale_slip.value.amount = (route.query.amount ?? 0) as number
    sale_slip.value.buyer_name = (route.query.buyer_name ?? '') as string
    sale_slip.value.card_num = (route.query.card_num ?? '') as string
    sale_slip.value.item_name = (route.query.item_name ?? '') as string
    sale_slip.value.appr_num = (route.query.appr_num ?? '') as string
    sale_slip.value.installment = (route.query.installment ?? 0) as number
    sale_slip.value.trx_dttm = (route.query.trx_dttm ?? new Date()) as string
    sale_slip.value.is_cancel = Boolean(route.query.is_cancel ?? false)

    return {
        sale_slip, pgs, result_cd, result_msg
    }
}

