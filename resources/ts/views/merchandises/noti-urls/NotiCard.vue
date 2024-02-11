<script lang="ts" setup>
import { useRequestStore } from '@/views/request'
import { requiredValidator, nullValidator } from '@validators'
import type { NotiUrl, Merchandise } from '@/views/types'
import BooleanRadio from '@/layouts/utils/BooleanRadio.vue'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import { VForm } from 'vuetify/components'

interface Props {
    item: NotiUrl,
    able_mcht_chanage: boolean,
    merchandises: Merchandise[]
}
const vForm = ref<VForm>()
const props = defineProps<Props>()

const { update, remove } = useRequestStore()

const mcht = ref(null)

watchEffect(() => {
    if(props.able_mcht_chanage)
        props.item.mcht_id = mcht.value
})

</script>
<template>
    <AppCardActions action-collapsed :title="props.item.note">
        <VDivider />
        <VForm ref="vForm">
            <div class="d-flex justify-space-between flex-wrap flex-md-nowrap flex-column flex-md-row">
                <VCol cols="12">
                    <VCardItem>
                        <VRow class="pt-3" v-if="props.able_mcht_chanage">
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>소유 가맹점</template>
                                <template #input>
                                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="mcht"
                                        :items="props.merchandises" prepend-inner-icon="tabler-building-store" label="가맹점 선택"
                                        item-title="mcht_name" item-value="id" single-line :rules=[nullValidator] />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow class="pt-3">
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>발송 URL</template>
                                <template #input>
                                    <VTextField v-model="props.item.send_url" type="text" placeholder="https://www.test.com"
                                        :rules="[requiredValidator]" />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow class="pt-3">
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>노티 사용 유무</template>
                                <template #input>
                                    <BooleanRadio :radio="props.item.noti_status"
                                        @update:radio="props.item.noti_status = $event" :rules="[nullValidator]">
                                        <template #true>사용</template>
                                        <template #false>미사용</template>
                                    </BooleanRadio>
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow>
                            <VCol>
                                <VTextarea v-model="props.item.note" counter label="메모사항"
                                    prepend-inner-icon="twemoji-spiral-notepad" maxlength="190" auto-grow/>
                            </VCol>
                        </VRow>
                        <VRow>
                            <VCol class="d-flex gap-4">
                                <VBtn type="button" style="margin-left: auto;"
                                    @click="update('/merchandises/noti-urls', props.item, vForm, false)">
                                    {{ props.item.id == 0 ? "추가" : "수정" }}
                                    <VIcon end icon="tabler-pencil" />
                                </VBtn>
                                <VBtn type="button" color="error" v-if="props.item.id"
                                    @click="remove('/merchandises/noti-urls', props.item, false)">
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
