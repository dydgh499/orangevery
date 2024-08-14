import { Filter, SubFilter } from '@/views/types';
import _ from 'lodash';
import * as XLSX from 'xlsx';

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
        getSubHeaderFormat, getSubHeaders,
        getSubHeaderComputed
    }
}

export const Header = (path: string, file_name: string) => {
    let header_count = 0
    const filter = ref<any>(null)
    const flat_headers = ref<Filter>({})
    const {
        headers, sub_headers,
        getSubHeaderFormat, getSubHeaders,
        getSubHeaderComputed
    } = SubHeader(path)

    const _init = (_headers: object) => {
        const kos = Object.keys(headers.value).map(key => headers.value[key].ko)
        if (!_.isEqual(kos, Object.values(_headers)))
            headers.value = {}
    }

    const initHeader = (_headers: object, result: Filter): Filter => {
        _init(_headers)
        for (const [key, value] of Object.entries(_headers)) {
            if (typeof value === 'object')
                result[key] = initHeader(value, {})
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
        return orderedData as T
    }

    const exportWorkSheet = (datas: { [key: string]: any }[]) => {
        const getDatasToArray = () => {
            const results = [];
            for (let i = 0; i < datas.length; i++) {
                const result: { [key: string]: string } = {};
                for (const key of Object.keys(datas[i])) {
                    if (typeof datas[i][key] === 'object' && datas[i][key] !== null) {
                        for (const sub_key of Object.keys(datas[i][key])) {
                            result[key + "." + sub_key] = datas[i][key][sub_key]
                        }
                    }
                    else
                        result[key] = datas[i][key];
                }
                results.push(result)
            }
            return results.map(obj => Object.values(obj));
        }
        const getHeadersToArray = (): any[][] => {
            if(sub_headers.value.length) {
                const _sub_headers =  _.map(sub_headers.value, (init_sub_header: SubFilter) => init_sub_header.ko)
                const keys = Object.keys(flat_headers.value)
                const least_length = keys.length - _sub_headers.length
                for (let i = 0; i < least_length; i++) {
                    _sub_headers.push('')                    
                }
                return [_sub_headers, _.map(flat_headers.value, (value:Filter) => value.ko)]
            }
            else
               return [_.map(flat_headers.value, (value:Filter) => value.ko)]
        }

        const getExcelOptions = (): { merge_cols: any[], widths: any[] } => {
            const header_2 = _.map(flat_headers.value, (value: Filter) => value.ko);
            const _sub_headers = getSubHeaders()
            let index = 0
            let merge_cols = []

            for (let i = 0; i < _sub_headers.length; i++) {
                merge_cols.push({ s: { r: 0, c: index }, e: { r: 0, c: index + _sub_headers[i].width - 1 } })
                index += _sub_headers[i].width
            }
            const widths = _.map(header_2, () => ({ width: 20 }))
            return { merge_cols, widths };
        };

        const contents = getDatasToArray()
        const total_headers = getHeadersToArray()
        const { merge_cols, widths } = getExcelOptions()
        const all_data = total_headers.concat(contents)
        const ws: XLSX.WorkSheet = XLSX.utils.aoa_to_sheet(all_data)
        ws['!merges'] = merge_cols
        ws['!cols'] = widths

        if (total_headers.length > 0) {
            console.log(ws)
            for (let i = 0; i < merge_cols.length; i++) {
                const cell = XLSX.utils.encode_cell(merge_cols[i].s);
                console.log(cell)
                ws[cell].v = total_headers[0][i]
            }
        }
        return ws
    }

    const exportToExcel = (datas: object[]) => {
        const date = new Date().toISOString().split('T')[0];
        const ws = exportWorkSheet(datas)
        const wb = XLSX.utils.book_new()
        XLSX.utils.book_append_sheet(wb, ws, file_name)
        XLSX.writeFile(wb, file_name + "_" + date + ".xlsx")
    }

    const exportToPdf = (datas: object[]) => {
        const date = new Date().toISOString().split('T')[0];
        const rows = sub_headers.value.length > 0 ? 2 : 1
        const ws = exportWorkSheet(datas)
        const json_data = XLSX.utils.sheet_to_json(ws)

        const docDefinition = {
            content: [
                {
                    table: {
                        headerRows: rows,
                        body: json_data,
                        // 가로로 돌리기 위해 rotate 속성 사용
                        style: {
                            tableBody: {
                                rotate: 90,
                            },
                        },
                    },
                },
            ],
        };
        //pdfDoc.download(file_name + "_" + date + ".pdf");
    }

    return {
        filter, headers, sub_headers, flat_headers, initHeader, sortAndFilterByHeader,
        flatten, getDepth, getSubHeaderComputed, getSubHeaderFormat,
        exportToExcel, exportToPdf, path,
    }
}

