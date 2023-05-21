
<script setup lang="ts">

import { axios } from '@axios';
import { VForm } from 'vuetify/components';
import AlertDialog from '@/views/utils/AlertDialog.vue';
import LoadingDialog from '@/views/utils/LoadingDialog.vue';
import Snackbar from '@/views/utils/Snackbar.vue';
import { getCurrentInstance } from 'vue'

interface Props {
    id: integer | string,
    path: string,
    tabs: object[],
    item: object,
}
const props = defineProps<Props>()

let app = null
const url = ref('/api/v1/manager/' + props.path + '/' + props.id)
const tab = ref(0);
const alert = ref(null)
const snackbar = ref(null)
const vForm = ref<VForm>()

const errorHandler = inject('$errorHandler');

provide('alert', alert)
provide('snackbar', snackbar)

const get = (uri: string) => {
    axios.get(uri)
        .then(r => {
            Object.assign(props.item, r.data);
        })
        .catch(e => {
            snackbar.value.show(e.response.data.message, 'primary')
            const res = errorHandler(e);
        })
}
const update = async () => {
    let is_valid = await vForm.value.validate();
    let up_type = props.id != 0 ? '수정' : '생성';
    if (is_valid.valid && await alert.value.show('정말 ' + up_type + '하시겠습니까?')) {
        axios({
            headers: { "Content-Type": "multipart/form-data", },
            url: url.value,
            method: 'post',
            data: props.item,
        })
            .then(r => {
                snackbar.value.show('성공하였습니다.', 'success')
            })
            .catch(e => {
                snackbar.value.show(e.response.data.message, 'primary')
                const res = errorHandler(e);
            })
    }
    else
        snackbar.value.show(up_type + '조건에 맞지않는 필드가 존재합니다.', 'primary')
}
const disabledConditions = (index: integer) => {
    return index == 2 && props.id == 0 && props.path == 'merchandises'
}
const hideConditions = () => {
    const cond_1 = tab.value == 2 && props.path == 'merchandises' ? false : true;
    const cond_2 = props.path == 'pay-modules' ? false : true;
    return cond_1 && cond_2
}

onMounted(async () => {
    app = getCurrentInstance()
})

watchEffect(() => {
    if (props.id) {
        url.value = '/api/v1/manager/' + props.path + '/' + props.id
        get(url.value)
    }
    else
        url.value = '/api/v1/manager/' + props.path
});
</script>
<template>
    <VTabs v-model="tab" class="v-tabs-pill">
        <VTab v-for="(t, index) in props.tabs" :key="tab.icon" :disabled="disabledConditions(index)">
            <VIcon :size="18" :icon="t.icon" class="me-1" />
            <span>{{ t.title }}</span>
        </VTab>
    </VTabs>

    <VForm ref="vForm" class="mt-5">
        <VWindow v-model="tab">
            <slot name="view"></slot>
        </VWindow>
    </VForm>
    <!-- 👉 submit -->

    <VCard style="margin-top: 1em;" slot="button" v-show="hideConditions()">
        <VCol class="d-flex gap-4">
            <VBtn type="button" style="margin-left: auto;" @click="update()">
                {{ props.id == 0 ? "추가" : "수정" }}
                <VIcon end icon="tabler-checkbox" />
            </VBtn>
            <VBtn color="secondary" variant="tonal" @click="vForm.reset()">
                리셋
                <VIcon end icon="tabler-arrow-back" />
            </VBtn>
        </VCol>

    </VCard>
    <Snackbar ref="snackbar" />
    <AlertDialog ref="alert" />
    <LoadingDialog ref="loading" />
</template>
