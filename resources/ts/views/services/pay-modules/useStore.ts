import { Header } from '@/views/headers'		
import { Searcher } from '@/views/searcher'		
import { useStore } from '@/views/services/pay-gateways/useStore'		
import type { Options, PayModule } from '@/views/types'		
import { axios, getUserLevel, pay_token, user_info } from '@axios'		
import corp from '@corp'		


export const module_types = <Options[]>([
    { id: 4, title: "빌키결제" }
])

export const installments = <Options[]>([		
    { id: 0, title: "일시불" }, { id: 2, title: "2개월" },		
    { id: 3, title: "3개월" }, { id: 4, title: "4개월" },		
    { id: 5, title: "5개월" }, { id: 6, title: "6개월" },		
    { id: 7, title: "7개월" }, { id: 8, title: "8개월" },		
    { id: 9, title: "9개월" }, { id: 10, title: "10개월" },		
    { id: 11, title: "11개월" }, { id: 12, title: "12개월" },		
])		

export const payModFilter = (all_pay_modules:PayModule[], filter:PayModule[], pmod_id:number|null) => {		
    if (all_pay_modules.length > 0) {		
        if (filter.length > 0) {		
            let item = all_pay_modules.find((item:PayModule) => item.id === pmod_id)		
            if (pmod_id != null)		
                pmod_id = null		
        }		
        else {		
            if (pmod_id != null)		
                pmod_id = null		
        }		
    }		
    return pmod_id		
}		
export const useSearchStore = defineStore('payModSearchStore', () => {    		
    const store = Searcher('merchandises/pay-modules')		
    const head  = Header('merchandises/pay-modules', '결제모듈 관리')		

    const getDefaultCol = () => {		
        return {		
            'id' : 'NO.',		
            'mcht_name' : '가맹점 상호',		
            'note' : '별칭',		
            'module_type': '모듈타입',		
        }		
    }		
    const getPaymentCols = () => {		
        const headers_4:Record<string, string> = {}		
        if(getUserLevel() >= 35) {		
            headers_4['mid'] = 'MID'		
            headers_4['tid'] = 'TID'    		
        }		
        return headers_4		
    }		
    const getETCCols = () => {		
        return {		
            'created_at' : '생성시간',		
            'updated_at' : '업데이트시간',		
        }		
    }   		

    const headers0:any = getDefaultCol()	
    const headers3:any = getPaymentCols()		
    const headers11:any = getETCCols()		

    const headers = {		
        ...headers0,		
        ...headers3,		
        ...headers11,		
    }		
    const sub_headers: any = []		
    head.getSubHeaderCol('기본 정보', headers0, sub_headers)		
    head.getSubHeaderCol('결제/취소 정보', headers3, sub_headers)		
    head.getSubHeaderCol('기타 정보', headers11, sub_headers)		

    head.sub_headers.value = sub_headers		
    head.headers.value = head.initHeader(headers, {})		
    head.flat_headers.value = head.flatten(head.headers.value)		
    const { pgs, pss } = useStore()		

    const metas = ref(<any>[])		
    if(getUserLevel() > 35) {		
        metas.value = [		
            {		
                icon: 'tabler-user-check',		
                color: 'primary',		
                title: '금월 추가된 결제모듈',		
                stats: '0',		
                percentage: 0,		
            },		
            {		
                icon: 'tabler-user-exclamation',		
                color: 'error',		
                title: '금월 감소한 결제모듈',		
                percentage: 0,		
                stats: '0',		
            },		
            {		
                icon: 'tabler-user-check',		
                color: 'primary',		
                title: '금주 추가된 결제모듈',		
                percentage: 0,		
                stats: '0',		
            },		
            {		
                icon: 'tabler-user-exclamation',		
                color: 'error',		
                title: '금주 감소한 결제모듈',		
                percentage: 0,		
                stats: '0',		
            },		
        ]		
    }		

    const exporter = async () => {		
        const keys = Object.keys(head.flat_headers.value)		
        const r = await store.get(store.base_url, { params:store.getAllDataFormat()})		
        let datas = r.data.content;		
        for (let i = 0; i < datas.length; i++) {		
            datas[i]['module_type'] = module_types.find(module_type => module_type['id'] === datas[i]['module_type'])?.title as string		
            datas[i]['pg_id'] = pgs.find(pg => pg['id'] === datas[i]['pg_id'])?.pg_name as string		
            datas[i]['ps_id'] = pss.find(ps => ps['id'] === datas[i]['ps_id'])?.name as string		

            datas[i] = head.sortAndFilterByHeader(datas[i], keys)		
        }		
        head.exportToExcel(datas)		
    }		

    return {		
        store,		
        head,		
        exporter,		
        metas,		
    }		
});		

export const defaultItemInfo =  () => {   		
    const path  = 'services/pay-modules'		
    const item  = reactive<PayModule>({		
        id: 0,		
        api_key: '',		
        sub_key: '',		
        module_type: 4,		
        note: '빌키',		
        mid: '',		
        tid: '',		
        pg_id: null,		
        ps_id: null,		
        is_old_auth: 0,	
    })		
    //카드사 필터 및 다른 필터옵션들
    return {		
        path, item		
    }		
}		

export const getAllPayModules = async(mcht_id:number|null=null, module_type:number|null=null) => {    		
    let url = '/api/v1/manager/merchandises/pay-modules/all'		
    const params = <any>({})		
    if(mcht_id || module_type) {		
        if(mcht_id)		
            params['mcht_id'] = mcht_id		
        if(module_type)		
            params['module_type'] = module_type		
        url += "?" + new URLSearchParams(params)		
    }		

    try {		
        const r = await axios.get(url)		
        return r.data.content.sort((a:PayModule, b:PayModule) => a.note.localeCompare(b.note))		
    }		
    catch (e: any) {		
        pay_token.value = ''		
        user_info.value = {}		
        location.href = '/'		
        return []		
    }		
}