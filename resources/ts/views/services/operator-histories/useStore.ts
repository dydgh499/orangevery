import { Header } from '@/views/headers';
import { Searcher } from '@/views/searcher';
import type { Options } from '@/views/types';

export const history_types = <Options[]>([
    { id: 0, title: "추가" }, { id: 1, title: "수정" },
    { id: 2, title: "조회" }, { id: 3, title: "삭제" },
    { id: 4, title: "로그인" }, { id: 5, title: "변경예약" },
    { id: 6, title: "예약삭제" }, { id: 7, title: "이력삭제" },
])

export const useSearchStore = defineStore('operatorHistorySearchStore', () => {
    const store = Searcher('services/operator-histories')
    const head = Header('services/operator-histories', '운영자 활동이력')
    const headers: Record<string, string | object> = {
        'id': 'NO.',
        'profile_img'   : '프로필',
        'nick_name'     : '운영자명',
        'history_target': '활동종류',
        'history_title' : '적용대상',
        'history_type'  : '활동타입',
        'created_at'    : '생성시간',
        'extra_col'     : '더보기',
    }
    head.main_headers.value = [];
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.setFlattenHeaders()

    const exporter = async (type: number) => {
        const keys = Object.keys(headers);
        const r = await store.get(store.base_url, { params:store.getAllDataFormat()})
        let datas = r.data.content;
        for (let i = 0; i < datas.length; i++) {
            datas[i]['module_type'] = history_types.find(history_type => history_type['id'] === datas[i]['history_type'])?.title as string
            datas[i] = head.sortAndFilterByHeader(datas[i], keys)
        }
        type == 1 ? head.exportToExcel(datas) : head.exportToPdf(datas)
    }
    return {
        store,
        head,
        exporter,
    }
})
