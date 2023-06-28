a
<script setup lang="ts">

import { VForm } from 'vuetify/components'
import type { Tab } from '@/views/types'
import { useRequestStore } from '@/views/request'


interface Props {
    id: number | string,
    path: string,
    tabs: Tab[],
    item: object,
}
const props = defineProps<Props>()

const tab = ref(0)
const vForm = ref<VForm>()

const { formRequest, remove, setOneObject } = useRequestStore()

const disabledConditions = (index: number) => {
    return index == 2 && props.id == 0 && props.path == 'merchandises'
}

const hideConditions = () => {
    const cond_1 = tab.value == 2 && props.path == 'merchandises' ? false : true;
    const cond_2 = props.path == 'merchandises/pay-modules' ? false : true;
    const cond_3 = props.path == 'services/pay-gateways' ? false : true;
    const cond_4 = props.path == 'services/bulk-registration' ? false : true;
    return cond_1 && cond_2 && cond_3 && cond_4
}

watchEffect(() => {
    if (props.id) 
        setOneObject('/' + props.path, Number(props.id), props.item)
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
            <VBtn type="button" style="margin-left: auto;" @click="formRequest('/'+props.path, Number(props.id), props.item, vForm)">
                {{ props.id == 0 ? "추가" : "수정" }}
                <VIcon end icon="tabler-pencil" />
            </VBtn>
            <VBtn type="button" color="secondary" variant="tonal" @click="vForm?.reset()">
                리셋
                <VIcon end icon="tabler-arrow-back" />
            </VBtn>
            <VBtn type="button" color="error" v-if="props.id" @click="remove('/'+props.path, Number(props.id))">
                삭제
                <VIcon size="22" icon="tabler-trash" />
            </VBtn>
        </VCol>
    </VCard>
</template>
