import { Filter } from '@/views/types';
import _ from 'lodash';
import * as XLSX from 'xlsx';

export const Header = (_path: string, file_name: string) => {
    const filter = ref<any>(null)
    const headers = ref<Filter>(JSON.parse(localStorage.getItem(_path) || "{}"))
    const flat_headers = ref<Filter>({})
    const main_headers = ref<string[]>([])
    const path = _path
    let header_count = 0;

    const initHeader = (_headers: object, result: Filter): Filter => {
        for (const [key, value] of Object.entries(_headers)) {
            if (typeof value === 'object')
                result[key] = initHeader(value, {})
            else
                result[key] = { ko: value, visible: true, idx: header_count++ };
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
    const getColspans = (): number[] => {
        let colspans: number[] = [];
        let colspan: number = 0;
        _.forEach(headers.value, (val: any) => {
            if (getDepth(val, 0) !== 1) {
                if (colspan !== 0)
                    colspans.push(colspan)
                colspan = 0
                const subColspan: number = _.reduce(val, (sum: number, subVal: any) => {
                    return sum + (subVal.visible ? 1 : 0)
                }, 0)
                colspans.push(subColspan)
                colspan = 0
            } 
            else
                colspan += val.visible ? 1 : 0
        })
        return colspans
    }
    const getFullColspans = () => {
        let colspans: number[] = []
        let colspan = 0
        _.forEach(headers.value, (val:Filter) => {
            if (getDepth(val, 0) !== 1) {
                if (colspan !== 0) colspans.push(colspan)
                colspans.push(_.keys(val).length)
                colspan = 0
            } 
            else
                colspan += 1
        });
        return colspans
    };
    const getColspansComputed = computed(() => { return getColspans() })

    const setFlattenHeaders = (): Filter => {
        const result: { [key: string]: any } = {};
        for (const key of Object.keys(headers.value)) {
            const val = headers.value[key]
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
    const setHeader = (_headers: Filter): Filter => {
        return _.transform(_headers, (result: Filter, val:any, key:string) => {
            if (getDepth(val, 0) === 1 && key in headers.value) {
                val.visible = headers.value[key].visible;
            } else {
                result[key] = setHeader(val as Filter);
            }
        }, {});
    };
    // ----- excel -----
    const sortAndFilterByHeader = <T>(data: T, keys: string[]): T => {
        const filteredData = _.pick(data, keys);
        const orderedData = _.fromPairs(_.sortBy(_.toPairs(filteredData), ([key]:string) => keys.indexOf(key)));
        return orderedData as T;
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
            return [_.map(main_headers.value, (value: string| number) => value), _.map(flat_headers.value, (value:Filter) => value.ko)];
        };
        const getExcelOptions = (): { merge_cols: any[], widths: any[] } => {
            const header_2 = _.map(flat_headers.value, (value: Filter) => value.ko);
            const colspans = getColspans()

            let index = 0
            const merge_cols = []
            for (let i = 0; i < colspans.length; i++) {
                merge_cols.push({ s: { r: 0, c: index }, e: { r: 0, c: index + colspans[i] - 1 } })                
                index += colspans[i]
            }

            const widths = _.map(header_2, () => ({ width: 20 }));
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
            for (let i = 0; i < merge_cols.length; i++) {
                const cell = XLSX.utils.encode_cell(merge_cols[i].s);
                if (total_headers[0].length >= i && ws[cell] != undefined)
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
        const rows = main_headers.value.length > 0 ? 2 : 1
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

    watchEffect(() => {
        localStorage.setItem(path, JSON.stringify(headers.value))
    })

    return {
        filter, headers, main_headers, flat_headers, initHeader, sortAndFilterByHeader,
        setFlattenHeaders, getColspans, getFullColspans, getColspansComputed, getDepth,
        exportToExcel, exportToPdf,
    }
}

