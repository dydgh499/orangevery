import router from '@/router';
import { useRequestStore } from '@/views/request';
import type { Pagenation } from '@/views/types';
import { getUserLevel, isAbleModifyMcht } from '@axios';
import { DateFilters, StatusColors } from '@core/enums';
import corp from '@corp';
import { cloneDeep } from 'lodash';


const StatusColorSetter = () => {
    const booleanTypeColor = (type: boolean | null) => {
        return Boolean(type) ? "default" : "success";
    };
    
    const getSelectIdColor = (id: number | undefined) => {
        if (id == StatusColors.Default)
            return "default"
        else if (id == StatusColors.Primary)
            return "primary"
        else if (id == StatusColors.Success)
            return "success"
        else if (id == StatusColors.Info)
            return "info"
        else if (id == StatusColors.Warning)
            return "warning"
        else if (id == StatusColors.Error)
            return "error"
        else
            return 'default'
    }
    return {
        booleanTypeColor,
        getSelectIdColor,
    }
}

const chartSetter = (base_url: string) => {
    const { get } = useRequestStore()
    const chart_process = ref(false)

    const getChartProcess = () => {
        return chart_process.value
    }
    const setChartProcess = () => {
        chart_process.value = false
    }

    const _getChartData = async(params: any) => {
        const r = await get(base_url+'/chart', { params: params })
        chart_process.value = true
        return r
    }
    const getPercentage = (n:number, d:number) => {
        return d === 0 ? 0 : Number(((n / d) * 100).toFixed(2))
    }

    return {
        get,
        getChartProcess,
        setChartProcess,
        _getChartData,
        getPercentage,
    }
}

export const Searcher = (path: string) => {
    const base_url = '/api/v1/manager/'+path
    // -----------------------------
    let before_search   = ''
    const items         = shallowRef(<[]>([]))
    const params        = reactive<any>({})
    const pagenation    = reactive<Pagenation>({ total_count: 0, total_page: 1, total_range: 0})
    const is_skeleton   = ref(true)
    const {
        get,
        getChartProcess,
        setChartProcess,
        _getChartData,
        getPercentage,
    } = chartSetter(base_url)
    const {
        booleanTypeColor,
        getSelectIdColor,
    } = StatusColorSetter()
    
    const getChartData = async() => { return _getChartData(getParams())  }

    const edit = (id: number = 0) => {
        const cond_1 = (path === 'merchandises' || path === 'merchandises/pay-modules' || path === 'merchandises/terminals') 
        && (getUserLevel() >= 35 || isAbleModifyMcht())
        const cond_2 = getUserLevel() >= 35
        if(cond_1 || cond_2) {
            if(id == 0)
                router.push('/' + path + '/create')
            else
                router.push('/' + path + '/edit/' + id)
        }
    }

    const getSearch = () => {
        const search = (document.getElementById('search') as HTMLInputElement)
        return search ? search.value : ''
    }

    const getParams = () => {
        const p = cloneDeep(params)
        p.search = getSearch()        
        if(before_search != p.search) {
            params.page = 1
            setChartProcess()
            before_search = p.search
        }
        return p
    }

    const updateQueryString = (obj: any) => {
        const is_chart_update = Object.keys(obj).some(key => !['page', 'page_size', 'search'].includes(key))
        const query = {...router.currentRoute.value.query, ...obj}
        if(is_chart_update) {
            params.page = 1
            query.page = 1
        }
        router.push({query: query})
        if(is_chart_update) {
            setChartProcess()
        }
    }
    
    const setTable = async() => {
        const p = getParams()
        const r = await get(base_url, {params: p})
        if (r.status == 200) {
            let l_page = r.data.total / params.page_size

            items.value = r.data.content
            pagenation.total_range = r.data.content.length
            pagenation.total_count = r.data.total
            pagenation.total_page = parseInt(String(l_page > Math.floor(l_page) ? l_page + 1 : l_page))
        }
        is_skeleton.value = false
        return r.data.content
    }
    
    const pagenationCouputed = computed(() => {
        const firstIndex = pagenation.total_range ? ((params.page - 1) * params.page_size) + 1 : 0
        const lastIndex = pagenation.total_range + ((params.page - 1) * params.page_size)
        return `총 ${pagenation.total_count}개 항목 중 ${firstIndex} ~ ${lastIndex}개 표시`
    })
    
    const getAllDataFormat = () => {
        const p  = cloneDeep(params)  
        p.search = getSearch()
        p.page_size = 99999999
        p.page = 1
        return p
    }

    const getItems = computed(() => {
        return items.value
    })
    
    return {
        setTable, getItems, base_url, updateQueryString,
        items, params, pagenation, getChartProcess, setChartProcess,
        edit, getChartData, getPercentage,
        get, booleanTypeColor, getSelectIdColor, getAllDataFormat,
        pagenationCouputed, is_skeleton
    }
}

