import { Merchandise, MultipleHandPay, PayModule, SalesSlip } from "@/views/types"
import { axios } from '@axios'
import { cloneDeep } from 'lodash'
import { useDisplay } from 'vuetify'

export const multipleHandPaySequence = () => {
    const noti_temp = ref('')
    const full_processes = ref<any[]>([])
    const hand_pay_info = ref(<MultipleHandPay>({}))
    const hand_pay_infos = ref(<MultipleHandPay[]>([]))
    const valid_total_amount = ref(0)
    const snackbar = <any>(inject('snackbar'))
    const alert = <any>(inject('alert'))
    const route = useRoute()

    const purchaseStart = async (total_amount: number, merchandise: Merchandise) => {
        for (let i = 0; i < hand_pay_infos.value.length; i++) {
            if (hand_pay_infos.value[i].status_color != 'success') {
                snackbar.value.show((i+1) + '번째 결제정보를 확인해주세요.', 'error')
                return
            }
        }
        if (await alert.value.show("총 " + total_amount.toLocaleString() + '원을 결제하시겠습니까?')) {
            snackbar.value.show('다중결제를 시작합니다...', 'primary')
            trxProcess()
            setProcessTableWidth()
            await trxResult(merchandise)
            setProcessTableWidth()
            await cxlProcess()
            setProcessTableWidth()
        }
    }

    // level 0 다중결제 파라미터 구성
    const getProcessObject = () => {
        return {
            'trx_process': <Promise<any>>({}),
            'trx_result': <SalesSlip>({}),
            'cxl_process': null,
            'cxl_result': <SalesSlip>({}),
        }
    }

    // level 1 결제
    const trxProcess = () => {
        full_processes.value = []
        for (let i = 0; i < hand_pay_infos.value.length; i++) {
            full_processes.value.push(getProcessObject())
            full_processes.value[i].trx_process = pay(i)
        }
    }

    // level 2 결제 결과 처리
    const trxResult = async (merchandise: Merchandise) => {
        const results = await Promise.all(full_processes.value.map(item => item.trx_process))
        for (let i = 0; i < results.length; i++) {
            full_processes.value[i].trx_result = {
                ...results[i],
                ...merchandise
            }
            full_processes.value[i].trx_result.module_type = 1 // 수기결제
        }
        snackbar.value.show('결제가 완료되었습니다.', 'success')
    }

    // level 3 성공건 취소 처리 (실패 존재할 경우만)
    const cxlProcess = async () => {
        let fail_find = false
        for (let i = 0; i < full_processes.value.length; i++) {
            if (full_processes.value[i].trx_result.result_cd !== "0000") {
                fail_find = true
                break
            }
        }
        if (fail_find) {
            snackbar.value.show('결제실패건을 발견하였으므로 성공건들을 모두 취소합니다.', 'error')
            for (let i = 0; i < full_processes.value.length; i++) {
                if (full_processes.value[i].trx_result.result_cd === "0000")
                    full_processes.value[i].cxl_process = cancel(i)
                else
                    full_processes.value[i].cxl_process = null
            }
            await cxlResult()
        }
    }

    // level 4 성공건 취소 결과 처리
    const cxlResult = async () => {
        const results = await Promise.all(full_processes.value.map(item => item.cxl_process))
        for (let i = 0; i < results.length; i++) {
            if (results[i] != null) {
                full_processes.value[i].cxl_result = cloneDeep(full_processes.value[i].trx_result)
                Object.assign(full_processes.value[i].cxl_result, results[i])
            }
        }
    }

    const pay = (index: number) => {
        return new Promise((resolve, reject) => {
            axios.post('/api/v1/transactions/hand-pay', {
                ...hand_pay_info.value,
                ...hand_pay_infos.value[index]
            }).then(r => {
                resolve({
                    ...r.data,
                    result_cd: "0000",
                    result_msg: "결제 성공",
                })
            }).catch(e => {
                resolve({
                    result_cd: e.response.data.code,
                    result_msg: e.response.data.message
                })
            })
        })
    }

    const cancel = (index: number) => {
        return new Promise((resolve, reject) => {
            axios.post('/api/v1/transactions/pay-cancel', {
                temp: hand_pay_info.value.temp,
                pmod_id: hand_pay_infos.value[index].pmod_id,
                amount: hand_pay_infos.value[index].amount,
                trx_id: full_processes.value[index].trx_result.trx_id,
                only: true,
            }).then(r => {
                resolve({
                    ...r.data,
                    result_cd: "0000",
                    result_msg: "취소 성공",
                })
            }).catch(e => {
                resolve({
                    result_cd: e.response.data.code,
                    result_msg: e.response.data.message
                })
            })
        })
    }

    const setProcessTableWidth = () => {
        if(window.innerWidth < 780) {
            const table = document.getElementById('process-table')
            if(table)
                table.style['width'] = window.innerWidth + 'px'
        }
    }

    const init = () => {
        const urlParams = new URLSearchParams(window.location.search)
        if(route.query.temp) {
            try {
                const temp = decodeURIComponent(route.query.temp as string)
                const base64 = atob(temp)
                const plain = JSON.parse(base64)
                if(plain.order_num !== undefined && plain.total_amount !== undefined) {
                    noti_temp.value = plain.order_num as string
                    valid_total_amount.value = plain.total_amount as number
                }
                else {
                    snackbar.value.show('order_num 또는 total_amount가 존재하지 않습니다.', 'error')
                }
            }
            catch(e) {
                snackbar.value.show('base64 decode 또는 json parse에 실패하였습니다.', 'error')
            }
        }
        else
            snackbar.value.show('temp 파라미터가 존재하지 않습니다.', 'error')

        hand_pay_info.value = (<MultipleHandPay>({
            yymm: '',
            card_num: '',
            installment: 0,
            item_name: urlParams.get('item_name') || '',
            buyer_name: urlParams.get('buyer_name') || '',
            buyer_phone: urlParams.get('phone_num') || '',
            temp: noti_temp.value
        }))
    }

    const addNewHandPay = (pay_module: PayModule) => {
        const { mobile } = useDisplay()
        const urlParams = new URLSearchParams(window.location.search)
        hand_pay_infos.value.push(<MultipleHandPay><unknown>({
            auth_num: '',
            card_pw: '',
            yymm: String(''),
            card_num: String(''),
            installment: Number(0),
            amount: Number(urlParams.get('amount') || ''),
            pmod_id: pay_module.id,
            is_old_auth: pay_module.is_old_auth,
            ord_num: pay_module.id + "H" + Date.now().toString().substr(0, 10),
            user_agent: mobile.value ? "WM" : "WP",
        }))
    }

    watchEffect(() => {
        setProcessTableWidth()
    })
    
    return {
        hand_pay_info,
        hand_pay_infos,
        full_processes,
        valid_total_amount,
        purchaseStart,
        addNewHandPay,
        init,
    }
}
