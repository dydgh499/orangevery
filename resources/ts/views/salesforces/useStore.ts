import { getFixplusSalesHeader, isFixplus } from '@/plugins/fixplus'
import { Header } from '@/views/headers'
import { Searcher } from '@/views/searcher'
import type { Merchandise, Options, Salesforce, UnderAutoSetting } from '@/views/types'
import { avatars } from '@/views/users/useStore'
import { axios, getSalesLevelByCol, getUserLevel, salesLevels } from '@axios'
import corp from '@corp'
import _ from 'lodash'

const levels = corp.pv_options.auth.levels

export const SALES_LEVEL_SIZE = 6

export const settleDays = () => {
    return <Options[]>([
        {id:null, title: '적용안함'}, {id:0, title:'일요일'},
        {id:1, title:'월요일'}, {id:2, title:'화요일'}, 
        {id:3, title:'수요일'}, {id:4, title:'목요일'}, 
        {id:5, title:'금요일'}, {id:6, title:'토요일'},         
    ])    
}

export const settleCycles = () => {
    return <Options[]>([
        {id:0, title:'하루씩 정산'}, {id:7, title:'1주일씩 정산'},
        {id:14, title:'2주일씩 정산'}, {id:28, title:'한달씩 정산(말일)'},
    ])
}

export const settleTaxTypes = () => {
    return <Options[]>([
        {id:0, title:'세율 없음'}, {id:1, title:'3.3%'},
        {id:2, title:'10%'}, {id:3, title:'10+3.3%'},
    ])
}

export const getAutoSetting = (auto_settings: UnderAutoSetting[]) => {
    return auto_settings.map(item => `${item.note} ${item.sales_fee}%`)
}

const getSalesHeaders = () => {
    const headers: Record<string, string> = {
        'id' : 'NO.',
        'level' : '등급',
        'user_name' : '영업점 ID',
        'sales_name': '영업점 상호',
    }
    headers['under_auto_settings'] = '수수료율'
    if(getUserLevel() >= 35)
        headers['is_able_modify_mcht'] = '가맹점 수정권한'
    headers['view_type'] = '화면타입'
    headers['settle_cycle'] = '정산 주기'
    headers['settle_day'] = '정산 요일'
    headers['settle_tax_type'] = '정산 세율'
    Object.assign(headers, {
        'nick_name' : '대표자명',
        'phone_num' : '연락처',
        'resident_num' : '주민등록번호',
        'business_num' : '사업자등록번호',
        'sector' : '업종',
        'addr' : '주소',
        'acct_name' : '예금주',
        'acct_num' : '계좌번호',
        'acct_bank_name' : '은행',
        'acct_bank_code' : '은행코드',
        'last_settle_dt': '마지막 정산일',
        'created_at' : '생성시간',
        'updated_at' : '업데이트시간',
        'extra_col' : '더보기',
    })
    return headers
}

