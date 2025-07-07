

import { isEmpty } from '@core/utils'
import { banks } from '@/views/users/useStore';
import corp from '@/plugins/corp';
import { useStore } from '@/views/services/options/useStore';
import { axios } from '@axios'

export const ownerCheck = async (items: any[]): Promise<[boolean, string]> => {
    const chunkSize = 5;    // 5개 단위
    const batchSize = 3;    // 3개 병렬 요청

    const chunks: any[][] = [];
    for (let i = 0; i < items.length; i += chunkSize) {
        chunks.push(items.slice(i, i + chunkSize));
    }

    for (let i = 0; i < chunks.length; i += batchSize) {
        const batch = chunks.slice(i, i + batchSize);

        const results = await Promise.allSettled(
            batch.map(chunk =>
                axios.post('/api/v1/manager/bank-accounts/batch-updaters/register', chunk)
            )
        );

        for (const result of results) {
            if (result.status === 'rejected') {
                const err = result.reason;
                const message = err?.response?.data?.message || err.message || 'Unknown error';
                console.error("❌ 요청 실패:", message);
                return [false, message];
            }
        }
    }

    return [true, ''];
};

export const validateItems = (item: any, i: number, acct_nums: any) => {
    const { finance_vans } = useStore()
    const finance_van = finance_vans.find(a => a.id === parseInt(item.fin_id))
    
    if (finance_van === null || finance_van === undefined) 
        return [false, (i + 2) + '번째 이체모듈이 이상합니다.']
    else if (isEmpty(item.acct_bank_code)) 
        return [false, (i + 2) + '번째 입금 은행코드는 필수로 입력해야합니다.']
    else if (banks.find(bank => bank.code === item.acct_bank_code) == null) 
        return [false, (i + 2) + '번째줄의 은행코드가 이상합니다.']
    else if (isEmpty(item.acct_num)) 
        return [false, (i + 2) + '번째줄의 입금 계좌번호는 필수로 입력해야합니다.']
    else if (corp.ov_options.free.use_account_number_duplicate && acct_nums.has(item.acct_num))
        return [false, (i + 2) + '번째줄의 입금 계좌번호가 중복됩니다.('+item.acct_num+")"]
    else if (isEmpty(item.acct_name)) 
        return [false, (i + 2) + '번째줄의 예금주는 필수로 입력해야합니다.']
    else if (isEmpty(item.withdraw_amount)) 
        return [false, (i + 2) + '번째줄의 출금 금액은 필수로 입력해야합니다.']
    else {
        return [true, '']
    }
}

export const useRegisterStore = defineStore('virtualAccountRegisterStore', () => {
    const getHeaders = () => {
        return [
            { title: '이체모듈 타입(O)', key: 'fin_id'},
            { title: '입금 은행코드(O)', key: 'acct_bank_code'},
            { title: '입금 계좌번호(O)', key: 'acct_num'},
            { title: '예금주(O)', key: 'acct_name'},
            { title: '출금금액(O)', key: 'withdraw_amount'},
        ]
    }
    const headers = getHeaders()

    return {
        headers
    }
})
