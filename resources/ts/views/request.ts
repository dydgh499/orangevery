import router from '@/router'
import { useSalesFilterStore } from '@/views/salesforces/useStore'
import type { Merchandise } from '@/views/types'
import { axios, getLevelByIndex } from '@axios'
import corp from '@corp'

export const useRequestStore = defineStore('requestStore', () => {
    const alert = <any>(inject('alert'))
    const snackbar = <any>(inject('snackbar'))
    const errorHandler = <any>(inject('$errorHandler'))
    const { all_sales, mchts } = useSalesFilterStore()

    const deleteTreatment = (back_url: string, is_redirect: boolean, params: any, res: any) => {
        if (res.status === 201) {
            if (is_redirect)
                setTimeout(function () { router.push(back_url) }, 1000)
            else
                params.id = -1
        }
    }
    const afterTreatment = (back_url: string, is_redirect: boolean, params: any, res: any) => {
        if (res.status === 201) {
            if(params.id == 0) {
                params.id = res.data.id
                if (back_url == 'salesforces') {
                    const idx = getLevelByIndex(params.level)
                    all_sales[idx].push(params)
                }
                else if (back_url == '/merchandises') {
                    mchts.push(params)
                    mchts.sort((a:Merchandise, b:Merchandise) => a.mcht_name.localeCompare(b.mcht_name))
                }
                else if (back_url == '/salesforces/under-auto-settings') {
                    all_sales.forEach(sales => {
                        sales.forEach(sale => {
                            if (sale.id === res.data.sales_id) {
                                if (!sale.under_auto_settings)
                                    sale.under_auto_settings = []
                                else
                                    sale.under_auto_settings.push(params)
                            }
                        })
                    })
                }
            }
            if (is_redirect) {
                if (back_url == '/merchandises/pay-modules')
                    setTimeout(function () { router.push('/merchandises/edit/' + res.data.mcht_id) }, 500)
                else if (back_url == '/merchandises/noti-urls')
                    setTimeout(function () { router.push('/merchandises/edit/' + res.data.mcht_id) }, 500)
                else if (back_url == '/merchandises/regular-credit-cards')
                    setTimeout(function () { router.push('/merchandises/edit/' + res.data.mcht_id) }, 500)            
                else if (back_url == '/salesforces/under-auto-settings')
                    setTimeout(function () { router.push('/salesforces/edit/' + res.data.sales_id) }, 500)        
                else if (back_url == '/merchandises')
                    setTimeout(function () { router.push('/merchandises/edit/' + res.data.id) }, 500)
                else
                    setTimeout(function () { router.back() }, 1000)
            }
        }
    }

    const request = async (params: any, use_snackbar: boolean = true) => {
        try {
            const res = await axios(params)
            if(use_snackbar)
                snackbar.value.show('성공하였습니다.', 'success')
            return res
        }
        catch (e: any) {
            if(use_snackbar)
                snackbar.value.show(e.response.data.message, 'error')
            return errorHandler(e)
        }
    }

    const getFormParams = (formData: FormData, data: any, parentKey = '') => {
        for (const key in data) {
            if (data.hasOwnProperty(key)) {
                const value = data[key];
                const formKey = parentKey ? `${parentKey}[${key}]` : key;

                if (value instanceof File)  // 파일 객체인 경우 직접 추가
                    formData.append(formKey, value);
                else if (value instanceof Date)// 날짜 객체인 경우 ISO 8601 형식으로 변환하여 추가                    
                    formData.append(formKey, value.toISOString());
                else if (value instanceof Object && value !== null) // 객체인 경우 재귀 호출을 통해 중첩된 속성 처리                   
                    getFormParams(formData, value, formKey);
                else if (value != null)// 기본 타입인 경우 문자열로 변환하여 추가
                    formData.append(formKey, value);
            }
        }
    }

    const getBaseSendInfo = (base_url: string, id: number) => {
        let url = '/api/v1/manager' + base_url
        url += id ? "/" + id : ""
        let reqType = id != 0 ? '수정' : '생성'
        return { url, reqType }
    }

    const formRequest = async (base_url: string, params: any, vForm: any, is_redirect: boolean = true) => {
        const is_valid = await vForm.validate()
        const { url, reqType } = getBaseSendInfo(base_url, params.id)
        if (is_valid.valid && await alert.value.show('정말 ' + reqType + '하시겠습니까?')) {
            const form_data = new FormData()
            getFormParams(form_data, params)
            const res = await request({
                url: url,
                data: form_data,
                method: params.id != 0 ? 'put' : 'post',
                headers: { 'Content-Type': "multipart/form-data" }
            })
            afterTreatment(base_url, is_redirect, params, res)
        }
        else if (is_valid.valid == false)
            snackbar.value.show(reqType + '조건에 맞지않는 필드가 존재합니다.', 'warning')
    }

    const update = async (base_url: string, params: any, vForm: any, is_redirect: boolean = true) => {
        if(base_url == 'merchandises/pay-modules') {
            params.use_mid_duplicate = Number(corp.pv_options.free.use_mid_duplicate);
            params.use_tid_duplicate = Number(corp.pv_options.free.use_tid_duplicate);
        }

        //payment moduels use_tid_duplicate
        const is_valid = await vForm.validate()
        const { url, reqType } = getBaseSendInfo(base_url, params.id)
        if (is_valid.valid && await alert.value.show('정말 ' + reqType + '하시겠습니까?')) {
            const res = await request({ url: url, data: params, method: params.id != 0 ? 'put' : 'post' })
            afterTreatment(base_url, is_redirect, params, res)
        }
        else if (is_valid.valid == false)
            snackbar.value.show(reqType + '조건에 맞지않는 필드가 존재합니다.', 'warning')
    }

    const remove = async (base_url: string, params: any, is_redirect: boolean = true) => {
        const { url, reqType } = getBaseSendInfo(base_url, params.id)
        if (await alert.value.show('정말 삭제하시겠습니까?')) {
            const res = await request({ url: url, method: 'delete' })
            deleteTreatment(base_url, is_redirect, params, res)
        }
    }

    const setNullRemove = (objects: any[]) => {
        // findIndex는 배열에 1개만 존재할 경우 참조가 되지 않음
        for (let i = 0; i < objects.length; i++) {
            if (objects[i].id === -1) {
                objects.splice(i, 1)
                return
            }
        }
    }

    const setOneObject = (base_url: string, id: number, obj: object) => {
        const { url, reqType } = getBaseSendInfo(base_url, id)
        axios.get(url)
            .then(r => { Object.assign(obj, r.data) })
            .catch(e => {
                snackbar.value.show(e.response.data.message, 'error')
                const r = errorHandler(e)
            })
    }

    const post = async (url: string, params: any, use_snackbar :boolean = true) => {
        return await request({ url: url, data: params, method: 'post' }, use_snackbar)
    }

    const get = async (url: string, params: any = {}) => {
        try {
            return await axios.get(url, params)
        }
        catch (e: any) {
            snackbar.value.show(e.response.data.message, 'error')
            return errorHandler(e)
        }
    }

    const getPromises = (selected: number[], items: any[], url: string, method: string, params: any = {}) => {
        const promises = []
        for (let i = 0; i < selected.length; i++) {
            const item: any = items.find(obj => obj['id'] === selected[i])
            if (item) {
                promises.push(axios({
                    url: url,
                    method: method,
                    params: params,
                }))
            }
        }
        return promises
    }
    return { formRequest, update, remove, setOneObject, request, post, get, getPromises, setNullRemove }
})

