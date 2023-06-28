<script setup lang="ts">
import { requiredValidator } from '@validators'
import { axios } from '@axios'

interface Props {
    id: number,
    name: string,
    is_mcht: boolean
}
const props = defineProps<Props>()

const store = <any>(inject('store'))
const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))
const errorHandler = <any>(inject('$errorHandler'))

const deduction = ref<number>(0)
const addDeduction = async () => {
    const params = {
        'amount': deduction.value,
        'id': props.id,
        'dt': store.params.dt
    };
    const page = props.is_mcht ? 'merchandises' : 'salesforces'
    if (await alert.value.show('정말 ' + props.name + '님을(를) 추가차감하시겠습니까?')) {
        try {
            const r = await axios.post('/api/v1/manager/transactions/settle/' + page + '/deduct', params)
            snackbar.value.show('성공하였습니다.', 'success')
            store.setTable()
        }
        catch (e: any) {
            snackbar.value.show(e.response.data.message, 'error')
            const r = errorHandler(e)
        }
    }
}

</script>
<template>
    <VRow no-gutters>
        <VCol>
            <div class="d-inline-flex align-center gap-2 justify-content-evenly">
                <VTextField v-model="deduction" type="text" suffix="₩" :rules="[requiredValidator]" dense />
                <VBtn size="small" variant="tonal" @click="addDeduction()">
                    차감
                </VBtn>
            </div>
        </VCol>
    </VRow>
</template>
<style scoped>
/deep/ .v-text-field {
  inline-size: 7em;
}

/deep/ .v-field__field {
  block-size: 2.2em;
}
</style>
