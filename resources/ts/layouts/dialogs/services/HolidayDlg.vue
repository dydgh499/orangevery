<script lang="ts" setup>
import { useRequestStore } from '@/views/request';
import { rest_types } from '@/views/services/holidays/useStore';
import type { Holiday } from '@/views/types';
import { VForm } from 'vuetify/components';

const { update , remove } = useRequestStore()

const visible = ref(false)
const vForm = ref<VForm>()

let resolveCallback: (isAgreed: boolean) => void;

const holiday = ref<Holiday>({
    id: 0,
    rest_dt: '',
    rest_name: '',
    rest_type: 0,
})

const show = (_holiday: Holiday): Promise<boolean> => {
    holiday.value = _holiday
    visible.value = true

    return new Promise<boolean>((resolve) => {
        resolveCallback = resolve;
    });
}

const onAgree = async () => {
    const res = await update(`/services/holidays`, holiday.value, vForm.value, false)
    visible.value = false
    resolveCallback(true); // 동의 버튼 누름
};

const onCancel = async () => {
    const res = await remove(`/services/holidays`, holiday.value, false)
    visible.value = false
    resolveCallback(false); // 취소 버튼 누름
};

defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="visible" persistent max-width="700">
        <!-- Dialog close btn -->
        <DialogCloseBtn @click="visible = !visible" />
        <!-- Dialog Content -->
        <VCard :title="'공휴일 ' + (holiday.id ? '수정' : '추가')">
            <VCardText>
                <VForm ref="vForm">
                    <VRow class="pt-3">
                        <VCol :md="4" :cols="12">
                            <VRow no-gutters>
                                <VCol>
                                    <VTextField type="date" v-model="holiday.rest_dt"
                                        prepend-inner-icon="material-symbols:calendar-add-on" label="공휴일 날짜" persistent-placeholder />
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol :md="4">
                            <VRow no-gutters>
                                <VCol>
                                    <VTextField v-model="holiday.rest_name" prepend-inner-icon="material-symbols:drive-file-rename-outline"
                                        persistent-placeholder label="공휴일명" />
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol :md="4" :cols="12">
                            <VRow no-gutters>
                                <VCol>
                                    <VSelect v-model="holiday.rest_type" :items="rest_types" label="공휴일 타입"
                                        item-title="title" item-value="id" prepend-inner-icon="material-symbols:holiday-village"/>
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                </VForm>
            </VCardText>
            <VCardText class="d-flex justify-end gap-3 flex-wrap">
                <VBtn color="error" @click="onCancel" v-if="holiday.id !== 0">
                    삭제
                </VBtn>
                <VBtn @click="onAgree">
                    확인
                </VBtn>
            </VCardText>
        </VCard>
    </VDialog>
</template>
