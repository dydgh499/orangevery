
import router from '@/router';
import { useStore } from '@/views/services/pay-gateways/useStore';
import { Merchandise, PayModule, Salesforce } from '@/views/types';
import { axios, getLevelByIndex, getUserLevel, user_info } from '@axios';
import corp from './corp';

export const IS_FIXPLUS_AGCY1_MODIFY_ABLE = ref(<boolean>(false))
export const IS_FIXPLUS_AGCY2_MODIFY_ABLE = ref(<boolean>(false))
// 픽스플러스 영업점 업데이트 가능 여부
export const isFixplusSalesAbleUpdate = (id: number) => {   
    const path = router.currentRoute.value.path
    if(path.includes('/merchandises')) {
        if(getUserLevel() === 30) { //총판
            return true
        }
        else if(getUserLevel() === 25) { //지사
            return true
        }
        else {  //대리점
            if(id) {
                if(getUserLevel() === 20) { // 대리점 1일 경우, 대리점 2가 있는지 여부에 따라 2가 있으면 업데이트 못함
                    // 하위 대리점이 없을 경우
                    if(IS_FIXPLUS_AGCY1_MODIFY_ABLE.value) 
                        return IS_FIXPLUS_AGCY2_MODIFY_ABLE.value   //매출이 발생했는지 확인
                    else    //하위 대리점이 있을 경우 (수정 불가)
                        IS_FIXPLUS_AGCY1_MODIFY_ABLE.value
                }
                else    //대리점 2일 경우 가맹점 정보 수정 가능
                    return IS_FIXPLUS_AGCY2_MODIFY_ABLE.value
            }
            else
                return true
        }
    }
    else if(path.includes('/salesforces')) {
        if(getUserLevel() === 30) { //총판
            return true
        }
        else if(getUserLevel() === 25) {    //지사
            return true
        }
        else {  //대리점
            return true                
        }
    }
    else
        return false
}

// 가맹점정보 자동업데이트(ID: 사업자, PW: 휴대폰)
export const autoUpdateMerchandiseInfo = (merchandise: Merchandise) => {  
    merchandise.tax_category_type = 0
    merchandise.use_regular_card  = 1
    merchandise.use_collect_withdraw = 1
    merchandise.use_saleslip_prov = 1
    merchandise.use_saleslip_sell = 0  
    merchandise.user_name = merchandise.business_num
    merchandise.user_pw = merchandise.phone_num
} 
export const getAutoInsertPaymentModuleFormat = (mcht_id: number) => {
    const { pgs } = useStore()
    const fin_id = 29
    const pg_id  = 171
    const ps_id  = 340

    const pay_module = <PayModule><unknown>({
        id: 0,
        mcht_id: mcht_id,
        terminal_id: null,
        settle_type: -1,
        module_type: 1,
        tid: mcht_id,
        serial_num: '',
        comm_settle_fee: 0,
        comm_settle_day: 1,
        comm_settle_type: 0,
        comm_calc_level: 10,
        under_sales_amt: 0,
        under_sales_type: 0,
        under_sales_limit: 0,
        begin_dt: null,
        ship_out_dt: null,
        ship_out_stat: 0,
        is_old_auth: 0,
        installment: 10,
        note: '수기결제',
        settle_fee: 0,
        pay_dupe_limit: 0,
        abnormal_trans_limit: 300,
        pay_year_limit: 60000,
        pay_month_limit: 5000,
        pay_day_limit: 2000,
        pay_single_limit: 300,
        pay_disable_s_tm: null,
        pay_disable_e_tm: null,
        show_pay_view: 1,
        pay_key: '',
        filter_issuers: [],
        contract_s_dt: null,
        contract_e_dt: null,
        fin_trx_delay: 0,
        cxl_type: 0,
        use_realtime_deposit: 1,
        pay_dupe_least: 0,
        payment_term_min: 1,
        p_mid: ''
    })

    pay_module.fin_id = fin_id
    const pg = pgs.find(obj => obj.id === pg_id)
    if(pg) {
        pay_module.pg_id = pg_id
        pay_module.ps_id = ps_id
        pay_module.api_key = pg.api_key
        pay_module.sub_key = pg.sub_key
        pay_module.mid = pg.mid
    }
    return pay_module
}

export const bulkAutoInsertPaymentModuleFormat = (mcht_ids: number[]) => {
    const pay_modules = []
    for (let i = 0; i < mcht_ids.length; i++) 
    {
        pay_modules.push(getAutoInsertPaymentModuleFormat(mcht_ids[i]))
    }
    return pay_modules
}

