
<script setup lang="ts">

import { axios } from '@axios';
import { VForm } from 'vuetify/components';
import type { Tab } from '@/views/types'
import router from '@/router';

interface Props {
    id: number | string,
    path: string,
    tabs: Tab[],
    item: object,
}
const props = defineProps<Props>()

const url = ref('/api/v1/manager/' + props.path + '/' + props.id)
const tab = ref(0);
const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))
const errorHandler = <any>(inject('$errorHandler'))
const vForm = ref<VForm>()

const get = (uri: string) => {
    axios.get(uri)
        .then(r => { Object.assign(props.item, r.data); })
        .catch(e => { 
            snackbar.value.show(e.response.data.message, 'error') 
            const r = errorHandler(e)
        })
}

const getParams = (formData: FormData, data: any, parentKey = '') => {
    for (const key in data) {
        if (data.hasOwnProperty(key)) {
            const value = data[key];
            const formKey = parentKey ? `${parentKey}[${key}]` : key;

            if (value instanceof File)  // 파일 객체인 경우 직접 추가
                formData.append(formKey, value);
            else if (value instanceof Date)// 날짜 객체인 경우 ISO 8601 형식으로 변환하여 추가                    
                formData.append(formKey, value.toISOString());
            else if (value instanceof Object && value !== null) // 객체인 경우 재귀 호출을 통해 중첩된 속성 처리                   
                getParams(formData, value, formKey);
            else if (value != null)// 기본 타입인 경우 문자열로 변환하여 추가
                formData.append(formKey, value);
        }
    }
}

const update = async () => {
    const is_valid = await vForm.value?.validate();
    if (is_valid?.valid && await alert.value.show('정말 ' + (props.id != 0 ? '수정' : '생성') + '하시겠습니까?')) {
        try {
            const formData = new FormData();
            getParams(formData, props.item)

            const params = {
                url: url.value,
                data: formData,
                method: props.id != 0 ? 'put' : 'post',
                headers: { 'Content-Type': "multipart/form-data", }
            };
            const res = await axios(params);
            snackbar.value.show('성공하였습니다.', 'success')
            setTimeout(function () { router.replace('/' + props.path) }, 1000);
        }
        catch (e: any) {
            snackbar.value.show(e.response.data.message, 'error')
            const r = errorHandler(e)
        }
    }
    else
        snackbar.value.show((props.id != 0 ? '수정' : '생성') + '조건에 맞지않는 필드가 존재합니다.', 'warning')
}
const remove = async () => {
    if (await alert.value.show('정말 삭제하시겠습니까?')) {
        try {
            const res = await axios.delete(url.value)
            snackbar.value.show('성공하였습니다.', 'success')
            setTimeout(function () { router.replace('/' + props.path) }, 1000);
        }
        catch (e: any) {
            snackbar.value.show(e.response.data.message, 'error')
            const r = errorHandler(e);
        }
    }
}

const disabledConditions = (index: number) => {
    return index == 2 && props.id == 0 && props.path == 'merchandises'
}

const hideConditions = () => {
    const cond_1 = tab.value == 2 && props.path == 'merchandises' ? false : true;
    const cond_2 = props.path == 'pay-modules' ? false : true;
    const cond_3 = props.path == 'pay-gateways' ? false : true;
    const cond_4 = props.path == 'services/bulk-registration' ? false : true;
    return cond_1 && cond_2 && cond_3 && cond_4
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
    <slot name="additional_explaination"></slot>
    <VForm ref="vForm" class="mt-5">
        <VWindow v-model="tab">
            <slot name="view"></slot>
        </VWindow>
    </VForm>
    <VCard style="margin-top: 1em;" slot="button" v-show="hideConditions()">
        <VCol class="d-flex gap-4">
            <VBtn type="button" style="margin-left: auto;" @click="update()">
                {{ props.id == 0 ? "추가" : "수정" }}
                <VIcon end icon="tabler-checkbox" />
            </VBtn>
            <VBtn type="button" color="secondary" variant="tonal" @click="vForm?.reset()">
                리셋
                <VIcon end icon="tabler-arrow-back" />
            </VBtn>
            <VBtn type="button" color="error" v-if="props.id" @click="remove()">
                삭제
                <VIcon size="22" icon="tabler-trash" />
            </VBtn>
        </VCol>
    </VCard>
</template>
