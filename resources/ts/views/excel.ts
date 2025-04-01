import { Filter } from '@/views/types';
import { Workbook } from 'exceljs';

export const ExcelStyle = () => {
    const setHeaderRows = (worksheet:any, key_names: string[], keys: string[]) => {
        const columns = []
        for (let i = 0; i < key_names.length; i++) {
            columns.push({
                header: key_names[i],
                key: keys[i],
                width: 20,
            })            
        }
        worksheet.columns = columns
        return worksheet.getRow(1)
    }
    const setHeaderStyle = (row: any) => {
        row.font = { bold: true, size: 12, color: {argb: 'FFFFFF'} };
        row.alignment = { horizontal: 'center', vertical: 'middle' };
        row.fill = {
            type: 'pattern',
            pattern: 'solid',
            fgColor: { argb: '0D47A1' }
        };
        row.border = {
            top: { style: 'thin', color: { argb: '000000' } },
            left: { style: 'thin', color: { argb: '000000' } },
            bottom: { style: 'medium', color: { argb: '000000' } },
            right: { style: 'thin', color: { argb: '000000' } }
        };
    }
    return {
        setHeaderRows,
        setHeaderStyle
    }
}

const excelSubHeader = (_sub_headers: Ref<any>, _flat_headers: Ref<Filter>) => {
    // string type 컬럼 검색
    const getStringCellName = (idx: number) => {
        return [_sub_headers.value[idx].s_col, _sub_headers.value[idx].e_col]
    }
    // object type 컬럼 검색
    const getOjectCellColName = (idx: number) => {
        let min = null;
        let max = null;
        let min_col = ''
        let max_col = ''
        const filters = Object.keys(_flat_headers.value).filter(key => key.includes(_sub_headers.value[idx].s_col+"."))
        for (let j = 0; j < filters.length; j++) {
            if(min === null || _flat_headers.value[filters[j]].idx < min) {
                min_col = filters[j]
                min = _flat_headers.value[filters[j]].idx
            }
            if(max === null || _flat_headers.value[filters[j]].idx > max) {
                max_col = filters[j]
                max = _flat_headers.value[filters[j]].idx
            }
        }
        return [min_col, max_col]
    }

    // 숨김 컬럼 적용
    const setVisiable = (headers:any, min_col: string, max_col: string) => {
        const keys = Object.keys(_flat_headers.value)
        const min_idx = keys.findIndex(obj => obj === min_col)
        const max_idx = keys.findIndex(obj => obj === max_col)
        // min_col이 없을 경우 = _flat_headers에서 앞으로에서부터 조회하여 visiable인 column을 min_col 대체
        if(headers[min_col] === undefined) {
            min_col = ''
            for (let i = min_idx; i <= max_idx; i++) {
                if(_flat_headers.value[keys[i]].visible) {
                    min_col = keys[i]
                    break
                }
            }
        }
        // max_col이 없을 경우 = _flat_headers에서 뒤에서부터 조회하여 visiable인 column을 min_col 대체
        if(headers[max_col] === undefined) {
            max_col = ''
            for (let i = max_idx; i >= min_idx; i--) {
                if(_flat_headers.value[keys[i]].visible) {
                    max_col = keys[i]
                    break
                }
            }
        }
        return [min_col, max_col]
    }

    // 보조 헤더 제작
    const setMergeCell = (worksheet:any, headers:any, sub_headers:any) => {
        for (let i = 0; i < sub_headers.length; i++) {
            let [min_col, max_col] = sub_headers[i].type === 'string' ? getStringCellName(i) : getOjectCellColName(i, headers)
            let [visible_min_col, visible_max_col] = setVisiable(headers, min_col, max_col)
            // 둘다 없을 경우 continue
            if(visible_min_col !== '' && visible_max_col !== '') {
                const s_col = worksheet.getColumn(visible_min_col)
                const e_col = worksheet.getColumn(visible_max_col)
    
                worksheet.mergeCells(1, s_col.number, 1, e_col.number)
                worksheet.getCell(1, s_col.number).value = sub_headers[i].ko
    
                s_col.eachCell((cell: any) => {
                    cell.border = {
                        left: { style: 'medium', color: { argb: '000000' } },
                    }  
                })
                e_col.eachCell((cell: any) => {
                    cell.border = {
                        right: { style: 'medium', color: { argb: '000000' } },
                    }  
                })
            }
        }
        ExcelStyle().setHeaderStyle(worksheet.getRow(1));
    }
    return { setMergeCell }
}

export const ExcelExporter = (_sub_headers: Ref<any>, _flat_headers: Ref<Filter>, _file_name: string) => {
    const { setMergeCell } = excelSubHeader(_sub_headers, _flat_headers)
    const getDataRows = (worksheet:any, datas: { [key: string]: any}[]) => {
        for (let i = 0; i < datas.length; i++) {
            const row: { [key: string]: string } = {};
            for (const key of Object.keys(datas[i])) {
                if (typeof datas[i][key] === 'object' && datas[i][key] !== null) {
                    for (const sub_key of Object.keys(datas[i][key])) {
                        row[key + "." + sub_key] = datas[i][key][sub_key]
                    }
                }
                else
                    row[key] = datas[i][key]
            }
            worksheet.addRow(row)
        }
    }

    const exportToExcel = async (_datas: object[]) => {
        const date = new Date().toISOString().split('T')[0];
        const wb = new Workbook();
        const worksheet = wb.addWorksheet(_file_name);
        const headers = Object.fromEntries(
            Object.entries(_flat_headers.value)
              .filter(([key, value]) => value?.visible !== false)
              .map(([key, value]) => [key, value])
        )
        const datas = _datas.map(data =>
            Object.fromEntries(
              Object.entries(data)
                .filter(([key]) => key in headers)
                .map(([key, value]) => [key, value])
            )
        )
        const sub_headers = _sub_headers.value

        const key_names = Object.keys(headers).map(key => headers[key]?.ko) as string[]
        ExcelStyle().setHeaderRows(worksheet, key_names, Object.keys(headers))
        getDataRows(worksheet, datas)
        
        if(sub_headers.length) {
            // sub header setting
            worksheet.spliceRows(1, 0, {});
            setMergeCell(worksheet, headers, sub_headers)
            ExcelStyle().setHeaderStyle(worksheet.getRow(2));
        }
        else
            ExcelStyle().setHeaderStyle(worksheet.getRow(1));

        worksheet.views = [{ state: 'frozen', ySplit: 1 }];
        try {
            const buffer = await wb.xlsx.writeBuffer(); // 버퍼로 엑셀 데이터 생성
            const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
            
            // 파일 다운로드 링크 생성
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `${_file_name}_${date}.xlsx`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        } catch (err) {
            console.error('Error creating Excel file:', err);
        }
    }

    return {
        exportToExcel,
    }
}
