import router from '@/router'
import { axios } from '@axios'

export const useRequestStore = defineStore('requestStore', () => {
    const alert = <any>(inject('alert'))
    const snackbar = <any>(inject('snackbar'))
    const errorHandler = <any>(inject('$errorHandler'))
    
    const request = async (back_url: string, params: any, is_redirect: boolean) => {
        try {
            const res = await axios(params);
            snackbar.value.show('성공하였습니다.', 'success')
            if (is_redirect) {
                if(back_url == '/merchandises/pay-modules')
                    setTimeout(function () { router.push('/merchandises/edit/'+res.data.mcht_id) }, 500)
                else if(back_url == '/merchandises/noti-urls')
                    setTimeout(function () { router.push('/merchandises/edit/'+res.data.mcht_id) }, 500)
                else if(back_url == '/salesforces/under-auto-settings')
                    setTimeout(function () { router.push('/salesforces/edit/'+res.data.sales_id) }, 500)
                else if(back_url == '/merchandises')
                    setTimeout(function () { router.push('/merchandises/edit/'+res.data.id) }, 500)
                else
                    setTimeout(function () { router.push(back_url) }, 1000)
            }
            else
                location.reload()
        }
        catch (e: any) {
            snackbar.value.show(e.response.data.message, 'error')
            const r = errorHandler(e)
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

    const formRequest = async (base_url: string, id: number, params: any, vForm: any, is_redirect: boolean = true) => {
        const is_valid = await vForm.validate();
        const { url, reqType } = getBaseSendInfo(base_url, id)
        if (is_valid.valid && await alert.value.show('정말 ' + reqType + '하시겠습니까?')) {
            const formData = new FormData();
            getFormParams(formData, params)
            await request(base_url, {
                url: url,
                data: formData,
                method: id != 0 ? 'put' : 'post',
                headers: { 'Content-Type': "multipart/form-data", }
            }, is_redirect)
        }
        else if (is_valid.valid == false)
            snackbar.value.show(reqType + '조건에 맞지않는 필드가 존재합니다.', 'warning')
    }

    const update = async (base_url: string, id: number, params: any, vForm: any, is_redirect: boolean = true) => {
        const is_valid = await vForm.validate();
        const { url, reqType } = getBaseSendInfo(base_url, id)
        if (is_valid.valid && await alert.value.show('정말 ' + reqType + '하시겠습니까?')) {
            await request(base_url, {
                url: url,
                data: params,
                method: id != 0 ? 'put' : 'post',
            }, is_redirect)
        }
        else if (is_valid.valid == false)
            snackbar.value.show(reqType + '조건에 맞지않는 필드가 존재합니다.', 'warning')

    }

    const remove = async (base_url: string, id: number, is_redirect: boolean = true) => {
        const { url, reqType } = getBaseSendInfo(base_url, id)
        if (await alert.value.show('정말 삭제하시겠습니까?')) {
            const params = {
                url: url,
                method: 'delete'
            };
            await request(base_url, params, is_redirect)
        }
    }
    const setOneObject = (base_url: string, id: number, obj: object) => {
        const { url, reqType } = getBaseSendInfo(base_url, id)
        axios.get(url)
            .then(r => { Object.assign(obj, r.data); })
            .catch(e => {
                snackbar.value.show(e.response.data.message, 'error')
                const r = errorHandler(e)
            })
    }

    const post = async(url: string, params: any) => {
        try {
            return await axios.post(url, params)
        }
        catch (e: any) {
            snackbar.value.show(e.response.data.message, 'error')
            return errorHandler(e)
        }
    }

    const get = async(url: string, params: any = {}) => {
        try {
            return await axios.get(url, params)
        }
        catch (e: any) {
            snackbar.value.show(e.response.data.message, 'error')
            return errorHandler(e)
        }
    }

    const getPromises = (selected:number[], items:any[], url:string, method: string, params:any = {}) => {
        const promises = []
        for (let i = 0; i < selected.length; i++) {
            const item:any = items.find(obj => obj['id'] === selected[i])
            if(item) {
                promises.push(axios({
                    url: url,
                    method: method,
                    params: params,
                }))
            }
        }
        return promises
    }
    return { formRequest, update, remove, setOneObject, request, post, get, getPromises }
})

