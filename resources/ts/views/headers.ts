import { ExcelExporter } from '@/views/excel';
import { Filter } from '@/views/types';
import _ from 'lodash';

const SubHeader = (path: string) => {
    const headers = ref<Filter>(JSON.parse(localStorage.getItem(path) || "{}"))
    const sub_headers = ref<any[]>([])

    const getSubHeaderFormat = (ko: string, s_col: string, e_col: string, type: string, width: number) => {
        return {
            'ko': ko,
            's_col': s_col,
            'e_col': e_col,
            'type': type,
            'width': width,
        }
    }

    const getSubHeaders = () => {
        const _sub_headers = []
        const getVisiableLength = (header: Filter, keys: string[], s_idx: number, e_idx: number) => {
            let length = 0  
            for (let i = s_idx; i <= e_idx; i++) {
                length += header[keys[i]].visible ? 1 : 0
            }
            return length
        }
        for (let i = 0; i < sub_headers.value.length; i++) {
            let length = 0
            if(sub_headers.value[i].type === 'string') {
                const keys = Object.keys(headers.value)
                const s_idx = keys.indexOf(sub_headers.value[i].s_col)
                const e_idx = keys.indexOf(sub_headers.value[i].e_col)
                length = getVisiableLength(headers.value, keys, s_idx, e_idx)
            }
            else {
                const _object = <Filter>(headers.value[sub_headers.value[i].s_col])
                const _keys = Object.keys(_object)
                length = getVisiableLength(_object, _keys, 0, _keys.length - 1)
            }
            _sub_headers.push({
                ko: sub_headers.value[i].ko,
                width: length
            })
        }
        return _sub_headers
    }
    const getSubHeaderComputed = computed(() => { return getSubHeaders() })
    
    watchEffect(() => {
        localStorage.setItem(path, JSON.stringify(headers.value))
    })

    return {
        headers, sub_headers,
        getSubHeaderFormat,
        getSubHeaderComputed
    }
}

export const Header = (path: string, file_name: string) => {
    let header_count = 0
    const filter = ref<any>(null)
    const flat_headers = ref<Filter>({})
    const {
        headers, sub_headers,
        getSubHeaderFormat,
        getSubHeaderComputed
    } = SubHeader(path)

    const { exportToExcel, setHeaderStyle } = ExcelExporter(sub_headers, flat_headers, file_name)

    const _init = (_headers: object) => {
        const getLocalStorageCols = (localstrage_headers: Filter, depth: number) => {
            if(depth > 1)
                return []
            else {
                return Object.keys(localstrage_headers).map(key => {
                    if(localstrage_headers[key].visible !== undefined)
                        return localstrage_headers[key].ko
                    else if(typeof localstrage_headers[key] === 'object')
                        return getLocalStorageCols(localstrage_headers[key] as Filter, ++depth)
                    else
                        return []    
                }).flat()
            }
        }
    
        const getRuntimeCols = (runtime_headers: object, depth: number) => {
            if(depth > 1)
                return []
            else {
                return Object.values(runtime_headers).map(key => {
                    if(typeof key !== 'object')
                        return key
                    else
                        return getRuntimeCols(key, ++depth)
                }).flat()    
            }
        }

        const local_cols = getLocalStorageCols(headers.value, 0)
        const runtime_cols = getRuntimeCols(_headers, 0)

        if (!_.isEqual(local_cols, runtime_cols)) {
            headers.value = {}
            return
        }
    }

    const initHeader = (_headers: object, result: Filter, depth=0): Filter => {
        if(depth === 0)
            _init(_headers)
        for (const [key, value] of Object.entries(_headers)) {
            if (typeof value === 'object')
                result[key] = initHeader(value, {}, ++depth)
            else {
                if(headers.value[key])
                    result[key] = headers.value[key]
                else
                    result[key] = { ko: value, visible: true, idx: header_count++ };
            }
        }
        return result;
    }

    const getDepth = (item: object, _depth: number): number => {
        if (_.isObject(item)) {
            let maxDepth = 0;
            _depth++;
            _.forEach(item, (value:any) => {
                if (!_.isNull(value)) {
                    const depth = getDepth(value, _depth);
                    maxDepth = Math.max(maxDepth, depth);
                }
            });
            return maxDepth;
        } 
        else
            return _depth;
    };

    const flatten = (object: any): Filter => {
        const result: { [key: string]: any } = {};
        for (const key of Object.keys(object)) {
            const val = object[key]
            if (getDepth(val, 0) !== 1) {
                for (const sub_key of Object.keys(val)) {
                    result[key + "." + sub_key] = val[sub_key]
                }
            }
            else
                result[key] = val
        }
        return result;
    }

    // ----- excel -----
    const sortAndFilterByHeader = <T>(data: T, keys: string[]): T => {
        const filteredData = _.pick(data, keys)
        const orderedData = _.fromPairs(_.toPairs(filteredData))
        // excel_search_filter
        return orderedData as T
    }

    const getSubHeaderCol = (title:string, _headers: any, _sub_headers: any) => {
        const keys = Object.keys(_headers)
        if(keys.length > 0)
            _sub_headers.push(getSubHeaderFormat(title, keys[0], keys[keys.length - 1], 'string', keys.length))
    }

    onDeactivated(() => {
        const tooltips = document.querySelectorAll('.v-tooltip.v-overlay--active')
        tooltips.forEach((tooltip) => {
            tooltip.classList.remove('v-overlay--active')
            const contents = tooltip.querySelectorAll('.v-overlay__content')
            contents.forEach((content) => {
                (content as HTMLElement).style.display = 'none'; // 툴팁 강제 숨김 처리
            })
        })
    })

    return {
        filter, headers, sub_headers, flat_headers, initHeader, sortAndFilterByHeader,
        flatten, getDepth, getSubHeaderComputed, getSubHeaderFormat, setHeaderStyle,
        exportToExcel, path, getSubHeaderCol,
    }
}

