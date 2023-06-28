import type { Classification, PayGateway, PaySection } from '@/views/types'
import { axios } from '@axios'

export const useStore = defineStore('payGatewayStore', () => {
    const pgs = ref<PayGateway[]>([])
    const pss = ref<PaySection[]>([])
    const terminals   = ref<Classification[]>([])
    const cus_filters = ref<Classification[]>([])
    const errorHandler = <any>(inject('$errorHandler'))
    const pg_types = [
        {id:1, name:'페이투스', rep_nm:'서동균', company_nm:'(주)페이투스', business_num:'810-81-00347', phone_num:'02-465-8800', addr:'서울특별시 금천구 가산디지털1로 168, C동 7층 701B호(가산동, 우림라이온스밸리)'},
        {id:2, name:'케이원피에스', rep_nm:'강승구', company_nm:'(주)케이원피에스', business_num:'419-88-00046', phone_num:'1855-1838', addr:'서울특별시 구로구 디지털로33길 27, 5층 513호, 514호(구로동, 삼성IT밸리)'},
        {id:3, name:'에이닐', rep_nm:'이승철', company_nm:'(주)에이닐에프앤피', business_num:'788-87-00950', phone_num:'1544-6872', addr:'서울 송파구 법원로11길 7 (문정동) 문정현대지식산업센터C동 1404~1406호'}, 
        {id:4, name:'웰컴페이먼츠', rep_nm:'김기현', company_nm:'웰컴페이먼츠(주)', business_num:'526-87-00842', phone_num:'02-838-2001', addr:'서울특별시 용산구 한강대로 148 웰컴금융타워 16층'},
        {id:5, name:'헥토파이넨셜', rep_nm:'최종원', company_nm:'(주)헥토파이낸셜', business_num:'101-81-63383', phone_num:'1600-5220', addr:'서울특별시 강남구 테헤란로34길 6, 9~10층(역삼동, 태광타워)'}, 
        {id:6, name:'루멘페이먼츠', rep_nm:'김인환', company_nm:'(주)루멘페이먼츠', business_num:'707-81-01787', phone_num:'02-1599-1873', addr:'서울특별시 동작구 상도로 13 4층 (프라임빌딩)'},
        {id:7, name:'페이레터', rep_nm:'이성우', company_nm:'페이레터(주)', business_num:'114-86-05588', phone_num:'1599-7591', addr:'서울시 강남구 역삼로 223 (역삼동 733-23, 대사빌딩) 페이레터(주)'}, 
        {id:8, name:'홀빅(페이스타)', rep_nm:'김병규', company_nm:'주식회사 홀빅', business_num:'136-81-35826', phone_num:'1877-5916', addr:'서울특별시 송파구 송파대로 167, B동 609호(문정동, 문정역테라타워)'},
        {id:9, name:'코페이', rep_nm:'채수철', company_nm:'주식회사 코페이', business_num:'206-81-90716', phone_num:'1644-3475', addr:'서울 성동구 성수일로 77 서울숲IT밸리 608-611호'}, 
        {id:10, name:'코리아결제시스템', rep_nm:'박형석', company_nm:'(주)코리아결제시스템', business_num:'117-81-85188', phone_num:'02-6953-6010', addr:'서울 강남구 도산대로1길 40 (신사동) 201호'},
        {id:11, name:'더페이원', rep_nm:'이일호', company_nm:'(주)더페이원', business_num:'860-87-00645', phone_num:'1670-1915', addr:'서울 송파구 송파대로 201 B동 1621~2호 (문정동, 테라타워2)'},
        {id:12, name:'이지피쥐', rep_nm:'김도형', company_nm:'주식회사 이지피쥐', business_num:'635-81-00256', phone_num:'02-1522-3434', addr:'서울 강남구 도산대로 157 (신사동) 신웅타워2 15층'},
        {id:13, name:'CM페이', rep_nm:'', company_nm:'씨엠컴퍼니 주식회사', business_num:'', phone_num:'', addr:''},
    ]

    const settle_types = [
        {id:-1, name:'실시간'}, //{id:0, name:'D+0'},
        {id:0, name:'D+1'}, {id:1, name:'D+2'},
        {id:2, name:'D+3'}, {id:3, name:'D+4'},
        {id:4, name:'D+5'}, {id:5, name:'D+6'},
        {id:6, name:'D+7'}, {id:7, name:'D+8'},
        {id:8, name:'D+9'}, {id:9, name:'D+10'},
        {id:10, name:'D+11'}, {id:11, name:'D+12'},
        {id:12, name:'D+13'}, {id:13, name:'D+14'},
        {id:14, name:'D+15'}, {id:15, name:'D+16'},
        {id:16, name:'D+17'}, {id:17, name:'D+18'},
        {id:18, name:'D+19'}, {id:19, name:'D+20'},
        {id:20, name:'D+21'}, {id:21, name:'D+22'},
        {id:22, name:'D+23'}, {id:23, name:'D+24'},
        {id:24, name:'D+25'}, {id:25, name:'D+26'},
        {id:26, name:'D+27'}, {id:27, name:'D+28'},
        {id:28, name:'D+29'}, {id:29, name:'D+30'},
    ]
    onMounted(async () => {
        try {
            const r = await axios.get('/api/v1/manager/services/pay-gateways/detail')
            Object.assign(pgs.value, r.data.pay_gateways)
            Object.assign(pss.value, r.data.pay_sections)
            Object.assign(terminals.value, r.data.terminals)
            Object.assign(cus_filters.value, r.data.custom_filters)
        }
        catch(e) {
            errorHandler(e)
        }
    })
    const setFee = (items: PaySection[], id: number | null) => {
        if(id != null)
        {
            const item = items.find(item => item.id === id)
            return item != undefined ? "수수료율: " + (item.trx_fee * 1).toFixed(3) + "%" : ''    
        }
        else
            return ''
    }
    const psFilter = (filter:PaySection[], ps_id:number|null) => {
        if (pss.value.length > 0) {
            if (filter.length > 0) {
                let item = pss.value.find((item:PaySection) => item.id === ps_id)
                if (item != undefined && filter[0].pg_id != item.pg_id) {
                    if (ps_id != null)
                        ps_id = null
                }
            }
            else {
                if (ps_id != null)
                    ps_id = null
            }
        }
        return ps_id
    }
    return {
        pgs, pss, terminals, settle_types, cus_filters, pg_types, psFilter, setFee,
    }
})
