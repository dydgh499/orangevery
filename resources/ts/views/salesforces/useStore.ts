import { Header } from '@/views/headers';
import { Searcher } from '@/views/searcher';
import type { Options, Salesforce } from '@/views/types';
import { axios } from '@axios';
import corp from '@corp';

const levels = corp.pv_options.auth.levels

export const getLevelByIndex = (level:number) => {
    switch(level) {
        case 13:
            return 0;
        case 15:
            return 1;
        case 17:
            return 2;
        case 20:
            return 3;
        case 25:
            return 4;
        case 30:
            return 5;
        default:
            return 0;
    }
}

export const settleDays = () => {
    return <Options[]>([
        {id:0, title:'일요일'}, {id:1, title:'월요일'},
        {id:2, title:'화요일'}, {id:3, title:'수요일'},
        {id:4, title:'목요일'}, {id:5, title:'금요일'},
        {id:6, title:'토요일'}, 
    ])    
}

export const settleCycles = () => {
    return <Options[]>([
        {id:0, title:'하루씩 정산'}, {id:7, title:'1주일씩 정산'},
        {id:14, title:'2주일씩 정산'}, {id:30, title:'한달씩 정산(30일)'},
    ])
}

export const settleTaxTypes = () => {
    return <Options[]>([
        {id:0, title:'세율 없음'}, {id:1, title:'3.3%'},
        {id:2, title:'10%'}, {id:3, title:'10+3.3%'},
    ])
}

export const salesLevels = () => {
    const sales = <Options[]>([]);
    if(levels.sales0_use)
        sales.push({id: 13, title: levels.sales0_name})
    if(levels.sales1_use)
        sales.push({id: 15, title: levels.sales1_name})
    if(levels.sales2_use)
        sales.push({id: 17, title: levels.sales2_name})
    if(levels.sales3_use)
        sales.push({id: 20, title: levels.sales3_name})
    if(levels.sales4_use)
        sales.push({id: 25, title: levels.sales4_name})
    if(levels.sales5_use)
        sales.push({id: 30, title: levels.sales5_name})
    return sales
}

export const allLevels = () => {
    const sales = salesLevels()
    sales.unshift(<Options>({id: 10, title: '가맹점'}))
    sales.push(<Options>({id: 40, title: '본사'}))
    if(levels.dev_use)
        sales.push(<Options>({id: 50, title: levels.dev_name}))
    return sales
}

export const useSearchStore = defineStore('salesSearchStore', () => {
    const store = Searcher('salesforces')
    const head  = Header('salesforces', '결제모듈 관리')
    const all_sales = salesLevels()
    const all_cycles = settleCycles()
    const all_days = settleDays()
    const tax_types = settleTaxTypes()
    
    const headers: Record<string, string> = {
        'id' : 'NO.',
        'level' : '등급',
        'user_name' : '영업점 ID',
        'settle_cycle' : '정산 주기',
        'settle_day' : '정산 요일',
        'settle_tax_type': '정산 세율',
        'nick_name' : '대표자명',
        'phone_num' : '연락처',
        'resident_num' : '사업자등록번호',
        'business_num' : '주민등록번호',
        'sector' : '업종',
        'addr' : '주소',
        'acct_bank_nm' : '은행',
        'acct_bank_cd' : '은행코드',
        'acct_nm' : '예금주',
        'acct_num' : '계좌번호',
        'last_settle_dt': '마지막 정산일',
        'created_at' : '생성시간',
        'updated_at' : '업데이트시간',
    }
    head.main_headers.value = [];
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.setFlattenHeaders()
    
    const exporter = async (type: number) => {
        const keys = Object.keys(headers);
        const r = await store.get(store.getAllDataFormat())
        let datas = r.data.content;
        for (let i = 0; i < datas.length; i++) {
            datas[i]['level'] = all_sales.find(obj => obj['id'] === datas[i]['level'])?.title as string
            datas[i]['settle_cycle'] = all_cycles.find(obj => obj['id'] === datas[i]['settle_cycle'])?.title as string
            datas[i]['settle_day'] = all_days.find(obj => obj['id'] === datas[i]['settle_day'])?.title as string
            datas[i]['settle_tax_type'] = tax_types.find(obj => obj['id'] === datas[i]['settle_tax_type'])?.title as string

            datas[i] = head.sortAndFilterByHeader(datas[i], keys)
        }
        type == 1 ? head.exportToExcel(datas) : head.exportToPdf(datas)
    }
    return {
        store,
        head,
        exporter,
    }
})

export const useSalesFilterStore = defineStore('salesFilterStore', () => {
    const sales = Array.from({ length: 6 }, () => ref<any[]>([]));
    onMounted(async () => {
        await classification()
    })
    const classification = async () => {
        const r = await axios.get('/api/v1/manager/salesforces/classification')
        const keys = Object.keys(r.data);
        for (let index = 0; index < keys.length; index++) {           
            sales[index].value = r.data[keys[index]]
        }
    }
    return {
        sales
    }
})

export const useUpdateStore = defineStore('salesUpdateStore', () => {
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
        sector: '',
        passbook_img: null,
        id_img: null,
        contract_img: null,
        bsin_lic_img: null,
        acct_num: '',
        acct_nm: '',
        acct_bank_nm: '',
        acct_bank_cd: '',
        updated_at: undefined,
        id_file: undefined,
        passbook_file: undefined,
        contract_file: undefined,
        bsin_lic_file: undefined,
        level: 13,
        settle_cycle: 0,
        settle_day: 0
    })
    return {
        path, item
    }
})
