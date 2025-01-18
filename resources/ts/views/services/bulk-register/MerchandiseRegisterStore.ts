import { useSalesFilterStore } from '@/views/salesforces/useStore'
import { Merchandise } from '@/views/types'
import { banks } from '@/views/users/useStore'
import { getUserLevel } from '@axios'
import { isEmpty } from '@core/utils'
import corp from '@corp'
import { lengthValidatorV2 } from '@validators'
import { useStore } from '../pay-gateways/useStore'

const isNotExistSalesforce = (is_use: boolean, sales_idx: number, item: Merchandise) => {
    const { sales } = useSalesFilterStore()
    const sales_id = 'sales' + sales_idx + '_id';
    const sales_name = 'sales' + sales_idx + '_name';
    
    if (is_use && item[sales_name]) {
        const salesforce = sales[sales_idx].value.find(sales => sales.sales_name === item[sales_name])
        if (salesforce)
            item[sales_id] = salesforce.id
        return salesforce == null ? true : false
    }
    else
        return false
}
const isNotExistCustomFilter = (custom_id: number | null) => {
    if (custom_id) {
        const { cus_filters } = useStore()
        const filter = cus_filters.find(cus => cus.id === custom_id)
        return filter == null ? true : false
    }
    else
        return false
}
export const validateItems = (item: Merchandise, i: number, user_names: any, mcht_names: any) => {
    const levels = corp.pv_options.auth.levels
    item.sales0_name = item.sales0_name ? item.sales0_name?.trim() : null
    item.sales1_name = item.sales1_name ? item.sales1_name?.trim() : null
    item.sales2_name = item.sales2_name ? item.sales2_name?.trim() : null
    item.sales3_name = item.sales3_name ? item.sales3_name?.trim() : null
    item.sales4_name = item.sales4_name ? item.sales4_name?.trim() : null
    item.sales5_name = item.sales5_name ? item.sales5_name?.trim() : null
    item.resident_num = item.resident_num ? item.resident_num.trim() : ''

    if(user_names.has(item.user_name)) 
        return [false, (i + 2) + '번째줄의 아이디가 중복됩니다.('+item.user_name+")"]
    else if(mcht_names.has(item.mcht_name)) 
        return [false, (i + 2) + '번째줄의 상호가 중복됩니다.('+item.mcht_name+")"]
    else if (isNotExistSalesforce(levels.sales5_use, 5, item))
        return [false, (i + 2) + '번째줄의 ' + levels.sales5_name + '이(가) 존재하지 않습니다.']
    else if (isNotExistSalesforce(levels.sales4_use, 4, item)) 
        return [false, (i + 2) + '번째줄의 ' + levels.sales4_name + '이(가) 존재하지 않습니다.']
    else if (isNotExistSalesforce(levels.sales3_use, 3, item)) 
        return [false, (i + 2) + '번째줄의 ' + levels.sales3_name + '이(가) 존재하지 않습니다.']
    else if (isNotExistSalesforce(levels.sales2_use, 2, item)) 
        return [false, (i + 2) + '번째줄의 ' + levels.sales2_name + '이(가) 존재하지 않습니다.']
    else if (isNotExistSalesforce(levels.sales1_use, 1, item)) 
        return [false, (i + 2) + '번째줄의 ' + levels.sales1_name + '이(가) 존재하지 않습니다.']
    else if (isNotExistSalesforce(levels.sales0_use, 0, item)) 
        return [false, (i + 2) + '번째줄의 ' + levels.sales0_name + '이(가) 존재하지 않습니다.']
    else if (isNotExistCustomFilter(item.custom_id)) 
        return [false, (i + 2) + '번째줄의 커스텀필터가 존재하지 않습니다.']
    else if (isEmpty(item.user_name)) 
        return [false, (i + 2) + '번째줄의 가맹점의 아이디는 필수로 입력해야합니다.']
    else if (isEmpty(item.mcht_name)) 
        return [false, (i + 2) + '번째줄의 가맹점의 상호는 필수로 입력해야합니다.']
    else if (isEmpty(item.user_pw)) 
        return [false, (i + 2) + '번째줄의 가맹점의 패스워드는 필수로 입력해야합니다.']
    else if (typeof lengthValidatorV2(item.business_num, 10) != 'boolean') 
        return [false, (i + 2) + '번째줄의 가맹점의 사업자번호 포멧이 정확하지 않습니다.']    
    else if (typeof lengthValidatorV2(item.resident_num, 14) != 'boolean') 
        return [false, (i + 2) + '번째줄의 가맹점의 주민등록번호 포멧이 정확하지 않습니다.']
    else if (isEmpty(item.sector)) 
        return [false, (i + 2) + '번째줄의 가맹점의 업종은 필수로 입력해야합니다.']
    else if (isEmpty(item.acct_num)) 
        return [false, (i + 2) + '번째줄의 가맹점의 계좌번호는 필수로 입력해야합니다.']
    else if (isEmpty(item.acct_name)) 
        return [false, (i + 2) + '번째줄의 가맹점의 예금주는 필수로 입력해야합니다.']
    else if (banks.find(bank => bank.title === item.acct_bank_name) == null) 
        return [false, (i + 2) + '번째줄의 가맹점의 입금은행명이 이상합니다.']
    else {
        item.acct_bank_code = banks.find(bank => bank.title === item.acct_bank_name)?.code as string
        return [true, '']
    }
}
export const useRegisterStore = defineStore('mchtRegisterStore', () => {
    const levels    = corp.pv_options.auth.levels
    const headers = []
    if (levels.sales5_use && getUserLevel() >= 30) {
        headers.push(
            {title: levels.sales5_name + ' 상호(X)', key: 'sales5_name'},
            {title: levels.sales5_name + ' 수수료(X)', key: 'sales5_fee'},
        )
    }
    if (levels.sales4_use && getUserLevel() >= 25) {
        headers.push(
            {title: levels.sales4_name + ' 상호(X)', key: 'sales4_name'},
            {title: levels.sales4_name + ' 수수료(X)', key: 'sales4_fee'},
        )
    }
    if (levels.sales3_use && getUserLevel() >= 20) {
        headers.push(
            {title: levels.sales3_name + ' 상호(X)', key: 'sales3_name'},
            {title: levels.sales3_name + ' 수수료(X)', key: 'sales3_fee'},
        )
    }
    if (levels.sales2_use && getUserLevel() >= 17) {
        headers.push(
            {title: levels.sales2_name + ' 상호(X)', key: 'sales2_name'},
            {title: levels.sales2_name + ' 수수료(X)', key: 'sales2_fee'},
        )
    }
    if (levels.sales1_use && getUserLevel() >= 15) {
        headers.push(
            {title: levels.sales1_name + ' 상호(X)', key: 'sales1_name'},
            {title: levels.sales1_name + ' 수수료(X)', key: 'sales1_fee'},
        )
    }
    if (levels.sales0_use && getUserLevel() >= 13) {
        headers.push(
            {title: levels.sales0_name + ' 상호(X)', key: 'sales0_name'},
            {title: levels.sales0_name + ' 수수료(X)', key: 'sales0_fee'},
        )
    }
    headers.push(
        {title: '가맹점 ID(O)', key: 'user_name'},
        {title: '가맹점 패스워드(O)', key: 'user_pw'},
        {title: '가맹점 수수료(X)', key: 'trx_fee'},
        {title: '유보금 수수료(X)', key: 'hold_fee'},
        {title: '상호(O)', key: 'mcht_name'},
        {title: '대표자명(O)', key: 'nick_name'},
        {title: '주소(O)', key: 'addr'},
        {title: '휴대폰번호(O)', key: 'phone_num'},
        {title: '주민등록번호(X)', key: 'resident_num'},
        
        {title: '사업자등록번호(X)', key: 'business_num'},
        {title: '업종(O)', key: 'sector'},
        {title: '계좌번호(O)', key: 'acct_num'},
        {title: '예금주(O)', key: 'acct_name'},
        {title: '입금은행명(O)', key: 'acct_bank_name'},
        
        {title: '사업자 유형(X)', key: 'tax_category_type'},
        {title: '커스텀 필터(X)', key: 'custom_id'},
    )

    if(corp.pv_options.paid.use_collect_withdraw) 
        headers.push({title: '모아서 출금 사용여부(X)', key: 'use_collect_withdraw'})
    if(corp.pv_options.paid.use_regular_card) 
        headers.push({title: '단골고객 사용여부(X)', key: 'use_regular_card'})
    if(corp.pv_options.paid.use_collect_withdraw)
        headers.push({title: '모아서 출금 수수료(X)', key: 'collect_withdraw_fee'})
    if(corp.pv_options.paid.use_withdraw_fee)
        headers.push({title: '출금 수수료(X)', key: 'withdraw_fee'})
    if(corp.pv_options.paid.use_pay_verification_mobile)
        headers.push({title: '결제전 휴대폰 인증(X)', key: 'use_pay_verification_mobile'})
    if(corp.pv_options.paid.use_multiple_hand_pay)
        headers.push({title: '다중결제 사용(X)', key: 'use_multiple_hand_pay'})
    if(corp.pv_options.paid.use_noti)
        headers.push({title: '노티 사용(X)', key: 'use_noti'})

    const isPrimaryHeader = (key: string) => {
        const keys = ['tax_category_type', 'custom_id']
        return keys.includes(key)
    }

    return {
        headers, isPrimaryHeader
    }
})
