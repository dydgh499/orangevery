import { Header } from '@/views/headers';
import { Searcher } from '@/views/searcher';
import type { MchtBlacklist, Merchandise } from '@/views/types';
import { axios } from '@axios';
import corp from '@corp';

export const useSearchStore = defineStore('mchtBlacklistSearchStore', () => {
    const store = Searcher('services/mcht-blacklists')
    const head = Header('services/mcht-blacklists', '가맹점 블랙리스트 관리')
    const headers: Record<string, string | object> = {
        'id': 'NO.',
        'mcht_name': '사업자명',
        'nick_name': '대표자명',
        'phone_num': '전화번호',
        'business_num': '사업자번호',
        'resident_num': '주민등록번호',
        'addr': '주소',
        'card_num': '카드번호',
        'block_reason': '차단 사유',
        'created_at': '생성 시간',
        'updated_at': '업데이트 시간',
        'extra_col': '더보기',
    }

    head.sub_headers.value = []
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.flatten(head.headers.value)

    const metas = ref([])

    const exporter = async () => {
        const keys = Object.keys(head.flat_headers.value)
        const r = await store.get(store.base_url, { params:store.getAllDataFormat()})
        let datas = r.data.content;
        for (let i = 0; i < datas.length; i++) {
            datas[i] = head.sortAndFilterByHeader(datas[i], keys)
        }
        head.exportToExcel(datas)
    }
    
    onMounted(async () => {})
    
    return {
        store,
        head,
        exporter,
        metas,
    }
})


export const useMchtBlacklistStore = defineStore('useMchtBlacklistStore', () => {
    const alert = <any>(inject('alert'))
    const mcht_blacklists = ref(<MchtBlacklist[]>([]))    

    onMounted(async () => { 
        getAllMchtBlacklist()
    })

    const getAllMchtBlacklist = async() => {
        if(Number(corp.pv_options.paid.use_mcht_blacklist)) {
            const r = await axios.get('/api/v1/services/mcht-blacklists/all?brand_id='+corp.id)
            mcht_blacklists.value = r.data
        }
    }

    const isMchtBlackList = (mcht: Merchandise) => {
        let blacklist = mcht_blacklists.value.find(obj => obj.mcht_name === mcht.mcht_name && mcht.mcht_name !== '')
        if(blacklist)
            return [true, blacklist]

        blacklist = mcht_blacklists.value.find(obj => obj.nick_name === mcht.nick_name && mcht.nick_name !== '')
        if(blacklist)
            return [true, blacklist]

        blacklist = mcht_blacklists.value.find(obj => obj.business_num === mcht.business_num && mcht.business_num !== '')
        if(blacklist)
            return [true, blacklist]
        
        blacklist = mcht_blacklists.value.find(obj => obj.phone_num === mcht.phone_num && mcht.phone_num !== '')
        if(blacklist)
            return [true, blacklist]

        blacklist = mcht_blacklists.value.find(obj => obj.resident_num === mcht.resident_num && mcht.resident_num !== '')
        if(blacklist)
            return [true, blacklist]

        blacklist = mcht_blacklists.value.find(obj => obj.addr === mcht.addr && mcht.addr !== '')
        if(blacklist)
            return [true, blacklist]
        return [false, null]
    }

    const customValidFormRequest = async(mcht: Merchandise) => {
        if (Number(corp.pv_options.paid.use_mcht_blacklist)) {
            let [result, blacklist] = isMchtBlackList(mcht)
            if(result)
                return await alert.value.show('해당 가맹점은 아래이유로 인해 블랙리스트로 등록된 가맹점입니다. 그래도 진행하시겠습니까?<br><br><b style="color:red">'+blacklist?.block_reason+'</b>')
            else
                return true
        }
        return true
    }
    
    return { isMchtBlackList, customValidFormRequest }
})
