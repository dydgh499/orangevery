
<script setup lang="ts">

import { axios } from '@axios';
import { VForm } from 'vuetify/components';
import type { Tab } from '@/views/types'

interface Props {
    id: number | string,
    path: string,
    tabs: Tab[],
    item: object,
}
const props = defineProps<Props>()

const url = ref('/api/v1/manager/' + props.path + '/' + props.id)
const tab = ref(0);
const alert     = <any>(inject('alert'))
const snackbar  = <any>(inject('snackbar'))
const vForm = ref<VForm>()

const errorHandler = <any>(inject('$errorHandler'));


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
    const is_valid = await vForm.value?.validate();
    let up_type = props.id != 0 ? '수정' : '생성';
    if (is_valid?.valid && await alert.value.show('정말 ' + up_type + '하시겠습니까?')) {
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
const disabledConditions = (index: number) => {
    return index == 2 && props.id == 0 && props.path == 'merchandises'
}
const hideConditions = () => {
    const cond_1 = tab.value == 2 && props.path == 'merchandises' ? false : true;
    const cond_2 = props.path == 'pay-modules' ? false : true;
    const cond_3 = props.path == 'pay-gateways' ? false : true;
    return cond_1 && cond_2 && cond_3
}

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
        <VTab v-for="(t, index) in props.tabs" :key="index" :disabled="disabledConditions(index)">
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
            <VBtn color="secondary" variant="tonal" @click="vForm?.reset()">
                리셋
                <VIcon end icon="tabler-arrow-back" />
            </VBtn>
        </VCol>

    </VCard>
</template>
