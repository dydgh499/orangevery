import { getUserLevel } from '@/plugins/axios'
import { Header } from '@/views/headers'
import { Searcher } from '@/views/searcher'
import { useStore } from '@/views/services/pay-gateways/useStore'
import type { OptionGroup } from '@/views/types'

export const getOptionGroupInfo = (_option_group: string) => {
    const array = [];
    const option_groups = <OptionGroup[]>(JSON.parse(_option_group))
    for (let i = 0; i < option_groups.length; i++) {
        array.push({
            '옵션명': option_groups[i].option_name,
            '옵션가격': option_groups[i].option_price,
            '주문수량': option_groups[i].count,
        })        
    }
    return array
}

export const useSearchStore = defineStore('orderSearchStore', () => {
    const store = Searcher('transactions/orders')
    const head = Header('transactions/orders', '주문 관리')
    const { pgs, pss } = useStore()

    const getTransactionCols = () => {
        return {
            'id': 'NO.',
            'appr_num': '승인번호',
            'trx_at': '거래시간',
            'amount': '총결제금액',
        }
    }

    const getMerchandiseCols = () => {
        return {            
            'user_name': '가맹점 ID',
            'mcht_name': '가맹점 상호',
        }
    };

    const getPGCols = () => {
        const headers_3:Record<string, string> = {}
        if(getUserLevel() >= 35) {
            headers_3['pg_id'] = 'PG사'
            headers_3['ps_id'] = '구간'
            headers_3['ps_fee'] = '구간 수수료'
        }
        return headers_3
    }

    const getPaymentCols = () => {
        const headers_6:Record<string, string> = {}
        headers_6['issuer'] = '발급사'
        headers_6['card_num'] = '카드번호'
        headers_6['buyer_name'] = '구매자명'
        headers_6['buyer_phone'] = '구매자 연락처'
        headers_6['item_name'] = '상품명'    
        return headers_6
    }

    const getDeliveryCols = () => {
        return {
            'addr': '배송주소',
            'detail_addr': '상세주소',
            'note': '메모사항',
            'option_groups': '옵션정보',    
        }
    }

    const headers: Record<string, string> = {
        ...getTransactionCols(),
        ...getMerchandiseCols(),
        ...getPGCols(),
        ...getPaymentCols(),
        ...getDeliveryCols(),
    }

    const sub_headers: any = []
    head.getSubHeaderCol('거래 정보', getTransactionCols(), sub_headers)
    head.getSubHeaderCol('가맹점 정보', getMerchandiseCols(), sub_headers)
    head.getSubHeaderCol('PG사 정보', getPGCols(), sub_headers)
    head.getSubHeaderCol('결제 정보', getPaymentCols(), sub_headers)
    head.getSubHeaderCol('배송 정보', getDeliveryCols(), sub_headers)

    head.sub_headers.value = sub_headers
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.flatten(head.headers.value)

    const exporter = async () => {
        const r = await store.get(store.base_url, { params: store.getAllDataFormat() })
        printer(r.data.content)
    }

    const printer = (datas: any[]) => {
        const keys = Object.keys(head.flat_headers.value)
        for (let i = 0; i < datas.length; i++) {
            datas[i]['pg_id'] = pgs.find(pg => pg['id'] === datas[i]['pg_id'])?.pg_name as string
            datas[i]['ps_id'] = pss.find(ps => ps['id'] === datas[i]['ps_id'])?.name as string
            datas[i]['option_groups'] = getOptionGroupInfo(datas[i]['option_groups'])
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
