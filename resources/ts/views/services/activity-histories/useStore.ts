import { salesLevels } from '@/plugins/axios';
import corp from '@/plugins/corp';
import { Header } from '@/views/headers';
import { cxl_types, fin_trx_delays, installments, pay_limit_types, pay_window_extend_hours, pay_window_secure_levels, withdraw_limit_types } from '@/views/merchandises/pay-modules/useStore';
import { merchant_statuses, tax_category_types } from '@/views/merchandises/useStore';
import { authLevels, settleCycles, settleDays, settleTaxTypes, useSalesFilterStore } from '@/views/salesforces/useStore';
import { Searcher } from '@/views/searcher';
import { pg_settle_types, round_types } from '@/views/services/pay-gateways/useStore';
import type { ActivityHistory, Options } from '@/views/types';
import { useStore } from '../pay-gateways/useStore';

export const historyLevels = () => {
    const sales = salesLevels()
    sales.unshift(<Options>({id: 10, title: '가맹점'}))
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
        if(histories[i].history_target === '구분 정보') {
            if(histories[i].before_history_detail['타입'] === 0) {
                delete histories[i].after_history_detail['타입']
                delete histories[i].before_history_detail['타입']
                histories[i].after_history_detail['구분 타입'] = '장비'
                histories[i].before_history_detail['구분 타입'] = '장비'
            }
            else if(histories[i].before_history_detail['타입'] === 1) {
                delete histories[i].after_history_detail['타입']
                delete histories[i].before_history_detail['타입']
                histories[i].after_history_detail['구분 타입'] = '커스텀 필터'
                histories[i].before_history_detail['구분 타입'] = '커스텀 필터'
            }
        }
        else if(corp.pv_options.paid.use_issuer_filter && histories[i].history_target === '결제모듈')
            histories[i].before_history_detail['카드사 필터'] = JSON.stringify(histories[i].before_history_detail['카드사 필터'])
    }
    return histories
}

export const replaceVariable = (history_detail: any, history_target:string) => {
    const { mchts, all_sales } = useSalesFilterStore()
    const { pgs, pss, settle_types, terminals, finance_vans, pg_companies } = useStore()
    const changeKeyName = () => {
        const keys = [
            'sales0_id','sales1_id','sales2_id','sales3_id','sales4_id','sales5_id',    
            'sales0_fee','sales1_fee','sales2_fee','sales3_fee','sales4_fee','sales5_fee',
            'sales0_settle_amount','sales1_settle_amount','sales2_settle_amount',
            'sales3_settle_amount','sales4_settle_amount','sales5_settle_amount',
        ]
        keys.forEach((key) => {
            if("validation.attributes." + key in history_detail) {
                const level = key.slice(0, 6)
                let key_name = corp.pv_options.auth.levels[level+'_name']
                if(key.includes('fee')) {
                    key_name += ' 수수료';
                    history_detail['validation.attributes.'+key] *= 100
                }
                else if(key.includes('_settle_amount')) {
                    key_name += ' 정산금';
                }
                history_detail[key_name] =  history_detail['validation.attributes.'+key]
                delete history_detail['validation.attributes.'+key]
            }
        })
        replaceIdtoName()
        return history_detail
    }
    const replaceIdtoName = () => {
        const levels = corp.pv_options.auth.levels
        const _replaceToName = (lists: any[], key: string, name: string) => {
            if(key in history_detail) {
                const value = lists.find(obj => obj.id == history_detail[key])
                history_detail[key] = value ? value[name] : history_detail[key]
            }
        }

        _replaceToName(pgs, "PG사", 'pg_name')
        _replaceToName(pss, "구간", 'name')
        _replaceToName(mchts, "가맹점", 'mcht_name')
        _replaceToName(terminals, "장비", 'name')
        _replaceToName(finance_vans, "금융벤 ID", 'nick_name')
        _replaceToName(fin_trx_delays, "이체 딜레이", 'title')
        _replaceToName(withdraw_limit_types, "출금제한타입", 'title')

        _replaceToName(cxl_types, "취소 타입", 'title')
        _replaceToName(installments, "할부", 'title')
        _replaceToName(pay_limit_types, "결제제한타입", 'title')
        _replaceToName(pay_window_extend_hours, "결제창 연장시간", 'title')
        _replaceToName(pay_window_secure_levels, "결제창 보안등급", 'title')

        _replaceToName(merchant_statuses, "가맹점 상태", 'title')
        _replaceToName(tax_category_types, "사업자 타입", 'title')

        _replaceToName(settleCycles(), "정산 주기", 'title')
        _replaceToName(settleDays(), "정산 요일", 'title')
        _replaceToName(settleTaxTypes(), "정산 세율", 'title')
        _replaceToName(authLevels(), "권한등급", 'title')
        
        _replaceToName(all_sales[0], levels.sales0_name, 'sales_name')
        _replaceToName(all_sales[1], levels.sales1_name, 'sales_name')
        _replaceToName(all_sales[2], levels.sales2_name, 'sales_name')
        _replaceToName(all_sales[3], levels.sales3_name, 'sales_name')
        _replaceToName(all_sales[4], levels.sales4_name, 'sales_name')
        _replaceToName(all_sales[5], levels.sales5_name, 'sales_name')

        if(history_target === 'PG사')
        {
            _replaceToName(pg_settle_types, "정산일", 'title')    
            _replaceToName(round_types, "소수점 정산반식", 'title')    
            _replaceToName(pg_companies, "PG 타입", 'name')    
            
        }
        else
            _replaceToName(settle_types, "정산일", 'name')        
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
