import axios from '@axios'
import { defineStore } from 'pinia'
import { Ref } from 'vue'
import * as XLSX from 'xlsx'

interface SearchParams {
    page: Ref<number>,
    page_size: Ref<number>,
    s_dt?: Ref<Date | null>,
    e_dt?: Ref<Date | null>,
    search?: Ref<string | null>,
}

interface Pagenation {
    total_count : Ref<number>,
    total_page  : Ref<number>,
}

interface Filter {
    key: string,
    ko: string,
    hidden: boolean,
}

export const useSearchStore = defineStore('searchStore', () => {
    const headers   = ref<Filter[]>(JSON.parse(localStorage.getItem('merchandises') || "[]"))
    // -----------------------------
    const path      = ref('');
    const items     = ref<any[]>([])
    const params    = <SearchParams>({
      page: ref(1),
      page_size: ref(10),
      s_dt: ref(null),
      e_dt: ref(null),
      search: ref(null),
    })
    const pagenation = <Pagenation>({
      total_count: ref(0),
      total_page: ref(1),
    })
    // -----------------------------
    const isLoading = ref(true)
    const isFilter  = ref(false)

    function setHeader(ko:string, key:string) {
        console.log(headers.value);
        const keys: string[] = headers.value.map(obj => obj.key);
        if(keys.includes(key) == false)
        {
            headers.value.push(<Filter>{ko: ko, key: key, hidden: false});
            localStorage.setItem('merchandises', JSON.stringify(headers.value))
        }
    }

    async function get(params: any) {
        isLoading.value = true
        const r = await axios.get('/api/v1/manager/merchandises', { params: params})
        isLoading.value = false
        if(r.status == 401 || r.status == 403)
            console.log(r.data)
        return r
    }

    async function setTable()
    {
        const p = {
            page: params.page.value,
            page_size: params.page_size.value,
            s_dt: params.s_dt?.value,
            e_dt: params.e_dt?.value,
            search: params.search?.value, 
        } 
        const r = await get(p)
        if(r.status == 200)
        {
            const data = r.data
            let l_page = data.total / params.page_size.value
            items.value = data.content
            pagenation.total_count.value = data.total
            pagenation.total_page.value = l_page > Math.floor(l_page) ? l_page + 1 : l_page      
        }
    }

    async function excel() {
        function currentDate(source:Date) {
            function left_pad(value:number) { return value >= 10 ? value : `0${value}` }
            const year  = source.getFullYear() 
            const month = left_pad(source.getMonth() + 1) 
            const day   = left_pad(source.getDate()) 
            return [year, month, day].join('-') 
        }            

        const p = {
            page: params.page.value,
            page_size: 999999999999,
            s_dt: params.s_dt?.value,
            e_dt: params.e_dt?.value,
            search: params.search?.value, 
        } 
        const r = await get(p)
        if(r.status == 200)
        {        
            const rows  = []    
            const keys  = headers.value.map(obj => obj.key)
            const data  = r.data.content
            for (let i = 0; i < data.length; i++) 
            {
                let row : Record<string, any> = {}
                let _keys :string[] = Object.keys(data[i])
                for (let j = 0; j < _keys.length; j++) 
                {
                    let idx = keys.indexOf(_keys[j])
                    if(idx > -1)
                    {
                        let key = headers.value[idx].ko
                        row[key] = data[i][_keys[j]]
                    }              
                }
                rows.push(row)
            }

            const file_nm = path.value+"_"+currentDate(new Date()) + ".xlsx"
            const data_ws = XLSX.utils.json_to_sheet(rows)
            const wb = XLSX.utils.book_new()
            XLSX.utils.book_append_sheet(wb, data_ws, path.value)
            XLSX.writeFile(wb, file_nm)
        }
    }
    const paginationData = computed(() => {
      const firstIndex = items.value.length ? ((params.page.value - 1) * params.page_size.value) + 1 : 0
      const lastIndex = items.value.length + ((params.page.value - 1) * params.page_size.value)
      return `총 ${pagenation.total_count.value}개 항목 중 ${firstIndex} ~ ${lastIndex}개 표시`
    })

    const watchParam = watchEffect(() => {
        setTable()
    })
    const watchFilter = watchEffect(() => {
        localStorage.setItem('merchandises', JSON.stringify(headers.value))
    })
    return {
        path, headers,
        items, params, pagenation, 
        get, excel, setHeader,
        paginationData, 
        isLoading, isFilter
    }
})

export default {useSearchStore}
