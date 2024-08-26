import { Header } from '@/views/headers'
import { Searcher } from '@/views/searcher'
import type { NotiUrl, Options } from '@/views/types'
import { axios } from '@axios'

export const noti_statuses = <Options[]>([
    { id: 0, title: "미사용" }, { id: 1, title: "사용" },
])

export const useSearchStore = defineStore('NotiSearchStore', () => {    
    const store = Searcher('merchandises/noti-urls')
    const head  = Header('merchandises/noti-urls', '노티 URL 관리')

    const headers: Record<string, string> = {
        'id' : 'NO.',
        'note' : '별칭',
        'mcht_name' : '가맹점 상호',
        'send_url' : '전송 URL',
        'noti_status' : '사용여부',
        'created_at' : '생성시간',
        'updated_at' : '업데이트시간',
    }
    head.sub_headers.value = []
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.flatten(head.headers.value)

    const exporter = async (type: number) => {
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
    const path  = 'merchandises/noti-urls'
    const item  = reactive<NotiUrl>({
        id: 0,
        mcht_id: 0,
        send_url: '',
        note: '비고(별칭)',
        noti_status: 1,
        pmod_id: -1
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
    const url = '/api/v1/manager/merchandises/noti-urls?'+sub_query
    const r = await axios.get(url)
    return r.data.content
}
