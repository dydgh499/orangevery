<script setup lang="ts">

const head = <any>(inject('head'))
const visible = ref(false)

let count = 0
const show = () => {
    initCount()
    visible.value = true;
}

const initCount = () => {
    count = 0
}

const getFirstHeader = () => {
    if(head.main_headers.length && count == 0) {
        console.log('first', count)
        count++
        return head.main_headers[0]
    }
    else
        return ''
}

const getLastHeader = () => {
    if(head.main_headers.length == count+1) {
        console.log('last', count)
        return head.main_headers[count]
    }
    else
        return ''
}

const doubleHeaders = () => {
    if(head.main_headers.length > count) {
        const hd = head.main_headers[count]
        console.log('middle', count)
        count++
        return hd
    }
    return ""
}
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
                        <VRow no-gutters>
                            <VCol cols="12">
                                <b class="title-search-header">{{ getFirstHeader() }}</b>
                            </VCol>
                            <template v-for="(_header, _key, _index) in head.headers" :key="_index">
                                <template v-if="head.getDepth(_header, 0) != 1">
                                    <VCol cols="12">
                                        <b class="title-search-header">{{ doubleHeaders() }}</b>
                                    </VCol>
                                    <VCol v-for="(__header, __key, __index) in _header" :key="__index" :cols="($vuetify.display.smAndDown ? 6 : 4)">
                                        <VCheckbox v-model="__header.visible" :label="__header.ko"
                                            true-icon="tabler-eye-check" false-icon="tabler-circle-x" color="primary" />
                                    </VCol>
                                    <VCol cols="12">
                                        <b class="title-search-header">{{ getLastHeader() }}</b>
                                    </VCol>
                                </template>
                                <template v-else>
                                    <VCol :cols="($vuetify.display.smAndDown ? 6 : 4)">
                                        <VCheckbox v-model="_header.visible" :label="_header.ko"
                                            true-icon="tabler-eye-check" false-icon="tabler-circle-x" color="primary" />
                                    </VCol>
                                </template>
                            </template>
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
