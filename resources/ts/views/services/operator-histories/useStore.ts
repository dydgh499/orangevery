import { Header } from '@/views/headers';
import { Searcher } from '@/views/searcher';
import type { Options } from '@/views/types';

export const history_types = <Options[]>([
    { id: 0, title: "추가" }, { id: 1, title: "수정" }, { id: 3, title: "삭제" },
    { id: 4, title: "로그인" }, { id: 5, title: "변경예약" },
    { id: 6, title: "예약삭제" }, { id: 7, title: "이력삭제" },
])

export const useSearchStore = defineStore('operatorHistorySearchStore', () => {
    const store = Searcher('services/operator-histories')
    const head = Header('services/operator-histories', '운영자 활동이력')
    const headers: Record<string, string | object> = {
        'oper_id'   : 'NO.',
        'profile_img'   : '프로필',
        'nick_name'     : '운영자명',
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
        head.getSubHeaderFormat('작업자 정보', 'oper_id', 'nick_name', 'string', 3),
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