// 결제모듈 자동등록
export const autoInsertPaymentModule = (mcht_id: number) => {
    const params:any = getAutoInsertPaymentModuleFormat(mcht_id)
    params.use_mid_duplicate = Number(corp.pv_options.free.use_mid_duplicate)
    params.use_tid_duplicate = Number(corp.pv_options.free.use_tid_duplicate)
    axios.post('/api/v1/manager/merchandises/pay-modules', params)
}

// 영업점 하위 영업점 수정권한
export const isDistAgcyUnderSalesModifyAble = (all_sales: Salesforce[][]) => {
    return isDistMchtFeeModifyAble(all_sales)
}

export const isMchtFeeModifyAble = (all_sales: Salesforce[][], dest_sales: Salesforce) => {
    console.log(dest_sales)
    const idx = getLevelByIndex(dest_sales.level)
    for (let i = idx; i < 5; i++)  {
        dest_sales = all_sales[i+1].find(obj => obj.id === dest_sales.parent_id)
        if(dest_sales && dest_sales.level === 25)
            return Boolean(dest_sales.is_able_under_modify)
        else
            break
    }
    return false
}

// 가맹점 수수료 수정권한
export const isDistMchtFeeModifyAble = (all_sales: Salesforce[][]) => {
    if(getUserLevel() === 10)   //가맹점
        return false
    else if(getUserLevel() === 25) { //  지사
        return user_info.value.is_able_under_modify
    }
    else if(getUserLevel() > 10 && getUserLevel() <= 25) { // 대리점
        return isMchtFeeModifyAble(all_sales, user_info.value)
    }
    else // 운영자, 총판
        return true

}

// 영업점 수수료 자동업데이트
export const autoUpdateMerchandiseAgencyInfo = (merchandise: Merchandise, all_sales: Salesforce[][]) => {    
    const updateMchtFee = (idx: number, dest_sales: Salesforce, merchandise: Merchandise) => {
        merchandise[`sales${idx}_id`] = dest_sales.id
        merchandise[`sales${idx}_fee`] = dest_sales.sales_fee
        if(dest_sales.level === 25)
            merchandise.trx_fee = dest_sales.mcht_batch_fee
        return merchandise
    }

    const idx = getLevelByIndex(getUserLevel())
    let dest_sales = user_info.value
    merchandise = updateMchtFee(idx, dest_sales, merchandise)

    for (let i = idx; i < 5; i++) 
    {
        let _dest_sales = all_sales[i+1].find(obj => obj.id === dest_sales.parent_id)
        if(_dest_sales) {
            merchandise = updateMchtFee(i+1, _dest_sales, merchandise)
            dest_sales = _dest_sales
        }
    }
}

// 영업점 정보 자동업데이트
export const autoUpdateSalesforceInfo = (salesforce: Salesforce) => {
    salesforce.settle_cycle = 0
    salesforce.settle_day = null
    salesforce.settle_tax_type = 0
    salesforce.view_type = 1

    if(getUserLevel() <= 30) {
        salesforce.sales_fee = user_info.value.sales_fee
    }
}

export const isFixplus = () => corp.id === 30
// 픽스플러스 대리점인 경우
export const isFixplusAgency = () => isFixplus() && getUserLevel() <= 20

export const isBrightFix = () => corp.id === 12 || corp.id === 14 || corp.id === 30

export const getFixplusMchtHeader = () => {
    const headers: Record<string, string> = {
        'id': 'NO.',
    }
    headers['mcht_name'] = '상호'
    headers['created_at'] = '생성시간'
    headers['user_name'] = '가맹점 ID'
    headers['nick_name'] = '대표자명'
    headers['phone_num'] = '연락처'
    headers['business_num'] = '사업자등록번호'
    headers['acct_bank_name'] = '은행'
    headers['acct_num'] = '계좌번호'    
    headers['acct_name'] = '예금주'
    headers['trx_fee'] = '수수료'
    return headers
}

export const getFixplusSalesHeader = () => {
    const headers: Record<string, string> = {
        'id' : 'NO.',
        'level' : '등급',
        'user_name' : '영업점 ID',
        'sales_name': '영업점 상호',
    }
    if(getUserLevel() >= 35)
        headers['is_able_under_modify'] = '하위 가맹점수수료 변경권한'
    headers['sales_fee'] = '영업점 수수료'
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
        'created_at' : '생성시간',
        'extra_col' : '더보기',
    })
    return headers
}
