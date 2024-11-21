

<script lang="ts" setup>

import { batch } from '@/layouts/components/batch-updaters/batch'
import FeeBookDialog from '@/layouts/dialogs/users/FeeBookDialog.vue'
import PasswordAuthDialog from '@/layouts/dialogs/users/PasswordAuthDialog.vue'
import CheckAgreeDialog from '@/layouts/dialogs/utils/CheckAgreeDialog.vue'
import corp from '@corp'

interface Props {
    selected_idxs: number[],
}

const props = defineProps<Props>()
const emits = defineEmits(['update:select_idxs'])
const {
        selected_idxs,
        selected_sales_id,
        selected_level,
        selected_all,
        feeBookDialog,
        checkAgreeDialog,
        passwordAuthDialog,
        post,
        batchRemove
    } = batch(emits, '노티', 'merchandises/noti-urls')
const noti = reactive<any>({
    note: '',
    send_url: '',
    noti_status: 0,
})
const noti_statuses = [{id:0, title:'미사용'}, {id:1, title:'사용'}]

const setSendUrl = (apply_type: number) => {
    post('set-send-url', {
        'send_url': noti.send_url,
    }, apply_type)
}

const setNotiStatus = (apply_type: number) => {
    post('set-noti-status', {
        'noti_status': noti.noti_status,
    }, apply_type)
}

const setNote = (apply_type: number) => {
    post('set-note', {
        'note': noti.note,
    }, apply_type)
}

watchEffect(() => {
    selected_idxs.value = props.selected_idxs
    selected_sales_id.value = null
    selected_level.value = null
})

</script>
<template>
    <section>
        <VCard title="노티주소 일괄작업" style="max-height: 55em !important;overflow-y: auto !important;">
            <VCardText>
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <VRadioGroup v-model="selected_all">
                        <VRadio :value="0" @click="">
                            <template #label>
                                <b>선택된 노티주소 : {{ selected_idxs.length.toLocaleString() }}개</b>
                            </template>
                        </VRadio>
                    </VRadioGroup>
                    <VBtn type="button" color="error" @click="batchRemove()" style="float: inline-end;" size="small">
                        일괄삭제
                        <VIcon size="18" icon="tabler-trash" />
                    </VBtn>
                </div>
                <VDivider style="margin: 1em 0;" />
                <div style="width: 100%;">
                    <h4 class="pt-3">노티정보 일괄변경</h4>
                    <br>
                    <VRow>
                        <VCol :md="12" :cols="12">
                            <VRow no-gutters style="align-items: center;">
                                <VCol md="8" cols="12">
                                    <VTextField v-model="noti.send_url" label="발송 URL" />
                                </VCol>
                                <VCol md="4" cols="12">
                                    <div class="button-cantainer">
                                        <VBtn variant="tonal" size="small" @click="setSendUrl(0)">
                                            즉시적용
                                            <VIcon end size="18" icon="tabler-direction-sign" />
                                        </VBtn>
                                        <VBtn variant="tonal" size="small" color="secondary" @click="setSendUrl(1)"
                                            style='margin-left: 0.5em;'>
                                            예약적용
                                            <VIcon end size="18" icon="tabler-clock-up" />
                                        </VBtn>
                                    </div>
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                    <VRow>
                        <VCol :md="6" :cols="12">
                            <VRow no-gutters style="align-items: center;">
                                <VCol md="6" cols="12">
                                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="noti.noti_status"
                                            :items="noti_statuses" 
                                            item-title="title" item-value="id" label="노티 사용여부" />
                                </VCol>
                                <VCol md="6" cols="12">
                                    <div class="button-cantainer">
                                        <VBtn variant="tonal" size="small" @click="setNotiStatus(0)">
                                            즉시적용
                                            <VIcon end size="18" icon="tabler-direction-sign" />
                                        </VBtn>
                                        <VBtn variant="tonal" size="small" color="secondary" @click="setNotiStatus(1)"
                                            style='margin-left: 0.5em;'>
                                            예약적용
                                            <VIcon end size="18" icon="tabler-clock-up" />
                                        </VBtn>
                                    </div>
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol :md="6" :cols="12">
                            <VRow no-gutters style="align-items: center;">
                                <VCol md="6" cols="12">
                                    <VTextField v-model="noti.note" label="별칭" />
                                </VCol>
                                <VCol md="6" cols="12">
                                    <div class="button-cantainer">
                                        <VBtn variant="tonal" size="small" @click="setNote(0)">
                                            즉시적용
                                            <VIcon end size="18" icon="tabler-direction-sign" />
                                        </VBtn>
                                        <VBtn variant="tonal" size="small" color="secondary" @click="setNote(1)"
                                            style='margin-left: 0.5em;'>
                                            예약적용
                                            <VIcon end size="18" icon="tabler-clock-up" />
                                        </VBtn>
                                    </div>
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                </div>
            </VCardText>
        </VCard>
        <FeeBookDialog ref="feeBookDialog"/>
        <CheckAgreeDialog ref="checkAgreeDialog"/>
        <PasswordAuthDialog ref="passwordAuthDialog"/>
    </section>
</template>
<style scoped>
.button-cantainer {
  display: flex;
  padding: 0.25em;
  float: inline-end;
}

:deep(.v-input) {
  padding: 0.25em !important;
}
</style>
