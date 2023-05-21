import { axios } from '@axios';
import { reactive } from 'vue';
import { VForm } from 'vuetify/components';

export function Updater<T extends object>(_type: T) {
    const app       = getCurrentInstance()
    // -----------------------------
    const item      = reactive<T>(_type)
    // -----------------------------
    const alert      = ref<any>(null)
    const snackbar   = ref<any>(null)

    async function get(uri: string) {
        axios.get(uri)
        .then(r => {
            Object.assign(item, r.data);
        })        
        .catch(e => {
            snackbar.value.show(e.response.data.message, 'primary')
            const res = app?.appContext.config.globalProperties.$errorHandler(e);
        })
    }

    async function update(url:string, form: VForm, is_update:boolean) {
        let is_valid = await form?.validate();
        let up_type  = is_update ? '수정' : '생성';
        
        if(is_valid.valid && await alert.value.show('정말 '+up_type+'하시겠습니까?')) {
            axios.post(url, {params: item})
            .then(r => {
                snackbar.value.show('성공하였습니다.', 'success')
            })
            .catch(e => {
                snackbar.value.show(e.response.data.message, 'primary')
                const res = app?.appContext.config.globalProperties.$errorHandler(e);                
            })
        }
        else
            snackbar.value.show(up_type+'조건에 맞지않는 필드가 존재합니다.', 'primary')
    }
    return {
        item,
        get, update
    }
}