export const useSearchStore = defineStore('salesSearchStore', () => {
    const store = Searcher('salesforces')
    const head  = Header('salesforces', '영업점 관리')
    const all_sales = salesLevels()
    const all_cycles = settleCycles()
    const all_days = settleDays()
    const tax_types = settleTaxTypes()
    

    const headers: Record<string, string> = isFixplus() ? getFixplusSalesHeader() : getSalesHeaders()
    head.sub_headers.value = []
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.flatten(head.headers.value)
    
    const metas = ref([])
    if(corp.pv_options.paid.sales_parent_structure === false) {
        Object.assign(metas.value, [
            {
                icon: 'tabler-user-check',
                color: 'primary',
                title: '금월 추가된 영업점',
                stats: '0',
                percentage: 0,
            },
            {
                icon: 'tabler-user-exclamation',
                color: 'error',
                title: '금월 감소한 영업점',
                percentage: 0,
                stats: '0',
            },
            {
                icon: 'tabler-user-check',
                color: 'primary',
                title: '금주 추가된 영업점',
                percentage: 0,
                stats: '0',
            },
            {
                icon: 'tabler-user-exclamation',
                color: 'error',
                title: '금주 감소한 영업점',
                percentage: 0,
                stats: '0',
            },
        ])
    }

    const exporter = async (type: number) => {
        const keys = Object.keys(head.flat_headers.value)
        const r = await store.get(store.base_url, { params:store.getAllDataFormat()})
        let datas = r.data.content;
        for (let i = 0; i < datas.length; i++) {
            datas[i]['level'] = all_sales.find(obj => obj['id'] === datas[i]['level'])?.title as string
            datas[i]['settle_cycle'] = all_cycles.find(obj => obj['id'] === datas[i]['settle_cycle'])?.title as string
            datas[i]['settle_day'] = all_days.find(obj => obj['id'] === datas[i]['settle_day'])?.title as string
            datas[i]['settle_tax_type'] = tax_types.find(obj => obj['id'] === datas[i]['settle_tax_type'])?.title as string
            datas[i]['resident_num'] = datas[i]['resident_num_front'] + "-" + (corp.pv_options.free.resident_num_masking ? "*******" : datas[i]['resident_num_back'])

            datas[i] = head.sortAndFilterByHeader(datas[i], keys)
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

export const useSalesFilterStore = defineStore('useSalesFilterStore', () => {
    const is_sales_loaded = ref(false)
    const all_sales = Array.from({ length: SALES_LEVEL_SIZE }, () => <Salesforce[]>([]))
    const sales = Array.from({ length: SALES_LEVEL_SIZE }, () => ref<Salesforce[]>([]))
    const sales_apply_histories = ref(<any[]>([]))
    const mchts = ref(<Merchandise[]>([]))
    
    onMounted(async () => { 
        await Promise.all([
            classification(),
            getAllMchts(),
            feeApplyHistoires(),
        ])
        is_sales_loaded.value = true
    })
    const isSalesLoaded = computed(() => { return is_sales_loaded.value })
    const classification = async () => {
        const sales_parent_structure = getUserLevel() > 10 && getUserLevel() < 35 ? Number(corp.pv_options.paid.sales_parent_structure ?? 0) : 0
        const r = await axios.get('/api/v1/manager/salesforces/classification?sales_parent_structure=' + sales_parent_structure)
        const keys = Object.keys(r.data);
        for (let i = 0; i < keys.length; i++) {
            all_sales[i] = r.data[keys[i]].sort((a:Salesforce, b:Salesforce) => a.sales_name.localeCompare(b.sales_name))
            sales[i].value = r.data[keys[i]]
        }
    }

    const getAllMchts = async() => {
        const url = '/api/v1/manager/merchandises/all'
        const r = await axios.get(url)
        Object.assign(mchts.value, r.data.content.sort((a:Merchandise, b:Merchandise) => a.mcht_name.localeCompare(b.mcht_name)))
    }

    const feeApplyHistoires = async () => {
        const r = await axios.get('/api/v1/manager/salesforces/fee-apply-histories')
        sales_apply_histories.value = r.data
    }
    
    const hintSalesApplyFee = (sales_id: number): string => {
        if (sales_id) {
            const history = sales_apply_histories.value.find(obj => obj.sales_id === sales_id)
            return history ? '마지막 일괄적용: ' + (history.trx_fee * 100).toFixed(3) + '%' : '';
        }
        else
            return ''
    }

    const hintSalesSettleTaxType = (sales_id: number, sales:Salesforce[]) => {
        return sales.find(obj => obj.id === sales_id)?.settle_tax_type as number
    }

    const hintSalesSettleTaxTypeText = (mcht: Merchandise, sales_idx: number, sales:Salesforce[]) => {
        const settle_tax_type = hintSalesSettleTaxType(mcht[`sales${sales_idx}_id`], sales)
        return settleTaxTypes().find(obj => obj.id === settle_tax_type)?.title
    }

    
    const hintSalesSettleTotalFee = (mcht: Merchandise, sales_idx: number, sales:Salesforce[]) => {
        let trx_fee = Number(hintSalesSettleFee(mcht, sales_idx).replace('정산수수료: ', '').replace('%', ''))
        const settle_tax_type = hintSalesSettleTaxType(mcht[`sales${sales_idx}_id`], sales)
        switch(settle_tax_type) {
            case 1:
                trx_fee *= 0.967;
                break;
            case 2:
                trx_fee *= 0.9;
                break;
            case 3:
                trx_fee *= 0.9;
                trx_fee *= 0.967;
                break;
        }
        return trx_fee.toFixed(4)
    }

    const hintSalesSettleFee = (mcht: Merchandise, sales_idx: number): string => {
        const levels = corp.pv_options.auth.levels
        const dest_key = `sales${sales_idx}`;
        if (levels[`${dest_key}_use`] && mcht[`${dest_key}_id`]) {
            let under_fee = -1;
            for (let i = sales_idx-1; i > -1; i--) 
            {
                const sales_key = `sales${i}`
                if(levels[`${sales_key}_use`] && mcht[`${sales_key}_id`]) {
                    under_fee = mcht[`${sales_key}_fee`]
                    break
                }
            }
            if(under_fee === -1) 
                under_fee = mcht.trx_fee

            mcht[`${dest_key}_settlement_fee`] = (under_fee - mcht[`${dest_key}_fee`]).toFixed(4)
            return `정산수수료: ${mcht[`${dest_key}_settlement_fee`]}%`
        }
        else {
            mcht[`${dest_key}_settlement_fee`] = 0
            return ''
        }
    }

    // 상위 영업점들
    const getAboveSalesFilter = (select_idx:number, params:any) => {
        let _mcht = [];
        for (let i = select_idx; i < SALES_LEVEL_SIZE; i++) {
            const sales_key = `sales${i}`
            // sales_id가 포함된 가맹점 리스트 목록
            if(params[sales_key + '_id']) {
                _mcht = _.filter(mchts.value, obj => obj[sales_key + '_id'] === params[sales_key + '_id'])    
                if(_.isEmpty(_mcht)) {
                    _mcht = _.chain(all_sales[i])
                        .filter(obj => obj.id === params[sales_key + '_id'])
                        .map(obj => ({
                            [sales_key + '_id']: obj.id,
                            [sales_key + '_name']: obj.sales_name
                        }))
                        .value()
                }
                else {
                    _mcht = _.map(_mcht, obj => ({
                        ...obj,
                        ..._.range(0, SALES_LEVEL_SIZE).reduce((acc, idx) => {
                            const sales = _.find(all_sales[idx], sales => sales.id === obj[`sales${idx}_id`])
                            if (sales) {
                                acc[`sales${idx}_name`] = sales.sales_name
                            }
                            return acc
                        }, {})
                    }))
                }
                return _mcht
            }
        }
        // 모든 가맹점 리스트 목록(아무것도 선택된게 없을 때, 전체리턴)
        return mchts.value;
    };

    const initAllSales = () => {
        for (let i = 0; i < SALES_LEVEL_SIZE; i++) {
            sales[i].value = [
                { id: null, sales_name: '전체' },
                ...all_sales[i].sort((a, b) => a.sales_name.localeCompare(b.sales_name))
            ]
        }
    }
    
    // 상위에 클릭된게 있는지 체크
    const isAboveCheck = (curr_idx: number, params: any) => {
        for (let i = curr_idx; i < SALES_LEVEL_SIZE; i++) {
            const sales_key = `sales${i}`
            if(params[sales_key+'_id'])
                return true
        }
        return false
    }

    const setUnderSalesFilter = (select_idx: number, params: any) => {
        for (let i = SALES_LEVEL_SIZE - 1; i >= 0; i--) {    
            const sales_key = `sales${i}`
            // 전산에서 사용하고 있는 영업점레벨이 전체를 선택했을때
            if(levels[sales_key+'_use'] && select_idx == i && !params[sales_key+'_id']) {
                // 상위가 아무것도 클릭된게 없을 때
                if(isAboveCheck(i, params) == false) {
                    initAllSales()
                    return    
                }
            }
        }
        
        const _mchts = getAboveSalesFilter(select_idx, params)
        for (let i = SALES_LEVEL_SIZE - 1; i >= 0; i--) {
            const sales_key = `sales${i}`
            // 가맹점목록에서 특정 level의 id와 name 목록을 가져옴
            const map_sales = _mchts.map(obj => ({
                 id: obj[sales_key + '_id'], 
                 sales_name: obj[sales_key + '_name'] 
                }
            ))
            .filter(obj => obj.id && obj.sales_name != '')  //
            .filter((v, i, a) => a.findIndex(t => t.id === v.id) === i)
            sales[i].value = [
                { id: null, sales_name: '전체' },
                ...map_sales.sort((a, b) => a.sales_name.localeCompare(b.sales_name))
            ]
            // 선택한 영업점이 영업점 목록에 없을 때 전체로 변경            
            if(sales[i].value.find(obj => obj.id === params[sales_key+'_id']) === undefined)
                params[sales_key+'_id'] = undefined
        }
    }
    
    const findSalesName = (key: string, value: number) => {
        const level = getSalesLevelByCol(key)
        return all_sales[level].find(obj => obj.id === value)?.sales_name
    }

    return {
        isSalesLoaded,
        all_sales,
        sales,
        sales_apply_histories,
        mchts,
        initAllSales,
        classification,
        setUnderSalesFilter,
        hintSalesApplyFee,
        hintSalesSettleFee,
        findSalesName,
        hintSalesSettleTaxType,
        hintSalesSettleTaxTypeText,
        hintSalesSettleTotalFee,
    }
})

export const defaultItemInfo = () => {
    const path = 'salesforces'
    const item = reactive<Salesforce>({
        id: 0,
        settle_tax_type: 0,
        created_at: undefined,
        user_name: '',
        user_pw: '',
        nick_name: '',
        addr: '',
        phone_num: '',
        resident_num: '',
        business_num: '',
        passbook_img: '/utils/icons/img-preview.svg',
        id_img: '/utils/icons/img-preview.svg',
        contract_img: '/utils/icons/img-preview.svg',
        bsin_lic_img: '/utils/icons/img-preview.svg',
        profile_img: avatars[Math.floor(Math.random() * avatars.length)],
        acct_num: '',
        acct_name: '',
        acct_bank_name: '',
        acct_bank_code: null,
        updated_at: undefined,
        id_file: undefined,
        passbook_file: undefined,
        contract_file: undefined,
        bsin_lic_file: undefined,
        level: 15,
        settle_cycle: 0,
        settle_day: 0,
        sales_name: '',
        view_type: 0,
        is_able_modify_mcht: 0,
        is_able_under_modify: 0,
        note: '',
        resident_num_front: '',
        resident_num_back: ''
    })
    return {
        path, item
    }
}
