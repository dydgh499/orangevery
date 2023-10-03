<script lang="ts" setup>
import { useRequestStore } from '@/views/request'
import type { UnderAutoSetting, Salesforce } from '@/views/types'
import { useSalesFilterStore, getLevelByIndex } from '@/views/salesforces/useStore'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import { VForm } from 'vuetify/components'
import corp from '@corp'

interface Props {
    item: UnderAutoSetting,
    salesforce: Salesforce,
}
const vForm = ref<VForm>()
const props = defineProps<Props>()
const { sales } = useSalesFilterStore()

const { update, remove } = useRequestStore()
const levels = corp.pv_options.auth.levels

const isAbleAutoSetting = (idx: number) => {
    return (getLevelByIndex(props.salesforce.level) >= (6-idx)) && levels['sales'+(6-idx)+'_use']
}
</script>
<template>
    <AppCardActions action-collapsed :title="props.item.note">
        <VDivider />
        <VForm ref="vForm">
            <div class="d-flex justify-space-between flex-wrap flex-md-nowrap flex-column flex-md-row">
                <VCol cols="12">
                    <VCardItem>
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name>별칭</template>
                            <template #input>
                                <VTextField v-model="props.item.note" type="text" placeholder="별칭 입력" prepend-inner-icon="twemoji-spiral-notepad"/>
                            </template>
                        </CreateHalfVCol>
                        <template v-for="i in 6" :key="i">
                            <CreateHalfVCol :mdl="3" :mdr="9" v-if="isAbleAutoSetting(i)">
                                <template #name>{{ levels['sales'+(6-i)+'_name'] }}/수수료율</template>
                                <template #input>
                                    <VRow>
                                        <VCol v-if="getLevelByIndex(props.salesforce.level) == 6-i">
                                            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="props.item['sales'+(6-i)+'_id']"
                                            :items="[{ id: null, sales_name: '선택안함' }].concat(sales[6-i].value)"
                                            prepend-inner-icon="ph:share-network" :label="levels['sales'+(6-i)+'_name'] + ' 선택'"
                                            item-title="sales_name" item-value="id" single-line readonly/>
                                        </VCol>
                                        <VCol v-else>
                                            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="props.item['sales'+(6-i)+'_id']"
                                            :items="[{ id: null, sales_name: '선택안함' }].concat(sales[6-i].value)"
                                            prepend-inner-icon="ph:share-network" :label="levels['sales'+(6-i)+'_name'] + ' 선택'"
                                            item-title="sales_name" item-value="id" single-line/>
                                        </VCol>
                                        <VCol>
                                            <VTextField v-model="props.item['sales'+(6-i)+'_fee']" type="number" suffix="%"/>
                                        </VCol>
                                    </VRow>
                                </template>
                            </CreateHalfVCol>
                        </template>
                        <br>
                        <VRow>
                            <VCol class="d-flex gap-4">
                                <VBtn type="button" style="margin-left: auto;"
                                    @click="update('/salesforces/under-auto-settings', props.item.id, props.item, vForm)">
                                    {{ props.item.id == 0 ? "추가" : "수정" }}
                                    <VIcon end icon="tabler-pencil" />
                                </VBtn>
                                <VBtn color="secondary" variant="tonal" @click="vForm?.reset()">
                                    리셋
                                    <VIcon end icon="tabler-arrow-back" />
                                </VBtn>
                                <VBtn type="button" color="error" v-if="props.item.id"
                                    @click="remove('/salesforces/under-auto-settings', props.item.id)">
                                    삭제
                                    <VIcon end icon="tabler-trash" />
                                </VBtn>
                            </VCol>
                        </VRow>
                    </VCardItem>
                </VCol>
            </div>
        </VForm>
    </AppCardActions>
</template>
