import { settleCycles, settleTaxTypes } from '@/views/salesforces/useStore';
import { Salesforce } from '@/views/types';
import { banks } from '@/views/users/useStore';
import { salesLevels } from '@axios';
import { isEmpty } from '@core/utils';
import { lengthValidatorV2 } from '@validators';

export const validateItems = (item: Salesforce, i: number, user_names: any) => {
    const all_sales = salesLevels()
    const all_cycles = settleCycles()
    const tax_types = settleTaxTypes()

    const level = all_sales.find(sales => sales.id === parseInt(item.level))
    const settle_cycle = all_cycles.find(sales => sales.id === parseInt(item.settle_cycle))
    const settle_tax_type = tax_types.find(sales => sales.id === parseInt(item.settle_tax_type))
    const acct_bank_name = banks.find(sales => sales.title === item.acct_bank_name)
    item.resident_num = item.resident_num ? item.resident_num.toString()?.trim() : ''

    if(user_names.has(item.user_name)) 
        return [false, (i + 2) + '번째줄의 아이디가 중복됩니다.('+item.user_name+")"]
    else if (isEmpty(item.user_name)) 
        return [false, (i + 2) + '번째줄의 아이디는 필수로 입력해야합니다.']
    else if (isEmpty(item.user_pw)) 
        return [false, (i + 2) + '번째줄의 패스워드는 필수로 입력해야합니다.']
    else if (isEmpty(item.resident_num)) 
        return [false, (i + 2) + '번째줄의 주민등록번호는 필수로 입력해야합니다.']
    else if (typeof lengthValidatorV2(item.business_num, 10) != 'boolean') 
        return [false, (i + 2) + '번째줄의 가맹점의 사업자번호 포멧이 정확하지 않습니다.']    
    else if (typeof lengthValidatorV2(item.resident_num, 14) != 'boolean') 
        return [false, (i + 2) + '번째줄의 주민등록번호 포멧이 정확하지 않습니다.']
    else if (level == null) 
        return [false, (i + 2) + '번째줄의 등급이 이상합니다.']
    else if (settle_cycle == null) 
        return [false, (i + 2) + '번째줄의 정산주기가 이상합니다.']
    else if (settle_tax_type == null) 
        return [false, (i + 2) + '번째줄의 정산세율이 이상합니다.']
    else if (isEmpty(item.acct_num)) 
        return [false, (i + 2) + '번째줄의 계좌번호는 필수로 입력해야합니다.']
    else if (isEmpty(item.acct_name)) 
        return [false, (i + 2) + '번째줄의 예금주는 필수로 입력해야합니다.']
    else if (acct_bank_name == null) 
        return [false, (i + 2) + '번째줄의 입금은행명이 이상합니다.']
    else {
        item.acct_bank_code = banks.find(sales => sales.title === item.acct_bank_name)?.code as string
        return [true, '']
    }
}

export const useRegisterStore = defineStore('salesRegisterStore', () => {
    const headers = [
        { key: 'user_name', title : '아이디(O)' },
        { key: 'user_pw', title : '패스워드(O)' },
        { key: 'sales_name', title : '영업라인 상호(0)' },
        { key: 'level', title : '등급(O)' }, 
        { key: 'nick_name', title : '대표자명(O)' },
        { key: 'addr', title : '주소(X)' }, 
        { key: 'phone_num', title : '휴대폰번호(X)' }, 
        { key: 'resident_num', title : '주민등록번호(O)' }, 
        { key: 'business_num', title : '사업자등록번호(X)' }, 
        { key: 'sector', title : '업종(X)' },
        { key: 'acct_num', title : '계좌번호(O)' },
        { key: 'acct_name', title : '예금주(O)' }, 
        { key: 'acct_bank_name', title : '입금은행명(O)' },
        { key: 'settle_tax_type', title : '정산세율(O)' }, 
        { key: 'settle_cycle', title : '정산주기(O)' }, 
        { key: 'settle_day', title : '정산일(X)' },
        { key: 'view_type', title : '화면 타입(O)' },
    ]

    const isPrimaryHeader = (key: string) => {
        const keys = ['level', 'settle_tax_type', 'settle_cycle', 'settle_day', 'view_type']
        return keys.includes(key)
    }

    return {
        headers, isPrimaryHeader
    }
})
