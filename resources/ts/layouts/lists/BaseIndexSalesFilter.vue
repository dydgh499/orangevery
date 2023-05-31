<script setup lang="ts">
import { useSalesFilterStore } from '@/views/salesforces/useStore'

const { sales } = useSalesFilterStore()
const store = <any>(inject('store'))
</script>
<template>
    <VRow>
        <VCol cols="12" sm="3">
            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="store.params.sales[0]" :items="sales[5].value"
                prepend-inner-icon="tabler-man" :label="`지사 선택`" item-title="nick_name" item-value="id"/>
        </VCol>
        <VCol cols="12" sm="3">
            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="store.params.sales[2]" :items="sales[3].value"
                prepend-inner-icon="tabler-man" :label="`총판 선택`" item-title="nick_name" item-value="id" />
        </VCol>
        <VCol cols="12" sm="3">
            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="store.params.sales[4]" :items="sales[1].value"
                prepend-inner-icon="tabler-man" :label="`대리점 선택`" item-title="nick_name" item-value="id" />
        </VCol>
        <VCol>
            
        </VCol>
        <VCol cols="12" sm="3">
            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="store.params.sales[1]" :items="sales[4].value"
                prepend-inner-icon="tabler-man" :label="`하위지사 선택`" item-title="nick_name" item-value="id" />
        </VCol>
        <VCol cols="12" sm="3">
            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="store.params.sales[3]" :items="sales[2].value"
                prepend-inner-icon="tabler-man" :label="`하위총판 선택`" item-title="nick_name" item-value="id" />
        </VCol>
        <VCol cols="12" sm="3">
            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="store.params.sales[5]" :items="sales[0].value"
                prepend-inner-icon="tabler-man" :label="`하위대리점 선택`" item-title="nick_name" item-value="id" />
        </VCol>
    </VRow>
</template>
