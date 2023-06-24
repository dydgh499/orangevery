import { Pagenation } from '@/views/types';
import { axios } from '@axios';
import { cloneDeep } from 'lodash';

export function Searcher(path: string) {
    const snackbar = <any>(inject('snackbar'))
    const errorHandler = <any>(inject('$errorHandler'))
    // -----------------------------
    const items = ref<[]>([])
    const router = useRouter()
    const params = reactive<any>({page:1, page_size:10})
    const search = ref<string>('')

    const pagenation = reactive<Pagenation>({ total_count: 0, total_page: 1 })

    const get = async (p: object) => {
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
        const p = cloneDeep(params)
        p.search = search.value
        
        const r = await get(p)
        if (r.status == 200) {
            const data = r.data
            let l_page = data.total / params.page_size
            items.value = data.content
            pagenation.total_count = data.total
            pagenation.total_page = parseInt(String(l_page > Math.floor(l_page) ? l_page + 1 : l_page))
        }
    }
    const getAllDataFormat = () => {
        const p = cloneDeep(params)
        p.search = search.value
        p.page_size = 99999999
        p.page = 1
        return p
    }

    const booleanTypeColor = (type: boolean | null) => {
        return Boolean(type) ? "default" : "success";
    };
    
    const getSelectIdColor = (id: number | undefined) => {
        if (id == 0)
            return "default"
        else if (id == 1)
            return "primary"
        else if (id == 2)
            return "success"
        else if (id == 3)
            return "info"
        else if (id == 4)
            return "warning"
        else if (id == 5)
            return "error"
        else
            return 'default';
    }
    const pagenationCouputed = computed(() => {
        const firstIndex = items.value.length ? ((params.page - 1) * params.page_size) + 1 : 0
        const lastIndex = items.value.length + ((params.page - 1) * params.page_size)
        return `총 ${pagenation.total_count}개 항목 중 ${firstIndex} ~ ${lastIndex}개 표시`
    })

    return {
        setTable,
        items, params, search, pagenation,
        create, edit,
        get, booleanTypeColor, getSelectIdColor, getAllDataFormat,
        pagenationCouputed
    }
}
