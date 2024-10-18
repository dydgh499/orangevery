import { Filter } from '@/views/types';
import { Workbook } from 'exceljs';

export const ExcelExporter = (sub_headers: Ref<any>, flat_headers: Ref<Filter>, file_name: string) => {
    const setMergeCell = (worksheet:any) => {
        const row = worksheet.getRow(1)
        for (let i = 0; i < sub_headers.value.length; i++) {
            let s_col = null
            let e_col = null
            if(sub_headers.value[i].type === 'string') {
                s_col = worksheet.getColumn(sub_headers.value[i].s_col) 
                e_col = worksheet.getColumn(sub_headers.value[i].e_col)
            }
            else {
                let min = null;
                let max = null;
                let min_col = ''
                let max_col = ''
                const keys = Object.keys(flat_headers.value)
                const filters = keys.filter(key => key.includes(sub_headers.value[i].s_col+"."))
                for (let j = 0; j < filters.length; j++) {
                    if(min === null || flat_headers.value[filters[j]].idx < min) {
                        min_col = filters[j]
                        min = flat_headers.value[filters[j]].idx
                    }
                    if(max === null || flat_headers.value[filters[j]].idx > max) {
                        max_col = filters[j]
                        max = flat_headers.value[filters[j]].idx
                    }
                }
                s_col = worksheet.getColumn(min_col)
                e_col = worksheet.getColumn(max_col)        
            }
            worksheet.mergeCells(1, s_col.number, 1, e_col.number)
            worksheet.getCell(1, s_col.number).value = sub_headers.value[i].ko

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
        setHeaderStyle(row);
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

    const setHeaderRows = (worksheet:any) => {
        const columns = []
        const keys = Object.keys(flat_headers.value)
        for (let i = 0; i < keys.length; i++) {
            columns.push({
                header: flat_headers.value[keys[i]].ko,
                key: keys[i],
                width: 20,
            })            
        }
        worksheet.columns = columns
        return worksheet.getRow(1)
    }

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

    const checkVisiable = (worksheet: any) => {

    }

    const exportToExcel = async (datas: object[]) => {
        const date = new Date().toISOString().split('T')[0];

        const wb = new Workbook();
        const worksheet = wb.addWorksheet(file_name);

        setHeaderRows(worksheet)
        getDataRows(worksheet, datas)
        if(sub_headers.value.length) {
            // sub header setting
            worksheet.spliceRows(1, 0, {});
            setMergeCell(worksheet)
            setHeaderStyle(worksheet.getRow(2));
        }
        else
            setHeaderStyle(worksheet.getRow(1));

        worksheet.views = [{ state: 'frozen', ySplit: 1 }]
        checkVisiable(worksheet)
        try {
            const buffer = await wb.xlsx.writeBuffer(); // 버퍼로 엑셀 데이터 생성
            const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
            
            // 파일 다운로드 링크 생성
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `${file_name}_${date}.xlsx`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        } catch (err) {
            console.error('Error creating Excel file:', err);
        }
    }

    return {
        exportToExcel,
        setHeaderStyle,
    }
}
