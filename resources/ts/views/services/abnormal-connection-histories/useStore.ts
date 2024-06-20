import { Header } from '@/views/headers';
import { Searcher } from '@/views/searcher';
import type { Options } from '@/views/types';
import { allLevels, getLevelByIndex } from '@axios';

export const connection_types = <Options[]>([
    {id:0, title:'등록되지 않은 IP에서 운영자계정 로그인시도'},
    {id:1, title:'URL 조작, 파라미터 변조시도'},
    {id:2, title:'작업불가 시간대에 작업시도'},
    {id:3, title:'차단된 IP에서 접속시도'},
    {id:4, title:'허용되지 않은 작업 시도'},
    {id:5, title:'매크로 탐지'},
    {id:6, title:'해외 IP 접속'},
    {id:7, title:'세션변경'},
])

export const getLevelByChipColor = (level: number) => {
    if(level === 10)
        return 0
    else if(level >= 35)
        return getLevelByIndex(level)
    else
        return 2
}

export const useSearchStore = defineStore('abnormalConnectionHistoSearchStore', () => {
    const store = Searcher('services/abnormal-connection-histories')
    const head = Header('services/abnormal-connection-histories', '이상접속 이력')
    const headers: Record<string, string | object> = {
        'id': 'NO.',
        'connection_type': '접근타입',
        'created_at': '접근시간',
        'action': '조치사항',
        'comment': '메모사항',
        'target_level': '등급',
        'target_key': '대상',
        'target_value': '값',
        'request_ip': '접속 IP',
    }
    head.sub_headers.value = []
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.flatten(head.headers.value)

    const metas = ref([])

    const exporter = async (type: number) => {
        const keys = Object.keys(head.flat_headers.value)
        const r = await store.get(store.base_url, { params:store.getAllDataFormat()})
        let datas = r.data.content;
        for (let i = 0; i < datas.length; i++) {
            datas[i] = head.sortAndFilterByHeader(datas[i], keys)
            datas[i]['connection_type'] = connection_types.find(obj => obj.id === datas[i]['connection_type'])?.title
            datas[i]['target_level'] = allLevels().find(obj => obj.id === datas[i]['target_level'])?.title
        }
        type == 1 ? head.exportToExcel(datas) : head.exportToPdf(datas)
    }
        
    return {
        store,
        head,
        exporter,
        metas,
    }
})
