import { axios } from '@axios';
import { Workbook } from 'exceljs';
import * as XLSX from 'xlsx';
import { ExcelStyle } from './excel';

export const Registration = () => {
    const alert = <any>(inject('alert'))
    const snackbar = <any>(inject('snackbar'))
    const errorHandler = <any>(inject('$errorHandler'))
    
    const setKeyNameByHeadersV2 = (headers: any[], sheetData: Array<Record<string, any>>): Array<Record<string, any>> => {
        return sheetData.map(row => {
            const newRow: Record<string, any> = {};
            for (const idx in headers) {
                newRow[headers[idx].key] = row[headers[idx].title]
            }
            return newRow;
        });
    }

    const ExcelFormatV2 = async (file_name:string ,headers: any[]) => {
        const date = new Date().toISOString().split('T')[0];
        const wb = new Workbook();
        const worksheet = wb.addWorksheet(file_name);

        const key_names = headers.map(header => header.title) as string[]
        const keys = headers.map(header => header.key) as string[]
        ExcelStyle().setHeaderRows(worksheet, key_names, keys)
        ExcelStyle().setHeaderStyle(worksheet.getRow(1));

        //checkVisiable(worksheet)
        worksheet.views = [{ state: 'frozen', ySplit: 1 }];
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

    const ExcelReaderV2 = (headers: any[], file: File) => {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = (e) => {
                try {
                    const data = e.target?.result;
                    const workbook = XLSX.read(data, { type: 'binary' });
                    const sheetName = workbook.SheetNames[0];
                    const sheetData: Array<Record<string, any>> = XLSX.utils.sheet_to_json(workbook.Sheets[sheetName]);

                    if(sheetData.length > 1000) {
                        snackbar.value.show('1000개 이하씩 등록 가능합니다.(현재:'+sheetData.length+'개)<br>데이터를 나누어 등록해주세요.', 'error')
                        reject([]);
                    }
                    else 
                        resolve(setKeyNameByHeadersV2(headers, sheetData));    
                }
                catch (error) {
                    reject(error);
                }
            }
            reader.readAsBinaryString(file);
        })
    }
    
    const isEmpty = (value: string) => {
        return value == null || value == '' ? true : false;
    }
    
    const openFilePicker = (id: string) => {
        const fileInput = document.getElementById(id) as HTMLInputElement;
        fileInput.value = "";
        fileInput.click();
    }

    const bulkRegister = async (name: string, path: string, items:any[], is_payment=false) => {
        let result = false
        if (await alert.value.show('정말 '+name + ' ' + items.length + `개를 대량 ${is_payment ? '정산' : '등록'}하시겠습니까?`)) {
            try {
                const r = await axios.post('/api/v1/manager/' + path + '/batch-updaters/register', items)
                snackbar.value.show(r.data.message, 'success')
                result = true
            }
            catch (e: any) {
                snackbar.value.show(e.response.data.message, 'error')
                const r = errorHandler(e)
            }
        }
        return result
    }

    return {
        ExcelFormatV2,
        ExcelReaderV2,
        isEmpty,
        openFilePicker,
        bulkRegister,
    }
}
