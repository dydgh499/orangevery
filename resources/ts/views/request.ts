import { autoInsertPaymentModule, isFixplus } from '@/plugins/fixplus'
import router from '@/router'
import { defaultItemInfo as complaintItemInit } from '@/views/complaints/useStore'
import { defaultItemInfo as notiItemInit } from '@/views/merchandises/noti-urls/useStore'
import { defaultItemInfo as pmodItemInit } from '@/views/merchandises/pay-modules/useStore'
import { defaultItemInfo as mchtItemInit } from '@/views/merchandises/useStore'
import { defaultItemInfo as popupItemInit } from '@/views/popups/useStore'
import { defaultItemInfo as postItemInit } from '@/views/posts/useStore'
import { defaultItemInfo as salesItemInit } from '@/views/salesforces/useStore'
import { defaultItemInfo as transItemInit } from '@/views/transactions/useStore'

import { useSalesFilterStore } from '@/views/salesforces/useStore'
import { useMchtBlacklistStore } from '@/views/services/mcht-blacklists/useStore'
import type { Category, Merchandise } from '@/views/types'
import { axios, getLevelByIndex } from '@axios'
import corp from '@corp'
import { useCategoryStore } from './merchandises/shopping-mall/categories/useStore'

export const useRequestStore = defineStore('requestStore', () => {
    const alert = <any>(inject('alert'))
    const snackbar = <any>(inject('snackbar'))
    const errorHandler = <any>(inject('$errorHandler'))
    const { all_sales, mchts, sales } = useSalesFilterStore()
    const { isMchtBlackList } =  useMchtBlacklistStore()
    const { categories } = useCategoryStore()

    const clear = (back_url: string) => {
        const path = ''
        const item = {}
        if (back_url === '/merchandises') 
            return mchtItemInit()
        else if (back_url === '/merchandises/pay-modules')
            return pmodItemInit()
        else if (back_url === '/merchandises/noti-urls')
            return notiItemInit()
        else if (back_url === '/transactions')
            return transItemInit()
        else if (back_url === '/salesforces')
            return salesItemInit()
        else if (back_url === '/popups')
            return popupItemInit()
        else if (back_url === '/posts')
            return postItemInit()
        else if (back_url === '/complaints')
            return complaintItemInit()
        else
            return {path, item}
    }

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
                if (back_url === '/salesforces') {
                    const idx = getLevelByIndex(params.level)
                    all_sales[idx].push({ ...params})
                }
                else if (back_url === '/merchandises') {
                    mchts.push({ ...params})
                    mchts.sort((a:Merchandise, b:Merchandise) => a.mcht_name.localeCompare(b.mcht_name))
                    if(isFixplus()) {
                        autoInsertPaymentModule(params.id)
                    }
                }
                else if (back_url == '/salesforces/under-auto-settings') {
                    all_sales.forEach(sales => {
                        sales.forEach(sale => {
                            if (sale.id === res.data.sales_id) {
                                if (!sale.under_auto_settings)
                                    sale.under_auto_settings = []
                                else
                                    sale.under_auto_settings.push({...params})
                            }
                        })
                    })
                }
                else if (back_url == '/salesforces/sales-recommender-codes') {
                    params.id = res.data.id
                    params.recommend_code = res.data.recommend_code
                }
                else if (back_url === '/merchandises/shopping-mall/categories') {
                    categories.push({ ...params})
                    categories.sort((a:Category, b:Category) => a.category_name.localeCompare(b.category_name))
                }
                if (is_redirect) {
                    const { path, item } = clear(back_url)
                    if(path !== '')
                        Object.assign(params, item)
                }
            }
            if (is_redirect) {
                if (back_url === '/merchandises/pay-modules')
                    setTimeout(function () { router.replace('/merchandises/edit/' + res.data.mcht_id) }, 500)
                else if (back_url === '/merchandises/noti-urls')
                    setTimeout(function () { router.replace('/merchandises/edit/' + res.data.mcht_id) }, 500)
                else if (back_url === '/merchandises/regular-credit-cards')
                    setTimeout(function () { router.replace('/merchandises/edit/' + res.data.mcht_id) }, 500)            
                else if (back_url === '/salesforces/under-auto-settings')
                    setTimeout(function () { router.replace('/salesforces/edit/' + res.data.sales_id) }, 500)        
                else if (back_url === '/salesforces')
                    setTimeout(function () { router.push('/salesforces/edit/' + res.data.id) }, 500)
                else if (back_url === '/merchandises') 
                    setTimeout(function () { router.push('/merchandises/edit/' + res.data.id) }, 500)
                else if (back_url === '/services/brands') 
                    setTimeout(function () { router.push('/services/brands/edit/' + res.data.id) }, 500)
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

    const customValidFormRequest = async(base_url: string, params: any) => {
        if (Number(corp.pv_options.paid.use_mcht_blacklist) && base_url === '/merchandises') {
            let [result, blacklist] = isMchtBlackList(params)
            if(result)
                return await alert.value.show('해당 가맹점은 아래이유로 인해 블랙리스트로 등록된 가맹점입니다. 그래도 진행하시겠습니까?<br><br><b style="color:red">'+blacklist?.block_reason+'</b>')
            else
                return true
        }
        return true
    }

    const formRequest = async (base_url: string, params: any, vForm: any, is_redirect: boolean = true) => {
        const is_valid = await vForm.validate()
        const { url, reqType } = getBaseSendInfo(base_url, params.id)
        if (is_valid.valid && await customValidFormRequest(base_url, params) && await alert.value.show('정말 ' + reqType + '하시겠습니까?')) {
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

