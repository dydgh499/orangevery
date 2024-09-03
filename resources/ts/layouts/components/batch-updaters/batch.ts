import { useRequestStore } from '@/views/request'
import { axios, user_info } from '@axios'
import corp from '@corp'

export const batch = (emits:any, batch_name: string, batch_type: string) => {
    const selected_idxs     = ref(<number[]>([])) 
    const selected_level    = ref()
    const selected_sales_id = ref()
    const selected_all      = ref(0)

    const feeBookDialog         = ref()
    const checkAgreeDialog      = ref()
    const passwordAuthDialog    = ref()
    
    const store = <any>(inject('store'))
    const alert = <any>(inject('alert'))
    const snackbar = <any>(inject('snackbar'))
    const errorHandler = <any>(inject('$errorHandler'))
    const formatDate = <any>(inject('$formatDate'))
    const { request } = useRequestStore()

    const getCommonParams = async (params: any, method: string, type: string) => {
        if (selected_idxs.value.length || (selected_sales_id.value && selected_level.value) || selected_all.value) {
            if(selected_all.value) {
                const agree = await checkAgreeDialog.value.show(store.pagenation.total_count, method, batch_name)
                if (agree === false)
                    return [false, params]
                else {
                    if(corp.pv_options.paid.use_head_office_withdraw) {
                        let phone_num = user_info.value.phone_num
                        if(phone_num) {
                            phone_num = phone_num.replaceAll(' ', '').replaceAll('-', '')
                            const token = await passwordAuthDialog.value.show(phone_num)
                            if(token !== '') {
                                
                            }
                            else
                                return [false, params]
                        }
                        else {
                            snackbar.value.show('로그인한 계정의 휴대폰번호를 업데이트한 후 다시 시도해주세요.', 'error')
                            return [false, params]
                        }
                    }
                }
            }
            let message = `정말 ${type}${method}하시겠습니까?`;
            if(params['apply_type'] === 1)
                message += `<br><b>${params['apply_dt']}${params['apply_dt'].length > 10 ? '부터' : '일 자정에'}</b> 적용될 예정입니다.`
    
            if (await alert.value.show(message)) {
                Object.assign(params, { 
                    selected_idxs: selected_idxs.value,
                    selected_sales_id: selected_sales_id.value,
                    selected_level: selected_level.value, 
                    selected_all: selected_all.value,
                })
                if(selected_all.value) {
                    Object.assign(params, {
                        filter: store.params
                    })
                    params.filter.search = (document.getElementById('search') as HTMLInputElement)?.value
                    params.total_selected_count = store.pagenation.total_count
                }
                return [true, params]
            }
            return [false, params]
        }
        else {
            snackbar.value.show(batch_name+'을 1개이상 선택해주세요.', 'error')
            return [false, params]
        }
    }
    
    const batchRemove = async () => {
        const [result, params] = await getCommonParams({}, '삭제', '일괄')
        if(result) {
            const r = await request({ url: `/api/v1/manager/${batch_type}/batch-updaters/remove`, method: 'delete', data: params }, true)
            emits('update:select_idxs', [])
        }
    }
    
    const post = async (page: string, _params: any, apply_type: number) => {
        try {
            const apply_dt = await getApplyDt(page, apply_type)
            if(apply_dt !== '') {
                _params['apply_dt'] = apply_dt
                _params['apply_type'] = apply_type
                const [result, params] = await getCommonParams(_params, '적용', apply_type ? '예약' : '일괄')
                if(result) {
                    const r = await axios.post(`/api/v1/manager/${batch_type}/batch-updaters/` + page, params)
                    snackbar.value.show(r.data.message, 'success')
                    emits('update:select_idxs', [])
                }
            }
        }
        catch (e: any) {
            snackbar.value.show(e.response.data.message, 'error')
            const r = errorHandler(e)
        }
    }

    const getApplyDt = async (page: string, type: number) => {
        let apply_dt = ''
        if(type === 0)
            apply_dt = formatDate(new Date)
        else 
            apply_dt = await feeBookDialog.value.show(page.includes('set-fee') ? false : true)
        return apply_dt
    }
    return {
        selected_idxs,
        selected_sales_id,
        selected_level,
        selected_all,
        feeBookDialog,
        checkAgreeDialog,
        passwordAuthDialog,
        post,
        batchRemove
    }
}
