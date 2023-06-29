import { axios } from '@axios';
import * as XLSX from 'xlsx';

export const Registration = () => {
    const alert = <any>(inject('alert'))
    const snackbar = <any>(inject('snackbar'))
    const errorHandler = <any>(inject('$errorHandler'))
    
    const setKeyNameByHeaders = (headers: Record<string, string>, sheetData: Array<Record<string, any>>): Array<Record<string, any>> => {
        const headerMapping: Record<string, string> = {};
        // headers 객체를 통해 원래 키와 새로운 키 사이의 매핑을 생성합니다.
        for (const newKey in headers) {
            const originalKey = headers[newKey];
            headerMapping[originalKey] = newKey;
        }
        // sheetData 배열의 각 객체를 순회하며 키를 새로운 키로 바꿉니다.
        return sheetData.map(row => {
            const newRow: Record<string, any> = {};
            for (const key in row) {
                const newKey = headerMapping[key];
                if (newKey) {
                    newRow[newKey] = row[key];
                }
            }
            return newRow;
        });
    }
    
    const ExcelReader = (headers: Record<string, string>, file: File):Promise<Record<string, any>[]> => {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = (e) => {
                try {
                    const data = e.target?.result;
                    const workbook = XLSX.read(data, { type: 'binary' });
                    // workbook을 사용하여 엑셀 데이터를 처리합니다.
                    // 예를 들어, 첫 번째 시트의 이름을 가져옵니다.
                    const sheetName = workbook.SheetNames[0];
                    // 첫 번째 시트의 데이터를 JSON 형식으로 변환합니다.
                    const sheetData: Array<Record<string, any>> = XLSX.utils.sheet_to_json(workbook.Sheets[sheetName]);
                    const content = setKeyNameByHeaders(headers, sheetData);
                    resolve(content);
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
        fileInput.click();
    }
    const bulkRegister = async (name: string, path: string, items:any[]) => {
        if (await alert.value.show('정말 '+name + ' ' + items.length + '개를 대량 등록하시겠습니까?')) {
            try {
                const r = await axios.post('/api/v1/manager/'+path+'/bulk-register', items)
                snackbar.value.show('성공하였습니다.', 'success')
            }
            catch (e: any) {
                snackbar.value.show(e.response.data.message, 'error')
                const r = errorHandler(e)
            }
    
        }
    }
    return {
        ExcelReader,
        isEmpty,
        openFilePicker,
        bulkRegister,
    }
}
