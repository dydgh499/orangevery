<script lang="ts" setup>
import { useRequestStore } from '@/views/request'
import type { ExceptionWorkTime, Operator } from '@/views/types'
import { VForm } from 'vuetify/components'

const { update, get } = useRequestStore()
const store = <any>(inject('store'))

const formatDate = <any>(inject('$formatDate'))

const visible = ref(false)
const vForm = ref<VForm>()
const operators = ref<Operator[]>([])

const today = new Date()
const work_s_dt = ref(formatDate(today))
const work_s_tm = ref("21:00")
const work_e_dt = ref(formatDate(new Date(today.getFullYear(), today.getMonth(), today.getDate()+1)))
const work_e_tm = ref("06:00")

let resolveCallback: (isAgreed: boolean) => void;

const work_time = ref<ExceptionWorkTime>({
    id: 0,
    oper_id: null,
    work_s_at: '',
    work_e_at: '',
})

const show = async (_work_time: ExceptionWorkTime): Promise<boolean> => {
    work_time.value = _work_time
    visible.value = true

    return new Promise<boolean>((resolve) => {
        resolveCallback = resolve;
    });
}

const onAgree = async () => {
    work_time.value.work_s_at = work_s_dt.value + " " + work_s_tm.value
    work_time.value.work_e_at = work_e_dt.value + " " + work_e_tm.value
    const res = await update(`/services/exception-work-times`, work_time.value, vForm.value, false)
    if(res.status === 201) {
        store.setTable()
        visible.value = false
        resolveCallback(true); // 동의 버튼 누름        
    }
};

const onCancel = () => {
    visible.value = false
    resolveCallback(false); // 취소 버튼 누름
};

onMounted(async() => {
    const res = await get(`/api/v1/manager/services/operators`, {
        params: {
            page: 1,
            page_size: 999,
        }
    })
    operators.value = res.data.content
})

defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="visible" persistent max-width="700">
        <!-- Dialog close btn -->
        <DialogCloseBtn @click="visible = !visible" />
        <!-- Dialog Content -->
        <VCard :title="'예외 작업시간 ' + (work_time.id ? '수정' : '추가')">
            <VCardText>
                <VForm ref="vForm">
                    <VRow class="pt-3">
                        <VCol :md="4" :cols="12">
                            <VRow no-gutters>
                                <VCol>작업시작 시간</VCol>
                            </VRow>
                        </VCol>
                        <VCol :md="4" :cols="12">
                            <VRow no-gutters>
                                <VCol>
                                    <VTextField type="date" v-model="work_s_dt"
                                        prepend-inner-icon="material-symbols:calendar-add-on" />
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol :md="4" :cols="12">
                            <VRow no-gutters>
                                <VCol>
                                    <VTextField type="time" v-model="work_s_tm"
                                        prepend-inner-icon="material-symbols:calendar-add-on" />
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                    <VRow>
                        <VCol :md="4" :cols="12">
                            <VRow no-gutters>
                                <VCol>작업종료 시간</VCol>
                            </VRow>
                        </VCol>
                        <VCol :md="4" :cols="12">
                            <VRow no-gutters>
                                <VCol>
                                    <VTextField type="date" v-model="work_e_dt"
                                        prepend-inner-icon="material-symbols:calendar-add-on" />
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol :md="4" :cols="12">
                            <VRow no-gutters>
                                <VCol>
                                    <VTextField type="time" v-model="work_e_tm"
                                        prepend-inner-icon="material-symbols:calendar-add-on" />
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                    <VRow>
                        <VCol :md="4" :cols="12">
                            <VRow no-gutters>
                                <VCol>작업자 선택</VCol>
                            </VRow>
                        </VCol>
                        <VCol :md="4" :cols="12">
                            <VRow no-gutters>
                                <VCol>
                                    <VSelect v-model="work_time.oper_id" :items="[{id:null, user_name:'작업자 선택'}].concat(operators)"
                                        item-title="user_name" item-value="id" prepend-inner-icon="streamline:office-worker"/>
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                </VForm>
            </VCardText>
            <VCardText class="d-flex justify-end gap-3 flex-wrap">
                <VBtn color="secondary" variant="tonal" @click="onCancel">
                    취소
                </VBtn>
                <VBtn @click="onAgree">
                    확인
                </VBtn>
            </VCardText>
        </VCard>
    </VDialog>
</template>
