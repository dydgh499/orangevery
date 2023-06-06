import { Filter, Pagenation, SearchParams } from '@/views/types';
import { axios } from '@axios';
import * as XLSX from 'xlsx';

const setSearchParams = <SearchParams>(formatDate: any) => {
    const date = new Date()
    return <SearchParams>({
        page: 1,
        page_size: 10,
        s_dt: formatDate(new Date(date.getFullYear(), date.getMonth(), 1)),
        e_dt: formatDate(new Date(date.getFullYear(), date.getMonth() + 1, 0)),
    })
}
const excelprint = (headers:Filter, data:any, today:string, path:string) => {
    const rows = []
    const keys = Object.keys(headers) as Array<keyof Filter>;

    for (let i = 0; i < data.length; i++) {
        let row: Record<string, any> = {}
        for (let j = 0; j < keys.length; j++) {
            let idx = keys.indexOf(keys[j])
            if (idx > -1) {
                let key = headers[keys[j]].ko
                row[key] = data[i][keys[j]]
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
    const search = ref<string>('')
    
    const pagenation = reactive<Pagenation>({ total_count: 0, total_page: 1 })
    let header_count = 0;
  
    const setHeader = (ko: string, _key: string) => {
        if (_key in headers.value)
        {
            headers.value[_key].ko = ko
            headers.value[_key].idx = header_count++
        }
        else
            headers.value[_key] = { ko: ko, hidden: false, idx: header_count++ }
    }
    const sortHeader = () => {
        const keys = Object.keys(headers.value).sort((a, b) => headers.value[a].idx - headers.value[b].idx);
        let sorted = <any>({});
        for(let key of keys) {
            sorted[key] = headers.value[key];
        }
        headers.value = sorted
    }
    const get = async () => {
        const p = Object.assign(params, {search: search})
        try {
            const r = await axios.get('/api/v1/manager/' + path, { params: p })
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
    
    const setTable = async () => {
        const r = await get()
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
        const r = await get()
        if (r.status == 200) {
            excelprint(headers.value, r.data.content, today, path)
        }
    }

    const booleanTypeColor = (type: boolean | null) => {
        return Boolean(type) ? "default" : "success";
    };
    
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
        items, params, search, pagenation,
        create, edit,
        excel, setHeader, sortHeader, booleanTypeColor,
        filter, pagenationCouputed
    }
}
