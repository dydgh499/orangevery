import axios from '@axios';
import { reactive } from 'vue';
import { VForm } from 'vuetify/components';

export function Updater<T extends object>(_path: string, _type: T) {
    const app       = getCurrentInstance()
    // -----------------------------
    const router    = useRouter()
    const id        = Number(router.currentRoute.value.params.id || '0');
    const path      = _path
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
            const res = app?.appContext.config.globalProperties.$errorHandler(e);
            snackbar.value.show(res.data.message, 'primary')
        })
    }

    async function update(form: VForm) {        
        let is_valid = await form?.validate();
        let up_type  = id ? '수정' : '생성';
        
        if(is_valid.valid && await alert.value.show('정말 '+up_type+'하시겠습니까?'))
        {
            const uri = id ? '/api/v1/manager/'+path+'/'+id : '/api/v1/manager/'+path
            axios.post(uri, {params: item})
            .then(r => {
                snackbar.value.show('성공하였습니다.', 'success')
            })
            .catch(e => {
                const res = app?.appContext.config.globalProperties.$errorHandler(e);
                snackbar.value.show(res.data.message, 'primary')
            })
        }
        else
            snackbar.value.show(up_type+'조건에 맞지않는 필드가 존재합니다.', 'primary')
    }
    return {
        item, id,
        get, update
    }
}
