import { Filter, Pagenation, SearchParams } from '@/views/types';
import { axios } from '@axios';
import * as XLSX from 'xlsx';

export function Searcher<T>(_path: string, _type: T) {
    const filter = ref<any>(null)
    const alert = <any>(inject('alert'))
    const snackbar = <any>(inject('snackbar'))
    const errorHandler = <any>(inject('$errorHandler'))
    const formatDate = <any>(inject('$formatDate'))
    // -----------------------------
    const headers = ref<Filter[]>(JSON.parse(localStorage.getItem(_path) || "[]"))
    const extra_headers = ref<Filter[]>(JSON.parse(localStorage.getItem(_path) || "[]"))
    // -----------------------------
    const app = getCurrentInstance()
    const path = _path
    const items = ref<T[]>([])
    const router = useRouter()
    const params = reactive<SearchParams>(setSearchParams())
    const pagenation = reactive<Pagenation>({ total_count: 0, total_page: 1 })
    const type = _type as T

    function setSearchParams<SearchParams>() {
        const date = new Date()
        return <SearchParams>({
            page: 1,
            page_size: 10,
            search: '',
            s_dt: formatDate(new Date(date.getFullYear(), date.getMonth(), 1)),
            e_dt: formatDate(new Date(date.getFullYear(), date.getMonth() + 1, 0)),
        })
    }
    function setExtraHeader(ko: string, key: string) {
        const keys: string[] = headers.value.map(obj => obj.key);
        if (keys.includes(key) == false)
            extra_headers.value.push(<Filter>{ ko: ko, key: key, hidden: false });
    }
    function setHeader(ko: string, key: string) {
        const keys: string[] = headers.value.map(obj => obj.key);
        if (keys.includes(key) == false)
            headers.value.push(<Filter>{ ko: ko, key: key, hidden: false });
    }

    async function get(params: any) {
        try {
            const r = await axios.get('/api/v1/manager/' + path, { params: params })
            return r
        } catch (e: any) {
            snackbar.value.show(e.response.data.message, 'error')
            return errorHandler(e);
        }
    }
    function create() {
        router.push('/' + path + '/create')
    }
    function edit(id: number = 0) {
        router.push('/' + path + '/edit/' + id)
    }
    async function remove(id: number) {
        if (await alert.value.show('정말 삭제하시겠습니까? 삭제하신 정보는 복구하실 수 없습니다.')) {
            //const r = await axios.delete('/api/v1/manager/'+path.value+'/'+focus_id.value, {params:params})
            await setTable()
            snackbar.value.show('삭제되었습니다.', 'primary')
        }
    }

    async function setTable() {
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

    async function excel() {
        function currentDate(source: Date) {
            function left_pad(value: number) { return value >= 10 ? value : `0${value}` }
            const year = source.getFullYear()
            const month = left_pad(source.getMonth() + 1)
            const day = left_pad(source.getDate())
            return [year, month, day].join('-')
        }

        const p = params
        const r = await get(p)
        if (r.status == 200) {
            const rows = []
            const keys = headers.value.map(obj => obj.key)
            const data = r.data.content
            for (let i = 0; i < data.length; i++) {
                let row: Record<string, any> = {}
                let _keys: string[] = Object.keys(data[i])
                for (let j = 0; j < _keys.length; j++) {
                    let idx = keys.indexOf(_keys[j])
                    if (idx > -1) {
                        let key = headers.value[idx].ko
                        row[key] = data[i][_keys[j]]
                    }
                }
                rows.push(row)
            }

            const file_nm = path + "_" + currentDate(new Date()) + ".xlsx"
            const data_ws = XLSX.utils.json_to_sheet(rows)
            const wb = XLSX.utils.book_new()
            XLSX.utils.book_append_sheet(wb, data_ws, path)
            XLSX.writeFile(wb, file_nm)
        }
    }
    function findValueByKey(obj: typeof type, key: string) {
        // 객체가 null이거나 정의되지 않은 경우 빈 값을 반환합니다.
        if (obj === null || typeof obj !== 'object') {
            return undefined;
        }
        const keys = key.split('.');
        let value = obj;

        // 키 배열을 반복하면서 중첩된 객체를 탐색합니다.
        for (let i = 0; i < keys.length; i++) {
            const nestedKey:string = keys[i];
            if (value.hasOwnProperty(nestedKey)) {
                value = value[nestedKey];
            } else {
                // 키를 찾지 못한 경우 빈 값을 반환합니다.
                return undefined;
            }
        }
        return value;
    }
    watchEffect(() => {
        localStorage.setItem(path, JSON.stringify(headers.value))
    })

    return {
        headers, extra_headers, setTable,
        items, params, pagenation,
        create, edit, remove,
        excel, setHeader, setExtraHeader, findValueByKey,
        app, alert, snackbar, filter
    }
}
