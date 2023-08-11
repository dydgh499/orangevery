import { Pagenation } from '@/views/types'
import { axios, user_info } from '@axios'
import { cloneDeep } from 'lodash'

export function Searcher(path: string) {
    const snackbar = <any>(inject('snackbar'))
    const errorHandler = <any>(inject('$errorHandler'))
    // -----------------------------
    const items = ref<[]>([])
    const router = useRouter()
    const params = reactive<any>({page:1, page_size:10})
    const pagenation = reactive<Pagenation>({ total_count: 0, total_page: 1 })
    const chart_process = ref(false)
    let before_search = ''

    const getChartProcess = () => {
        return chart_process.value
    }

    const setChartProcess = () => {
        params.page = 1
        chart_process.value = false
    }

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
        if(user_info.value.level > 30)
            router.push('/' + path + '/edit/' + id)
    }
    
    const getChartData = async() => {
        const p = cloneDeep(params)
        const search = (document.getElementById('search') as HTMLInputElement)
        const current_search = search ? search.value : ''
        p.search = current_search
        
        try {
            const r = await axios.get('/api/v1/manager/'+path+'/chart', { params: p })
            chart_process.value = true
            return r
        } catch (e: any) {
            snackbar.value.show(e.response.data.message, 'error')
            return errorHandler(e)
        }
    }

    const getPercentage = (n:number, d:number) => {
        if (d === 0)
            return 0;
          return Number(((n / d) * 100).toFixed(2))
    }

    const setTable = async() => {
        const search = (document.getElementById('search') as HTMLInputElement)
        const current_search = search ? search.value : ''
        
        if(before_search != current_search) {
            setChartProcess()
            before_search = current_search
        }
            
        const p = cloneDeep(params)
        p.search = current_search
        
        const r = await get(p)
        if (r.status == 200) {
            const data = r.data
            let l_page = data.total / params.page_size
            items.value = data.content
            pagenation.total_count = data.total
            pagenation.total_page = parseInt(String(l_page > Math.floor(l_page) ? l_page + 1 : l_page))
        }
        return r.data.content
    }
    const getAllDataFormat = () => {
        const p = cloneDeep(params)
        const search = (document.getElementById('search') as HTMLInputElement)
        const current_search = search ? search.value : ''

        p.search = current_search
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
            return 'default'
    }
    const pagenationCouputed = computed(() => {
        const firstIndex = items.value.length ? ((params.page - 1) * params.page_size) + 1 : 0
        const lastIndex = items.value.length + ((params.page - 1) * params.page_size)
        return `총 ${pagenation.total_count}개 항목 중 ${firstIndex} ~ ${lastIndex}개 표시`
    })
    const getItems = computed(() => {
        return items.value
        //return await setTable()
    })
    return {
        setTable, getItems,
        items, params, pagenation, getChartProcess, setChartProcess,
        create, edit, getChartData, getPercentage,
        get, booleanTypeColor, getSelectIdColor, getAllDataFormat,
        pagenationCouputed
    }
}
