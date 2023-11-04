import router from '@/router'
import { useStore } from '@/views/services/pay-gateways/useStore'
import type { Merchandise, PayGateway, SalesSlip } from '@/views/types'
import { axios } from '@axios'
import * as CryptoJS from 'crypto-js'

export const pay = (module_type: number) => {
    const pg_id = ref(<number>(0))
    const pmod_id = ref(<number>(0))

    const is_old_auth = ref(<boolean>(false))
    const installment = ref(<number>(0))
    const merchandise = ref(<Merchandise>({}))

    const { pgs } = useStore()
    const pay_url = ref(<string>(''))
    const return_url = new URL(window.location.href).origin + '/pay/result'

    const getSalesSlipInfo = async () => {
        const urlParams = new URLSearchParams(window.location.search)
        const encrypt = decodeURIComponent(urlParams.get('e') || '')
        const enc = CryptoJS.AES.decrypt(encrypt, '^^_masking_^^').toString(CryptoJS.enc.Utf8)
        const params = JSON.parse(enc)

        if (params.m != null && params.p != null && params.m != 0 && params.p != 0) {
            pmod_id.value = params.p
            is_old_auth.value = params.o
            installment.value = params.i
            pg_id.value = params.g

            axios.get('/api/v1/merchandises/' + params.m + '/sale-slip')
                .then(r => { Object.assign(merchandise.value, r.data as Merchandise) })
                .catch(e => { router.replace('/404') })
        }
        else
            router.replace('/404')
    }
    watchEffect(() => {
        if (pgs.length > 0 && pg_id.value) {
            const pg: PayGateway = pgs.find(obj => obj.id === pg_id.value)
            if (pg) {
                let type = ''
                if (module_type == 2)
                    type = 'auth'
                else if (module_type == 3)
                    type = 'simple'
                pay_url.value = process.env.NOTI_URL + '/v2/online/pay/' + type
            }
        }
    })
    return {
        pg_id, pmod_id, is_old_auth,
        installment, merchandise, return_url,
        pgs, getSalesSlipInfo, pay_url,
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

