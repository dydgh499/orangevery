a
<script setup lang="ts">
import { useRequestStore } from '@/views/request'
import type { Tab } from '@/views/types'
import { getUserLevel, isAbleModiy, user_info } from '@axios'
import { VForm } from 'vuetify/components'

interface Props {
    id: number | string,
    path: string,
    tabs: Tab[],
    item: any,
}
const props = defineProps<Props>()

const tab = ref(0)
const vForm = ref<VForm>()

const { formRequest, remove, setOneObject } = useRequestStore()

const disabledConditions = (index: number) => {
    const cond_1 = index == 2 && props.id == 0 && props.path == 'merchandises'
    const cond_2 = index == 3 && props.id == 0 && props.path == 'merchandises'
    const cond_3 = index == 3 && getUserLevel() < 40 && props.path == 'services/brands'
    return cond_1 || cond_2 || cond_3
}

const hideConditions = () => {
    const cond_1 = tab.value == 2 && props.path === 'merchandises' ? false : true
    const cond_2 = tab.value == 3 && props.path === 'merchandises' ? false : true
    const cond_3 = props.path === 'merchandises/pay-modules' ? false : true
    const cond_4 = props.path === 'merchandises/noti-urls' ? false : true
    const cond_5 = props.path === 'services/pay-gateways' ? false : true
    const cond_6 = props.path === 'services/bulk-register' ? false : true
    const cond_7 = props.path === 'posts/view' ? false : true

    return cond_1 && cond_2 && cond_3 && cond_4 && cond_5 && cond_6 && cond_7
}

const authHideConditions = () => {
    const level = getUserLevel()
    if(level < 35) {
        if(props.path === 'posts') {
            if(props.id === 0)
                return true
            else if(props.id !== 0 && props.item.writer === user_info.value.user_name)
                return true
            else
                return false
        }
        else 
            return isAbleModiy(props.id as number)
    }
    else
    {
        if(props.path === 'services/brands' && (getUserLevel() >= 40) === false)
            return false
        else if(props.path === 'services/operators' && getUserLevel() < 40)
            return false
        else
            return true
    }
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
    <VForm ref="vForm" class="mt-5">
        <VWindow v-model="tab" :touch="false">
            <slot name="view"></slot>
        </VWindow>
    </VForm>
    <VCard style="margin-top: 1em;" slot="button" v-if="hideConditions() && authHideConditions()">
        <VCol class="d-flex gap-4">
            <VBtn type="button" style="margin-left: auto;" @click="formRequest('/'+props.path, props.item, vForm)">
                {{ props.id == 0 ? "추가" : "수정" }}
                <VIcon end icon="tabler-pencil" />
            </VBtn>
            <VBtn type="button" color="error" v-if="props.id" @click="remove('/'+props.path, props.item)">
                삭제
                <VIcon size="22" icon="tabler-trash" />
            </VBtn>
        </VCol>
    </VCard>
</template>
