import { Header } from '@/views/headers';
import { Searcher } from '@/views/searcher';

export const useSearchStore = defineStore('bonaejaSearchStore', () => {
    const store = Searcher('services/bonaejas')
    const head = Header('services/bonaejas', '문자발송 관리')
    const headers: Record<string, string | object> = {
        'code': '결과코드',
        'type': '메세지 타입',
        'sender': '발신자 전화번호',
        'receiver': '수신자 전화번호',
        'msg': '메세지 내용',
        'created_at': '발송시간',
    }
    head.main_headers.value = []
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.setFlattenHeaders()

    const metas = ref([
        {
            icon: 'ic-outline-payments',
            color: 'primary',
            title: '보내자 보유 잔액',
            stats: '0',
        },
        {
            icon: 'majesticons:message',
            color: 'default',
            title: 'SMS 발송가능 회수',
            stats: '0',
        },
        {
            icon: 'majesticons:message',
            color: 'success',
            title: 'LMS 발송가능 회수',
            stats: '0',
        },
        {
            icon: 'majesticons:message',
            color: 'info',
            title: 'MMS 발송가능 회수',
            stats: '0',
        },
    ])

    const getCodeTypeString = (code: number, res_msg:string) => {
        if(code == 1000)
            return '성공'
        else if(code == 500)
            return '전송중'
        else
            return res_msg
    }

    const getCodeTypeColor = (code: number) => {
        if(code == 1000)
            return 'success'
        else if(code == 500)
            return 'info'
        else
            return 'error'
    }

    const getMessegeTypeColor = (type: string) => {
        if(type == 'sms')
            return 'default'
        else if(type == 'lms')
            return 'success'
        else if(type == 'mms')
            return 'info'
        else
            return 'warning'
    }

    const exporter = async (type: number) => {
        const keys = Object.keys(headers);
        const r = await store.get(store.base_url, { params:store.getAllDataFormat()})
        let datas = r.data.content;
        for (let i = 0; i < datas.length; i++) {
            datas[i] = head.sortAndFilterByHeader(datas[i], keys)
            datas[i]['code'] = getCodeTypeString(datas[i]['code'], datas[i]['rsg_msg'])
        }
        type == 1 ? head.exportToExcel(datas) : head.exportToPdf(datas)
    }
    
    onMounted(async () => {
        const r = await store.getChartData()
        if(r.status == 200) {
            metas.value[0]['stats'] = r.data.data.TOTAL_DEPOSIT.toLocaleString() + ' ₩'
            metas.value[1]['stats'] = r.data.data.SMS_CNT.toLocaleString() + '건'
            metas.value[2]['stats'] = r.data.data.LMS_CNT.toLocaleString() + '건'
            metas.value[3]['stats'] = r.data.data.MMS_CNT.toLocaleString() + '건'
        }
    })
    
    return {
        store,
        head,
        exporter,
        metas,
        getCodeTypeString,
        getCodeTypeColor,
        getMessegeTypeColor,
    }
})
