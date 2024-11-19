import { bulkAutoInsertPaymentModuleFormat, isFixplus } from '@/plugins/fixplus';
import { axios } from '@axios';
import * as XLSX from 'xlsx';

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
    const bulkRegister = async (name: string, path: string, items:any[]) => {
        let result = false
        if (await alert.value.show('정말 '+name + ' ' + items.length + '개를 대량 등록하시겠습니까?')) {
            try {
                const r = await axios.post('/api/v1/manager/'+path+'/bulk-register', items)
                snackbar.value.show('성공하였습니다.', 'success')
                result = true

                if(name === '가맹점' && isFixplus()) {
                    const pay_modules = bulkAutoInsertPaymentModuleFormat(r.data)
                    await axios.post('/api/v1/manager/merchandises/pay-modules/bulk-register', pay_modules)
                }
            }
            catch (e: any) {
                snackbar.value.show(e.response.data.message, 'error')
                const r = errorHandler(e)
            }
        }
        return result
    }
    return {
        ExcelReaderV2,
        isEmpty,
        openFilePicker,
        bulkRegister,
    }
}
