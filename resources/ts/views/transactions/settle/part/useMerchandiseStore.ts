import { Header } from '@/views/headers';
import { transactionHeader } from '../../transacitonsHeader';

export const useSearchStore = defineStore('transSettlesMchtPartSearchStore', () => {    
    const head  = Header('transactions/settle/merchandises/part', '가맹점 부분정산관리')
    const table = transactionHeader('mcht-part')
    const headers: Record<string, string> = {
        ...table.headers0,
        ...table.headers1,
        ...table.headers2,
        ...table.headers3,
        ...table.headers4,
        ...table.headers5,
        ...table.headers6,
    }
    const sub_headers: any = []
    head.getSubHeaderCol('거래 정보', table.headers0, sub_headers)
    head.getSubHeaderCol('가맹점 정보', table.headers1, sub_headers)
    head.getSubHeaderCol('PG사 정보', table.headers2, sub_headers)
    head.getSubHeaderCol('수수료 정보', table.headers3, sub_headers)
    head.getSubHeaderCol('개인 정보', table.headers4, sub_headers)
    head.getSubHeaderCol('결제 정보', table.headers5, sub_headers)
    head.getSubHeaderCol('기타 정보', table.headers6, sub_headers)

    head.sub_headers.value = sub_headers
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.flatten(head.headers.value)
    const metas = ref(table.chart)

    return {
        head,
        metas,
        table,
    }
})
