import corp from '@/plugins/corp'
import { Header } from '@/views/headers'
import { Searcher } from '@/views/searcher'
import type { NotiUrl, Options } from '@/views/types'
import { axios, getUserLevel, isAbleModiy, user_info } from '@axios'

export const noti_statuses = <Options[]>([
    { id: 0, title: "미사용" }, { id: 1, title: "사용" },
])

export const send_types = <Options[]>([
    { id: 0, title: "자동이체" }, { id: 1, title: "모아서출금" },
])

export const notiViewable = (id: number) => {
    if(corp.pv_options.paid.use_noti) {
        if(getUserLevel() >= 35)
            return true
        else if(getUserLevel() > 10)
            return isAbleModiy(id)
        else if(getUserLevel() === 10 && user_info.value.use_noti)
            return true
        else
            return false
    }
    else
        return false
}

export const useSearchStore = defineStore('NotiSearchStore', () => {    
    const store = Searcher('virtual-accounts/noti-urls')
    const head  = Header('virtual-accounts/noti-urls', '출금통지 URL 관리')

    const headers: Record<string, string> = {
        'id' : 'NO.',
        'mcht_name' : '가맹점 상호',
        'send_url' : '전송 URL',
        'send_type' : '발송타입',
        'noti_status' : '사용여부',
        'fin_id': '이체 모듈 타입',
        'fin_trx_delay': '출금 딜레이',
        'withdraw_limit_type': '이체 금지타입',
        'withdraw_business_limit': '일 출금한도(영업일)',
        'withdraw_holiday_limit': '일 출금한도(휴무일)',
        'created_at' : '생성시간',
        'updated_at' : '업데이트시간',
    }

    head.sub_headers.value = []
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.flatten(head.headers.value)

    const exporter = async () => {
        const r = await store.get(store.base_url, { params:store.getAllDataFormat()})
        let datas = r.data.content;
        for (let i = 0; i < datas.length; i++) {
            datas[i]['noti_status'] = noti_statuses.find(status => status['id'] === datas[i]['noti_status'])?.title as string
        }
        head.exportToExcel(datas)
    }
    return {
        store,
        head,
        exporter,
    }
});

export const defaultItemInfo =  () => {   
    const path  = 'merchandises/virtual-account-noti-urls'
    const item  = reactive<NotiUrl>({
        id: 0,
        mcht_id: null,
        send_url: '',
        note: '비고(별칭)',
        noti_status: 1,
        pmod_id: -1,
        send_type: 0
    })
    //카드사 필터 및 다른 필터옵션들
    return {
        path, item
    }
}


export const getAllNotiUrls = async(mcht_id:number|null=null) => {
    const params:any = {
        page: 1,
        page_size: 999,
    }
    if(mcht_id)
        params['mcht_id'] = mcht_id    
    const sub_query = Object.keys(params).map(key => `${encodeURIComponent(key)}=${encodeURIComponent(params[key])}`).join('&')
    const url = '/api/v1/manager/merchandises/virtual-account-noti-urls?'+sub_query
    const r = await axios.get(url)
    return r.data.content
}
