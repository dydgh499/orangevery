import { CustomFilter, Filter, Pagenation, SalesForceFilter, SearchParams } from '@/views/types';
import { axios } from '@axios';
import * as XLSX from 'xlsx';

const setSearchParams = <SearchParams>(formatDate: any) => {
    const date = new Date()
    return <SearchParams>({
        page: 1,
        page_size: 10,
        search: '',
        s_dt: formatDate(new Date(date.getFullYear(), date.getMonth(), 1)),
        e_dt: formatDate(new Date(date.getFullYear(), date.getMonth() + 1, 0)),
        sales: <SalesForceFilter><unknown>([]),
        custom: <CustomFilter><unknown>([]),
    })
}
const excelprint = (headers:Filter, data:any, today:string, path:string) => {
    const rows = []
    const keys = Object.keys(headers.value) as Array<keyof Filter>;
    for (let i = 0; i < data.length; i++) {
        let row: Record<string, any> = {}
        let _keys: string[] = Object.keys(data[i])
        for (let j = 0; j < _keys.length; j++) {
            let idx = keys.indexOf(_keys[j])
            if (idx > -1) {
                let key = headers[_keys[j]].ko
                row[key] = data[i][_keys[j]]
            }
        }
        rows.push(row)
    }
    const file_nm = path + "_" + today + ".xlsx"
    const data_ws = XLSX.utils.json_to_sheet(rows)
    const wb = XLSX.utils.book_new()
    XLSX.utils.book_append_sheet(wb, data_ws, path)
    XLSX.writeFile(wb, file_nm)
}

export function Searcher<T>(_path: string, _type: T) {
    const filter = ref<any>(null)
    const alert = <any>(inject('alert'))
    const snackbar = <any>(inject('snackbar'))
    const formatDate = <any>(inject('$formatDate'))
    const errorHandler = <any>(inject('$errorHandler'))
    // -----------------------------
    const headers = ref<Filter>(JSON.parse(localStorage.getItem(_path) || "{}"))
    // -----------------------------
    const path = _path
    const items = ref<T[]>([])
    const router = useRouter()
    const params = reactive<SearchParams>(setSearchParams(formatDate))
    const pagenation = reactive<Pagenation>({ total_count: 0, total_page: 1 })
    let header_count = 0;
  
    const setHeader = (ko: string, _key: string) => {
        if (_key in headers.value)
            headers.value[_key].idx = header_count++
        else
            headers.value[_key] = { ko: ko, hidden: false, idx: header_count++ }
    }
    const sortHeader = () => {
        const keys = Object.keys(headers.value).sort((a, b) => headers.value[a].idx - headers.value[b].idx);
        let sortedObj = {};
        for(let key of keys) {
            sortedObj[key] = headers.value[key];
        }
        headers.value = sortedObj
    }
    const get = async (params: any) => {
        try {
            const r = await axios.get('/api/v1/manager/' + path, { params: params })
            return r
        } catch (e: any) {
            snackbar.value.show(e.response.data.message, 'error')
            return errorHandler(e)
        }
    }
    const create = () => {
        router.push('/' + path + '/create')
    }
    const edit = (id: number = 0) => {
        router.push('/' + path + '/edit/' + id)
    }
    const remove = async (id: number) => {
        if (await alert.value.show('정말 삭제하시겠습니까? 삭제하신 정보는 복구하실 수 없습니다.')) {
            //const r = await axios.delete('/api/v1/manager/'+path.value+'/'+focus_id.value, {params:params})
            await setTable()
            snackbar.value.show('삭제되었습니다.', 'primary')
        }
    }
    const setTable = async () => {
        const p = params
        const r = await get(p)
        if (r.status == 200) {
            const data = r.data
            let l_page = data.total / params.page_size
            items.value = data.content
            pagenation.total_count = data.total
            pagenation.total_page = parseInt(String(l_page > Math.floor(l_page) ? l_page + 1 : l_page))
        }
    }

    const excel = async () => {
        const dt    = new Date()
        const today = formatDate(new Date(dt.getFullYear(), dt.getMonth(), dt.getDay()))
        const r = await get(params)
        if (r.status == 200) {
            excelprint(headers.value, r.data.content, today, path)
        }
    }
    const pagenationCouputed = computed(() => {
        const firstIndex = items.value.length ? ((params.page - 1) * params.page_size) + 1 : 0
        const lastIndex = items.value.length + ((params.page - 1) * params.page_size)
        return `총 ${pagenation.total_count}개 항목 중 ${firstIndex} ~ ${lastIndex}개 표시`
    })

    watchEffect(() => {
        localStorage.setItem(path, JSON.stringify(headers.value))
    })
    
    watchEffect(() => {
    })
    return {
        headers, setTable,
        items, params, pagenation,
        create, edit, remove,
        excel, setHeader, sortHeader,
        alert, snackbar, filter, pagenationCouputed
    }
}
