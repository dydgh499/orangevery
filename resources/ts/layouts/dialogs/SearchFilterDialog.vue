<script setup lang="ts">
import { Filter } from '@/views/types';

const head = <any>(inject('head'))
const visible = ref(false)

const show = () => {
    visible.value = true
}
const getMainHeaders = (header: Filter, keys: string[], s_idx: number, e_idx: number) => {
    const headers = []
    for (let i = s_idx; i <= e_idx; i++) {
        headers.push(header[keys[i]])
    }
    return headers
}
const getHeaders = (_sub_header: any) =>  {
    if(_sub_header.type === 'string') {
        const keys = Object.keys(head.headers)
        const s_idx = keys.indexOf(_sub_header.s_col)
        const e_idx = keys.indexOf(_sub_header.e_col)
        return getMainHeaders(head.headers, keys, s_idx, e_idx)
    }
    else {
        const _object = <Filter>(head.headers[_sub_header.s_col])
        const _keys = Object.keys(_object)
        return getMainHeaders(_object, _keys, 0, _keys.length - 1)
    }
}
/*
const renderCheckboxes = (header: any) => {
    return (
        <VCol v-for="(_header, index) in head.headers" :key="index" :cols="($vuetify.display.smAndDown ? 6 : 4)">
            <VCheckbox v-model="_header.visible" :label="_header.ko"
                true-icon="tabler-eye-check" false-icon="tabler-circle-x" color="primary" />
        </VCol>
    )
}
*/
defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="visible" max-width="600">
        <!-- Dialog close btn -->
        <DialogCloseBtn @click="visible = false" />
        <!-- Dialog Content -->
        <VCard title="검색 필터">
            <VCardText>
                <div>
                    <VCol cols="12">
                        <VRow no-gutters v-if="head.sub_headers.length > 0">
                            <template v-for="(sub_header, index) in head.sub_headers" :key="index">
                                <VCol cols="12">
                                    <b class="title-search-header">{{ sub_header.ko }}</b>
                                </VCol>
                                <VCol v-for="(header, _index) in getHeaders(sub_header)" :key="_index" :cols="($vuetify.display.smAndDown ? 6 : 4)">
                                    <VCheckbox v-model="header.visible" :label="header.ko"
                                        true-icon="tabler-eye-check" false-icon="tabler-circle-x" color="primary" />
                                </VCol>
                            </template>
                        </VRow>
                        <VRow no-gutters v-else>
                            <VCol v-for="(_header, index) in head.headers" :key="index" :cols="($vuetify.display.smAndDown ? 6 : 4)">
                                <VCheckbox v-model="_header.visible" :label="_header.ko"
                                    true-icon="tabler-eye-check" false-icon="tabler-circle-x" color="primary" />
                            </VCol>
                        </VRow>
                    </VCol>
                </div>
            </VCardText>
        </VCard>
    </VDialog>
</template>
<style>
.title-search-header {
  line-height: 2em;
}
</style>
