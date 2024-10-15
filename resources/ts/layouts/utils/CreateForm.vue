a
<script setup lang="ts">
import { useDynamicTabStore } from '@/@core/utils/dynamic_tab';
import router from '@/router';
import { useRequestStore } from '@/views/request';
import type { Tab } from '@/views/types';
import { getUserLevel, isAbleModiy, user_info } from '@axios';

import { VForm } from 'vuetify/components';

interface Props {
    id: number | string,
    path: string,
    tabs: Tab[],
    item: any,
}
const props = defineProps<Props>()

const route = useRoute()
const tab = ref(0)
const vForm = ref<VForm>()

const { formRequest, remove, setOneObject } = useRequestStore()
const store = useDynamicTabStore()

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

const back = () => {
    router.replace('/' + props.path.replace('posts/view', 'posts'))
}

if (props.id) 
        setOneObject('/' + props.path, Number(props.id), props.item)

watchEffect(() => {
    if (props.item.id) {
        if(props.path === 'merchandises')
            store.titleUpdate(props.item.id, '가맹점', props.item.mcht_name)
        else if(props.path === 'salesforces')
            store.titleUpdate(props.item.id, '영업점', props.item.sales_name)
        else if(props.path === 'posts') {
            if(store.postTitleUpdate(' 수정', props.item.type, props.item.id, `/${props.path}/edit/${props.item.id}`) === false)
                store.postTitleUpdate(' 답변', props.item.type, props.item.id, `/${props.path}/reply?parent_id=${route.query.parent_id}`)
        }
        else if(props.path === 'posts/view') {
            store.postTitleUpdate('', props.item.type, props.item.id, `/${props.path}/${props.item.id}`)
        }
    }
})

onDeactivated(() => {
    const tooltips = document.querySelectorAll('.v-tooltip.v-overlay--active')
    tooltips.forEach((tooltip) => {
        tooltip.classList.remove('v-overlay--active')
        const contents = tooltip.querySelectorAll('.v-overlay__content')
        contents.forEach((content) => {
            (content as HTMLElement).style.display = 'none'; // 툴팁 강제 숨김 처리
        })
    })
})
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
    <VCard style="margin-top: 1em;" slot="button">
        <VCol class="d-flex gap-4" style="justify-content: end;">
            <template v-if="hideConditions() && authHideConditions()">
                <VBtn type="button" @click="formRequest('/'+props.path, props.item, vForm)">
                    {{ props.id == 0 ? "추가" : "수정" }}
                    <VIcon end icon="tabler-pencil" />
                </VBtn>
                <VBtn type="button" color="error" v-if="props.id" @click="remove('/'+props.path, props.item)">
                    삭제
                    <VIcon size="22" icon="tabler-trash" />
                </VBtn>
            </template>
            <VBtn type="button" color="warning" @click="back()">
                뒤로가기
                <VIcon end size="22" icon="tabler:arrow-back" />
            </VBtn>
        </VCol>
    </VCard>
</template>
