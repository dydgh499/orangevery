import { Header } from '@/views/headers'
import { user_info } from '@axios'
import corp from '@corp'

export const useRegisterStore = defineStore('mchtRegisterStore', () => {
    const head      = Header('merchandises', '가맹점 대량등록 포멧')
    const levels    = corp.pv_options.auth.levels

    const headers: Record<string, string> = {}
    if (levels.sales5_use && user_info.value.level >= 30) {
        headers['sales5_name'] = levels.sales5_name + '(X)'
        headers['sales5_fee'] = levels.sales5_name + ' 수수료(X)'
    }
    if (levels.sales4_use && user_info.value.level >= 25) {
        headers['sales4_name'] = levels.sales4_name + '(X)'
        headers['sales4_fee'] = levels.sales4_name + ' 수수료(X)'
    }
    if (levels.sales3_use && user_info.value.level >= 20) {
        headers['sales3_name'] = levels.sales3_name + '(X)'
        headers['sales3_fee'] = levels.sales3_name + ' 수수료(X)'
    }
    if (levels.sales2_use && user_info.value.level >= 17) {
        headers['sales2_name'] = levels.sales2_name + '(X)'
        headers['sales2_fee'] = levels.sales2_name + ' 수수료(X)'
    }
    if (levels.sales1_use && user_info.value.level >= 15) {
        headers['sales1_name'] = levels.sales1_name + '(X)'
        headers['sales1_fee'] = levels.sales1_name + ' 수수료(X)'
    }
    if (levels.sales0_use && user_info.value.level >= 13) {
        headers['sales0_name'] = levels.sales0_name + '(X)'
        headers['sales0_fee'] = levels.sales0_name + ' 수수료(X)'
    }
    headers['user_name'] = '가맹점 ID(O)'
    headers['user_pw'] = '가맹점 패스워드(O)'
    headers['trx_fee'] = '수수료(X)'
    headers['hold_fee'] = '유보금 수수료(X)'
    headers['mcht_name'] = '상호(O)'
    headers['nick_name'] = '대표자명(O)'
    headers['addr'] = '주소(O)'
    headers['phone_num'] = '휴대폰번호(O)'
    headers['resident_num'] = '주민등록번호(O)'
    headers['business_num'] = '사업자등록번호(O)'
    headers['sector'] = '업종(O)'
    headers['acct_num'] = '계좌번호(O)'
    headers['acct_name'] = '예금주(O)'
    headers['acct_bank_name'] = '입금은행명(O)'
    headers['acct_bank_code'] = '은행코드(O)'
    headers['custom_id'] = '커스텀 필터(X)'

    head.main_headers.value = [];
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.setFlattenHeaders()

    return {
        head, headers, levels
    }
})
