import { getUserLevel } from '@/plugins/axios'
import { useRequestStore } from '@/views/request'
import type { Classification, FinanceVan, Options, PayGateway, PaySection } from '@/views/types'

export const pg_settle_types = <Options[]>([
    {id:0, title:'매일정산'},
    {id:1, title:'주말, 공휴일 건너뛰고 정산'},
])

export const useStore = defineStore('payGatewayStore', () => {
    const { get, post } = useRequestStore()
    const snackbar = <any>(inject('snackbar'))
    const pgs = ref<PayGateway[]>([])
    const pss = ref<PaySection[]>([])
    const terminals   = ref<Classification[]>([])
    const cus_filters = ref<Classification[]>([])
    const finance_vans = ref<FinanceVan[]>([])
    const pg_companies = [
        {id:1, name:'페이투스', rep_name:'서동균', company_name:'(주)페이투스', business_num:'810-81-00347', phone_num:'02-465-8800', addr:'서울특별시 금천구 가산디지털1로 168, C동 7층 701B호(가산동, 우림라이온스밸리)'},
        {id:2, name:'케이원피에스', rep_name:'강승구', company_name:'(주)케이원피에스', business_num:'419-88-00046', phone_num:'1855-1838', addr:'서울특별시 구로구 디지털로33길 27, 5층 513호, 514호(구로동, 삼성IT밸리)'},
        {id:3, name:'에이닐', rep_name:'이승철', company_name:'(주)에이닐에프앤피', business_num:'788-87-00950', phone_num:'1544-6872', addr:'서울 송파구 법원로11길 7 (문정동) 문정현대지식산업센터C동 1404~1406호'}, 
        {id:4, name:'웰컴페이먼츠(2차)', rep_name:'김기현', company_name:'웰컴페이먼츠(주)', business_num:'526-87-00842', phone_num:'02-838-2001', addr:'서울특별시 용산구 한강대로 148 웰컴금융타워 16층'},
        {id:5, name:'헥토파이넨셜', rep_name:'최종원', company_name:'(주)헥토파이낸셜', business_num:'101-81-63383', phone_num:'1600-5220', addr:'서울특별시 강남구 테헤란로34길 6, 9~10층(역삼동, 태광타워)'}, 
        {id:6, name:'루멘페이먼츠', rep_name:'김인환', company_name:'(주)루멘페이먼츠', business_num:'707-81-01787', phone_num:'02-1599-1873', addr:'서울특별시 동작구 상도로 13 4층 (프라임빌딩)'},
        {id:7, name:'페이레터', rep_name:'이성우', company_name:'페이레터(주)', business_num:'114-86-05588', phone_num:'1599-7591', addr:'서울시 강남구 역삼로 223 (역삼동 733-23, 대사빌딩) 페이레터(주)'}, 
        {id:8, name:'홀빅(페이스타)', rep_name:'김병규', company_name:'주식회사 홀빅', business_num:'136-81-35826', phone_num:'1877-5916', addr:'서울특별시 송파구 송파대로 167, B동 609호(문정동, 문정역테라타워)'},
        {id:9, name:'코페이', rep_name:'채수철', company_name:'주식회사 코페이', business_num:'206-81-90716', phone_num:'1644-3475', addr:'서울 성동구 성수일로 77 서울숲IT밸리 608-611호'}, 
        {id:10, name:'코리아결제시스템', rep_name:'박형석', company_name:'(주)코리아결제시스템', business_num:'117-81-85188', phone_num:'02-6953-6010', addr:'서울 강남구 도산대로1길 40 (신사동) 201호'},
        {id:11, name:'더페이원', rep_name:'이일호', company_name:'(주)더페이원', business_num:'860-87-00645', phone_num:'1670-1915', addr:'서울 송파구 송파대로 201 B동 1621~2호 (문정동, 테라타워2)'},
        {id:12, name:'이지피쥐', rep_name:'김도형', company_name:'주식회사 이지피쥐', business_num:'635-81-00256', phone_num:'02-1522-3434', addr:'서울 강남구 도산대로 157 (신사동) 신웅타워2 15층'},
        {id:13, name:'섹터나인', rep_name:'김대일', company_name:'(주)섹타나인', business_num:'113-86-10017', phone_num:'02-2276-6800', addr:'서울특별시 강남구 논현로 201 (도곡동 907-54)'},
        {id:14, name:'키움페이', rep_name: '성백진', company_name: '(주)다우데이타', business_num: '220-81-01733', phone_num: '1588-5984', addr: '서울시 마포구 독막로 311 재화스퀘어 5층'},
        {id:15, name:'위즈페이', rep_name: '이용재', company_name: '(주)유니윌 위즈페이', business_num: '220-85-36623', phone_num: '1544-3267', addr: '서울 강남구 테헤란로 124, 5층 (역삼동, 삼원타워) (주)유니윌 위즈페이'},
        {id:16, name:'네스트페이', rep_name: '김찬수', company_name: '(주)페이네스트', business_num: '139-81-46088', phone_num: '02-431-8333', addr: '서울특별시 송파구 송파대로 201, 테라타워2 A동 905호 (문정동)'},
        {id:17, name:'E2U', rep_name: '이용원', company_name: '(주)이투유', business_num: '383-87-01545', phone_num: '1600-4191', addr: '경기도 성남시 수정구 위례광장로 19 아이페리온, 10층 1001호'},
        {id:18, name:'에드원', rep_name: '김춘걸', company_name: '주식회사 에드원', business_num: '114-81-90678', phone_num: '554-4002', addr: '서울시 영등포구 당산로 41길 11, E동 1109호 (당산동 4가, 당산 SK V1센터)'},
        {id:19, name:'삼인칭', rep_name: '윤건', company_name: '주식회사 삼인칭', business_num: '489-87-00733', phone_num: '1833-4854', addr: '서울특별시 마포구 큰우물로 76, 403호(도화동, 고려빌딩'},
        {id:20, name:'윈글로벌페이', rep_name: '우강섭', company_name: '(주)윈글로벌페이', business_num: '648-86-00577', phone_num: '1877-7590', addr: '[12918] 경기도 하남시 조정대로 45, 미사센텀비즈 F348호'},
        {id:21, name:'브라이트픽스(C3)', rep_name: '박용은', company_name: '(주)브라이트픽스', business_num: '235-88-01772', phone_num: '02-6336-0999', addr: '주소 서울특별시 금천구 가산디지털2로 166, 216~217호(가산동, 에이스K1타워)'},
        {id:22, name:'다날페이', rep_name:'박지만, 백현숙', company_name:'DANAL', business_num:'113-81-44055', phone_num:'1566-3355', addr:'13591 경기도 성남시 분당구 분당로 55 퍼스트타워 9층'},
        {id:23, name:'바움피엔에스', rep_name:'윤건, 안용희', company_name:'바움피엔에스 주식회사', business_num:'836-87-00147', phone_num:'1833-6199', addr:'인천시 연수구 인천타워대로 323 (송도 센트로드 Office A동 2907-9호)'},
        {id:24, name:'워너페이먼츠', rep_name:'황창우', company_name:'(주)워너페이먼츠', business_num:'864-88-01755', phone_num:'031-898-1775', addr:'경기 수원시 영통구 대학로 28, 3층'},
        {id:25, name:'버디페이', rep_name:'장호은', company_name:'(주)버디페이', business_num:'686-81-02591', phone_num:'031-898-1775', addr:'서울특별시 금천구 가산디지털2로 144, 1217~1220호'},
        {id:26, name:'위드페이', rep_name:'오섭규', company_name:'(주)위드페이먼츠', business_num:'858-86-00683', phone_num:'1511-7055', addr:'서울특별시 금천구 벚꽃로 298, 1511, 1512호(가산동, 대륭포스트타워 6차)'},
        {id:27, name:'픽스페이', rep_name:'박용은', company_name:'(주)브라이트픽스', business_num:'235-88-01772', phone_num:'02-6336-0999', addr:'주소 서울특별시 금천구 가산디지털2로 166, 216~217호(가산동, 에이스K1타워)'},
        {id:28, name:'갤럭시아머니트리', rep_name:'신동훈', company_name:'갤럭시아머니트리(주)', business_num:'120–81–60844', phone_num:'1566–0123', addr:'서울특별시 강남구 광평로 281 수서오피스빌딩 15층(수서동)'},
        {id:29, name:'부국위너스', rep_name:'신동훈', company_name:'부국위너스 주식회사', business_num:'675-86-00152', phone_num:'1644-1109', addr:'부산 해운대구 센텀중앙로 97 센텀스카이비즈 A동 2510호'},
        {id:30, name:'웰컴페이먼츠(1차)', rep_name:'김기현', company_name:'웰컴페이먼츠(주)', business_num:'526-87-00842', phone_num:'02-838-2001', addr:'서울특별시 용산구 한강대로 148 웰컴금융타워 16층'},
        {id:31, name:'토스페이먼츠', rep_name:'김민표', company_name:'토스페이먼츠 주식회사', business_num:' 411-86-01799', phone_num:'1544-7772', addr:'서울특별시 강남구 테헤란로 131, 14층 (역삼동,한국지식재산센터)'},
        {id:32, name:'페이업', rep_name:'문병래', company_name:'페이업(주)', business_num:'674-88-00508', phone_num:'02-1644-1017', addr:'서울시 강남구 테헤란로 83길 18, 9층(삼성동, 매직킹덤빌딩)'},
        {id:33, name:'웨이업', rep_name:'조준형', company_name:'(주)웨이업', business_num:'288-87-02685', phone_num:'-', addr:'서울 영등포구 국제금융로8길 27-9, 603호 (여의도동,동북빌딩)'},
        {id:34, name:'나이스페이', rep_name:'김광철', company_name:'나이스페이먼츠(주)', business_num:'288-87-02685', phone_num:'1661-7335', addr:'서울특별시 마포구 마포대로 217 크레디트센터 7층'},
        {id:35, name:'보나캠프', rep_name:'강기성', company_name:'보나캠프(주)', business_num:'320-87-01210', phone_num:'1644-7676', addr:'서울특별시 금천구 범안로 1130, 14층 (가산동, 디지털엠파이어빌딩)'},
        {id:36, name:'온오프코리아', rep_name:'서은교', company_name:' 온오프코리아 주식회사', business_num:'636-88-00753', phone_num:'02-1600-8952', addr:'경기 광명시 오리로619번길 11. 2층(하안동)'},
        {id:37, name:'케이에스넷', rep_name:'서은교', company_name:' (주)케이에스넷', business_num:'120-81-97322', phone_num:'02-3420-5800', addr:'서울특별시 서초구 반포대로 95, 2층(서초동)'},
        {id:38, name:'버디페이(위지온)', rep_name:'장호은', company_name:'(주)버디페이', business_num:'686-81-02591', phone_num:'031-898-1775', addr:'서울특별시 금천구 가산디지털2로 144, 1217~1220호'},
        {id:39, name:'씨드페이먼츠', rep_name:'김찬호', company_name:'주식회사 씨드파이낸셜', business_num:'320-86-02487', phone_num:'02-861-7717', addr:'서울특별시 금천구 가산디지털2로 144, 5층 507,508호(가산동, 현대 테라타워)'},
        {id:40, name:'위루트파이넨셜', rep_name:'길민홍', company_name:'위루트파이낸셜(주)', business_num:'456-81-01313', phone_num:'1811-7717', addr:'서울특별시 금천구 디지털로9길 99, 스타밸리 1013호'},
        {id:41, name:'이지피쥐(위지온)', rep_name:'김도형', company_name:'주식회사 이지피쥐', business_num:'635-81-00256', phone_num:'02-1522-3434', addr:'서울 강남구 도산대로 157 (신사동) 신웅타워2 15층'},
    ]

    const finance_companies = <Options[]>([
        {id:1, title:'쿠콘'},
        {id:2, title:'헥토파이낸셜'},
        {id:3, title:'웰컴페이먼츠'},
        {id:4, title:'더즌'},
        {id:5, title:'하이픈'},
        {id:6, title:'네스트페이'},
    ])
    
    const is_agency_vans = <Options[]>([
        {id:0, title:'미사용'},
        {id:1, title:'사용'},
    ])

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
            const r = await get('/api/v1/manager/services/pay-gateways/detail')
            Object.assign(pgs.value, r.data.pay_gateways.sort((a:PayGateway, b:PayGateway) => a.pg_name.localeCompare(b.pg_name)))
            Object.assign(pss.value, r.data.pay_sections.sort((a:PaySection, b:PaySection) => a.name.localeCompare(b.name)))
            Object.assign(terminals.value, r.data.terminals.sort((a:Classification, b:Classification) => a.name.localeCompare(b.name)))
            Object.assign(cus_filters.value, r.data.custom_filters.sort((a:Classification, b:Classification) => a.name.localeCompare(b.name)))
            Object.assign(finance_vans.value, r.data.finance_vans.sort((a:FinanceVan, b:FinanceVan) => a.nick_name.localeCompare(b.nick_name)))
            getFianaceVansBalance()
        }
        catch(e) {
            // 가맹점 수기결제시 필요
            //errorHandler(e)
        }
    })

    const updateFinanceVan = (fin_id: number) => {
        const finance_van = finance_vans.value.find(obj => obj.id === fin_id)
        if(finance_van)
            getFinanceVan(finance_van)
    }

    const getFinanceVan = async (finance_van: FinanceVan) => {
        let res = await post('/api/v1/manager/services/cms-transactions/get-balance', finance_van, false)
        let data = res.data
        if(data.code == 1) {
            finance_van.balance = <number>(parseInt(data['data']['WDRW_CAN_AMT']))
        } 
        else {
            finance_van.balance = 0
            const message = finance_van.nick_name+'의 잔고를 불러오는 도중 에러가 발생하였습니다.<br><br>'+data['message']+'('+data['code']+')'
            snackbar.value.show(message, 'error')
        }
    }

    const getFianaceVansBalance = async () => {
        if(getUserLevel() >= 35) {
            for (let i = 0; i < finance_vans.value.length; i++)  {
                if(!finance_vans.value[i].use_kakao_auth && !finance_vans.value[i].use_account_auth)
                    getFinanceVan(finance_vans.value[i])
            }    
        }
    }

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
        pgs, pss, terminals, settle_types, cus_filters, finance_vans, 
        pg_companies, finance_companies, is_agency_vans,
        psFilter, setFee, 
        updateFinanceVan, getFianaceVansBalance,
    }
})