export const DateSetter = (props: any, formatDate: any, formatTime: any) => {
    const range_date = ref(<string[]>(['', '']))
    const date = ref(<string>(''))
    const date_selecter = ref()
    const route = useRoute()

    const getDateFormat = (date: Date) => {
        if (corp.pv_options.free.use_search_date_detail && props.date_filter_type == DateFilters.DATE_RANGE)
            return formatDate(date) + " " + formatTime(date)
        else
            return formatDate(date)
    }
    const getRangeFormat = (dates: Date[]) => {
        if (props.date_filter_type == DateFilters.DATE_RANGE) {
            const setRangeFormat = (date: Date) => {
                if (formatTime(date) === "00:00:00" || formatTime(date) === "23:59:59")
                    return formatDate(date)
                else
                    return formatDate(date) + " " + formatTime(date)
            }
            const s_date = setRangeFormat(dates[0])
            const e_date = setRangeFormat(dates[1])
            return s_date + "  -  " + e_date
        }
        else if (props.date_filter_type == DateFilters.SETTLE_RANGE) {

            return formatDate(dates[0]) + "  -  " + formatDate(dates[1])
        }
    }

    const setDateRange = () => {
        let s_date = undefined
        let e_date = undefined
        const date = new Date();
        if (date_selecter.value == 'today') {
            s_date = new Date(date.getFullYear(), date.getMonth(), date.getDate(), 0, 0, 0)
            e_date = new Date(date.getFullYear(), date.getMonth(), date.getDate(), 23, 59, 59)
        }
        else if (date_selecter.value == '1 day') {
            s_date = new Date(date.getFullYear(), date.getMonth(), date.getDate() - 1, 0, 0, 0)
            e_date = new Date(date.getFullYear(), date.getMonth(), date.getDate() - 1, 23, 59, 59)
        }
        else if (date_selecter.value == '3 day') {
            s_date = new Date(date.getFullYear(), date.getMonth(), date.getDate() - 3, 0, 0, 0)
            e_date = new Date(date.getFullYear(), date.getMonth(), date.getDate() - 1, 23, 59, 59)
        }
        else if (date_selecter.value == '1 mon') {
            s_date = new Date(date.getFullYear(), date.getMonth() - 1, 1, 0, 0, 0)
            e_date = new Date(date.getFullYear(), date.getMonth(), 0, 23, 59, 59)
        }
        else if (date_selecter.value == '3 mon') {
            s_date = new Date(date.getFullYear(), date.getMonth() - 3, 1, 0, 0, 0)
            e_date = new Date(date.getFullYear(), date.getMonth(), 0, 23, 59, 59)
        }
        else {
            s_date = new Date(date.getFullYear(), date.getMonth(), 1, 0, 0, 0)
            e_date = new Date(date.getFullYear(), date.getMonth() + 1, 0, 23, 59, 59)
        }
        range_date.value[0] = getDateFormat(s_date)
        range_date.value[1] = getDateFormat(e_date)
    }

    const init = (store: any) => {
        if (route.query.s_dt && route.query.e_dt) {
            range_date.value[0] = route.query.s_dt as string
            range_date.value[1] = route.query.e_dt as string
        }
        else if (route.query.dt)
            date.value = route.query.dt as string
        else {
            if (props.date_filter_type == DateFilters.DATE_RANGE) {
                const date = new Date()
                const s_date = new Date(date.getFullYear(), date.getMonth(), 1, 0, 0, 0)
                const e_date = new Date(date.getFullYear(), date.getMonth() + 1, 0, 23, 59, 59)
                range_date.value[0] = getDateFormat(s_date)
                range_date.value[1] = getDateFormat(e_date)
            }
            else if (props.date_filter_type == DateFilters.SETTLE_RANGE) {
                const date = new Date()
                const s_date = new Date(date.getFullYear(), date.getMonth(), 1, 0, 0, 0)
                const e_date = date
                range_date.value[0] = getDateFormat(s_date)
                range_date.value[1] = getDateFormat(e_date)
            }
            else if (props.date_filter_type == DateFilters.DATE){
                date.value = formatDate(new Date())
            }
        }
        dateChanged(store)
    }

    const dateChanged = (store: any) => {
        if (props.date_filter_type == DateFilters.DATE_RANGE || props.date_filter_type == DateFilters.SETTLE_RANGE) {
            const s_date = new Date(range_date.value[0])
            const e_date = new Date(range_date.value[1])
            store.params.s_dt = getDateFormat(s_date)
            store.params.e_dt = getDateFormat(e_date)
            store.updateQueryString({ s_dt: store.params.s_dt, e_dt: store.params.e_dt })
        }
        else if (props.date_filter_type == DateFilters.DATE) {
            const dt = new Date(date.value)
            store.params.dt = formatDate(dt)
            store.updateQueryString({ dt: store.params.dt })
        }
    }
    
    return {
        getRangeFormat,
        setDateRange,
        init,
        dateChanged,
        range_date,
        date,
        date_selecter,
    }
}
