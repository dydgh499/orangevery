import { Filter } from '@/views/types';
import pdfMake from 'pdfmake/build/pdfmake';
import pdfFonts from "pdfmake/build/vfs_fonts";
import * as XLSX from 'xlsx';

pdfMake.vfs = pdfFonts.pdfMake.vfs;

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
                result[key] = { ko: value, hidden: false, idx: header_count++ };
        }
        return result;
    }
    const getDepth = (item: object, _depth: number) => {
        if (typeof item === 'object') {
            let max_depth = 0;
            _depth++;
            for (const _key of Object.keys(item)) {
                const key = _key as keyof typeof item;
                if (item[key] != null) {
                    let dep = getDepth(item[key], _depth);
                    if (dep > max_depth)
                        max_depth = dep;
                }
            }
            return max_depth;
        }
        else
            return _depth;
    }
    const getColspans = () => {
        let colspans = []
        let colspan = 0
        for (const key of Object.keys(headers.value)) {
            const val = headers.value[key]
            if (getDepth(val, 0) !== 1) {
                if (colspan != 0)
                    colspans.push(colspan)
                colspan = 0;
                for (const sub_key of Object.keys(val)) {
                    colspan += val[sub_key].hidden ? 0 : 1
                }
                colspans.push(colspan)
                colspan = 0
            }
            else
                colspan += val.hidden ? 0 : 1
        }
        return colspans;
    }
    const getFullColspans = () => {
        let colspans = []
        let colspan = 0
        for (const key of Object.keys(headers.value)) {
            const val = headers.value[key]
            if (getDepth(val, 0) !== 1) {
                if (colspan != 0)
                    colspans.push(colspan)
                colspan = 0;
                for (const sub_key of Object.keys(val)) {
                    colspan += 1
                }
                colspans.push(colspan)
                colspan = 0
            }
            else
                colspan += 1
        }
        return colspans
    }
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
        for (const key of Object.keys(_headers)) {
            const val = headers.value[key]
            if (getDepth(_headers[key], 0) === 1) {
                if (key in headers.value)
                    _headers[key].hidden = val.hidden
            }
            else
                _headers[key] = setHeader(_headers[key] as Filter)
        }
        return _headers;
    };

    const exportWorkSheet = (datas: {[key: string]: any }[]) => {
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
        const getHeadersToArray = () => {
            const header_1 = []
            const header_2 = []
            for (const key of Object.keys(main_headers.value)) {
                header_1.push(main_headers.value[key])
            }
            for (const key of Object.keys(flat_headers.value)) {
                header_2.push(flat_headers.value[key].ko)
            }
            const total_header = [header_1, header_2]
            return total_header
        }
        const getExcelOptions = () => {
            const header_2 = []
            for (const key of Object.keys(flat_headers.value)) {
                header_2.push(flat_headers.value[key].ko);
            }
            const colspans = getColspans()
            const merge_cols: XLSX.Range[] = []
            const widths: XLSX.ColInfo[] = []
            // merge col
            let s_idx = 0;
            for (let i = 0; i < colspans.length; i++) {
                merge_cols.push({ s: { r: 0, c: s_idx }, e: { r: 0, c: s_idx + colspans[i] - 1 } })
                s_idx += colspans[i]
            }
            for (let i = 0; i < header_2.length; i++) {
                widths.push({ width: 20 })
            }
            return { merge_cols, widths }
        }
        
        const contents = getDatasToArray()
        const total_headers = getHeadersToArray()
        const { merge_cols, widths } = getExcelOptions()

        const all_data = total_headers.concat(contents)
        const ws:XLSX.WorkSheet = XLSX.utils.aoa_to_sheet(all_data)
        ws['!merges'] = merge_cols
        ws['!cols'] = widths
        
        if(total_headers.length > 0) {
            for (let i = 0; i < merge_cols.length; i++) {
                const cell = XLSX.utils.encode_cell(merge_cols[i].s);
                if(total_headers[0].length >= i && ws[cell] != undefined)
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
        const pdfDoc = pdfMake.createPdf(docDefinition);
        pdfDoc.download(file_name + "_" + date + ".pdf");
    }

    watchEffect(() => {
        localStorage.setItem(path, JSON.stringify(headers.value))
    })

    return {
        filter, headers, main_headers, flat_headers, initHeader,
        setFlattenHeaders, getColspans, getFullColspans, getColspansComputed, getDepth,
        exportToExcel, exportToPdf,
    }
}

