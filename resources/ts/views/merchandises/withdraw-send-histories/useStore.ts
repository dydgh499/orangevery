import { Header } from '@/views/headers'
import { Searcher } from '@/views/searcher'
import type { NotiUrl, Options } from '@/views/types'
import { axios } from '@axios'

export const noti_statuses = <Options[]>([
    { id: 0, title: "미사용" }, { id: 1, title: "사용" },
])

export const useSearchStore = defineStore('NotiSearchStore', () => {    
    const store = Searcher('merchandises/withdraw-send-histories')
    const head  = Header('merchandises/withdraw-send-histories', '출금통지 이력')
    const getSendResponseHeader = () => {
        return {
            'id': 'NO.',
            'http_code': '응답코드',
            'message': '응답내용',
            'retry_count': '재시도 회수',    
        }
    }

    const getMerchandiseHeader = () => {
        return {
            'mcht_name': '가맹점 상호',
        }
    }

    const getWithdrawHeader = () => {
        return {
            'send_type': '발송 타입',
            'amount' : '출금금액',
        }
    }

    const getEtcCols = () => {
        return {
            'extra_col': '더보기'
        }
    }

    const headers: Record<string, string> = {
        ...getSendResponseHeader(),
        ...getMerchandiseHeader(),
        ...getWithdrawHeader(),
        ...getEtcCols(),
    }
    const sub_headers: any = []
    head.getSubHeaderCol('노티결과', getSendResponseHeader(), sub_headers)
    head.getSubHeaderCol('가맹점 정보', getMerchandiseHeader(), sub_headers)
    head.getSubHeaderCol('출금 정보', getWithdrawHeader(), sub_headers)
    head.getSubHeaderCol('기타 정보', getEtcCols(), sub_headers)

    head.sub_headers.value = sub_headers
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.flatten(head.headers.value)

    const exporter = async () => {
        const r = await store.get(store.base_url, { params:store.getAllDataFormat()})
        let datas = r.data.content;
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
    const url = '/api/v1/manager/merchandises/noti-urls?'+sub_query
    const r = await axios.get(url)
    return r.data.content
}
