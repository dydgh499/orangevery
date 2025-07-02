<script setup lang="ts">
import { useDynamicTabStore } from '@/@core/utils/dynamicTab';
import router from '@/router';
import { useRequestStore } from '@/views/request';
import type { Tab } from '@/views/types';
import { getUserLevel, isAbleModiyV2 } from '@axios';

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
    const cond_1 = index == 3 && getUserLevel() < 40 && props.path == 'services/brands'
    return cond_1
}


const hideConditions = () => {
    const cond_1 = props.path === 'services/pay-modules' ? false : true
    const cond_2 = props.path === 'services/pay-gateways' ? false : true

    return cond_1 && cond_2
}

const authHideConditions = () => {
    const level = getUserLevel()
    if(level < 35) {
        return isAbleModiyV2(props.item as number, props.path)
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
        if(props.path === 'services/brands')
            store.titleUpdate(props.item.id, '운영사', props.item.name)
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
        <VCol>
            <div>
                <div class="d-flex gap-4" style="justify-content: end;">
                    <template v-if="hideConditions() && authHideConditions()">
                        <VBtn type="button" @click="formRequest('/'+props.path, props.item, vForm)">
                            {{ props.id === 0 ? "추가" : "수정" }}
                            <VIcon end size="20" icon="tabler-pencil" />
                        </VBtn>
                        <VBtn type="button" color="error" v-if="props.id" @click="remove('/'+props.path, props.item)">
                            삭제
                            <VIcon end size="20" icon="tabler-trash" />
                        </VBtn>
                    </template>
                    <VBtn type="button" color="warning" @click="back()">
                        뒤로가기
                        <VIcon end size="20" icon="tabler:arrow-back" />
                    </VBtn>
                </div>
            </div>
        </VCol>
    </VCard>
</template>
