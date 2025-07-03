import router from '@/router'
import { axios } from '@axios'
import { useStore } from './services/options/useStore'

export const useRequestStore = defineStore('requestStore', () => {
    const alert = <any>(inject('alert'))
    const snackbar = <any>(inject('snackbar'))
    const errorHandler = <any>(inject('$errorHandler'))
    const { pss, pgs, bill_keys, pay_modules, finance_vans} = useStore()
    
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
                if(back_url === 'services/pay-gateways')
                    pgs.push({ ...params})
                else if(back_url === 'services/pay-sections')
                    pss.push({ ...params})
                else if(back_url === 'services/finance-vans')
                    finance_vans.push({ ...params})
                else if(back_url === 'pays/bill-keys')
                    bill_keys.push({ ...params})
                else if(back_url === 'pays/pay-modules')
                    pay_modules.push({ ...params})
            }
            if (is_redirect) {
                if (back_url === '/services/brands') 
                    setTimeout(function () { }, 500)
                else
                    setTimeout(function () { router.replace(back_url) }, 1000)
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
        else if (is_valid.valid == false) {
            const message = is_valid.errors.map((error: any) => error.errorMessages).join('<br>')
            snackbar.value.show(reqType + `조건에 맞지않는 필드가 존재합니다.<br><br>${message}`, 'error')
        }
    }

    const update = async (base_url: string, params: any, vForm: any, is_redirect: boolean = true) => {
        const is_valid = await vForm.validate()
        const { url, reqType } = getBaseSendInfo(base_url, params.id)
        if (is_valid.valid && await alert.value.show('정말 ' + reqType + '하시겠습니까?')) {
            const res = await request({ url: url, data: params, method: params.id !== 0 ? 'put' : 'post' })
            afterTreatment(base_url, is_redirect, params, res)
            return res
        }
        else if (is_valid.valid == false) {            
            const message = is_valid.errors.map((error: any) => error.errorMessages).join('<br>')
            snackbar.value.show(reqType + `조건에 맞지않는 필드가 존재합니다.<br><br>${message}`, 'error')
        }
        return undefined
    }

    const remove = async (base_url: string, params: any, is_redirect: boolean = true) => {
        const { url, reqType } = getBaseSendInfo(base_url, params.id)
        if (await alert.value.show('정말 삭제하시겠습니까?')) {
            const res = await request({ url: url, data: params, method: 'delete' })
            deleteTreatment(base_url, is_redirect, params, res)
            return res
        }
        return undefined
    }

    const setNullRemove = (objects: any[]) => {
        // findIndex는 배열에 1개만 존재할 경우 참조가 되지 않음
        for (let i = objects.length - 1; i >= 0; i--) {
            if (objects[i].id === -1) {
                objects.splice(i, 1);
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

