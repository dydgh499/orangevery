import corp from '@/plugins/corp';
import { Header } from '@/views/headers';
import { Searcher } from '@/views/searcher';
import type { ActivityHistory, Options } from '@/views/types';
import { useStore } from '../pay-gateways/useStore';

export const historyLevels = () => {
    const sales = []
    sales.push(<Options>({id: 35, title: '운영사'}))
    sales.push(<Options>({id: 40, title: '운영사'}))
    return sales
}

export const history_types = <Options[]>([
    { id: 0, title: "추가" }, { id: 1, title: "수정" }, { id: 3, title: "삭제" },
    { id: 4, title: "로그인" }, { id: 5, title: "변경예약" },
    { id: 6, title: "예약삭제" }, { id: 7, title: "이력삭제" },
])

export const replaceHistories = (histories: ActivityHistory[]) => {
    for (let i = 0; i < histories.length; i++) {
        histories[i].before_history_detail = replaceVariable(histories[i].before_history_detail, histories[i].history_target)
        histories[i].after_history_detail = replaceVariable(histories[i].after_history_detail, histories[i].history_target)
    }
    return histories
}

export const replaceVariable = (history_detail: any, history_target:string) => {
    const { pgs, pss, finance_vans, pg_companies } = useStore()
    const changeKeyName = () => {
        replaceIdtoName()
        return history_detail
    }
    const replaceIdtoName = () => {
        const _replaceToName = (lists: any[], key: string, name: string) => {
            if(key in history_detail) {
                const value = lists.find(obj => obj.id == history_detail[key])
                history_detail[key] = value ? value[name] : history_detail[key]
            }
        }

        _replaceToName(pgs, "PG사", 'pg_name')
        _replaceToName(finance_vans, "금융벤 ID", 'nick_name')

        if(history_target === 'PG사')
            _replaceToName(pg_companies, "PG 타입", 'name')       
    }
    return changeKeyName()
}

export const useSearchStore = defineStore('operatorHistorySearchStore', () => {
    const store = Searcher('services/activity-histories')
    const head = Header('services/activity-histories', '활동이력')
    const headers: Record<string, string | object> = {
        'user_id'       : 'NO.',
        'profile_img'   : '프로필',
        'nick_name'     : '유저명',
        'level'         : '등급',
        'add_count'     : '추가',
        'modify_count'  : '수정',
        'remove_count'  : '삭제',
        'book_change_count' : '변경예약',
        'book_remove_count' : '예약삭제',
        'history_remove_count' : '이력삭제',
        'login_count' : '로그인',
        'history_target' : '작업종류',
        'activity_s_at' : '처음',
        'activity_e_at' : '마지막',
        'detail_view'  : '상세보기',
    }
    head.sub_headers.value = [
        head.getSubHeaderFormat('작업자 정보', 'user_id', 'nick_name', 'string', 3),
        head.getSubHeaderFormat('활동개수', 'add_count', 'login_count', 'string', 7),
        head.getSubHeaderFormat('작업종류', 'history_target', 'history_target', 'string', 1),
        head.getSubHeaderFormat('작업기간', 'activity_s_at', 'activity_e_at', 'string', 3),
        head.getSubHeaderFormat('상세보기', 'detail_view', 'detail_view', 'string', 1),
    ]
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.flatten(head.headers.value)

    const exporter = async () => {
        const keys = Object.keys(head.flat_headers.value)
        const r = await store.get(store.base_url, { params:store.getAllDataFormat()})
        let datas = r.data.content;
        for (let i = 0; i < datas.length; i++) {
            datas[i]['module_type'] = history_types.find(history_type => history_type['id'] === datas[i]['history_type'])?.title as string
            datas[i] = head.sortAndFilterByHeader(datas[i], keys)
        }
        head.exportToExcel(datas)
    }
    return {
        store,
        head,
        exporter,
    }
})
