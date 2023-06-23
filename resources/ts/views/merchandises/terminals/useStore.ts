import { Header } from '@/views/headers';
import { Searcher } from '@/views/searcher';

export const useSearchStore = defineStore('terminalSearchStore', () => {    
    const store = Searcher('merchandises/terminals')
    const head  = Header('merchandises/terminals', '단말기 관리')
    const setHeaders = () => {
        const headers: Record<string, string> = {
            'id' : 'NO.',
            'mcht_name' : '가맹점 상호',
            'note' : '별칭',
            'module_type' : '모듈타입',
            'pg_id' : 'PG사명',
            'ps_id' : '구간',
            'settle_type' : '정산일',
            'mid' : 'MID',
            'tid' : 'TID',
            'installment' : '할부한도',
            'terminal_id' : '단말기 타입',
            'serial_num' : '시리얼 번호',
            'comm_settle_fee' : '통신비',
            'comm_settle_type' : '통신비 정산일',
            'comm_calc_level' : '통신비 정산주체',
            'under_sales_amt' : '매출미달 차감금',
            'begin_dt' : '개통일',
            'ship_out_dt' : '출고일',
            'ship_out_stat' : '출고상태',
            'created_at' : '생성시간',
            'updated_at' : '업데이트시간',
        }
        head.main_headers.value = [];
        head.headers.value = head.initHeader(headers, {})
        head.flat_headers.value = head.setFlattenHeaders()
    }
    const exporter = async (type: number) => {      
        const r = await store.get(store.getAllDataFormat())
        let convert = r.data.content;
        for (let i = 0; i <convert.length; i++) 
        {
            
        }
        type == 1 ? head.exportToExcel(convert) : head.exportToPdf(convert)        
    }
    setHeaders()
    return {
        store,
        head,
        exporter,
    }
});
