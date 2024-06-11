<script lang="ts" setup>
import BooleanRadio from '@/layouts/utils/BooleanRadio.vue'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import { useRequestStore } from '@/views/request'
import { useSalesFilterStore } from '@/views/salesforces/useStore'
import type { NotiUrl } from '@/views/types'
import { isAbleModiy } from '@axios'
import { requiredValidatorV2 } from '@validators'
import { VForm } from 'vuetify/components'

interface Props {
    item: NotiUrl,
    able_mcht_chanage: boolean,
}
const vForm = ref<VForm>()
const props = defineProps<Props>()

const { mchts } = useSalesFilterStore()
const { update, remove } = useRequestStore()

</script>
<template>
    <VCol cols="12" md="6">
        <AppCardActions action-collapsed :title="props.item.note">
            <VDivider />
            <VForm ref="vForm">
                <div class="d-flex justify-space-between flex-wrap flex-md-nowrap flex-column flex-md-row">
                    <VCol cols="12">
                        <VCardItem>
                            <VRow class="pt-3" v-if="props.able_mcht_chanage">
                                <CreateHalfVCol :mdl="6" :mdr="6" v-if="isAbleModiy(props.item.id as number)">
                                    <template #name>소유 가맹점</template>
                                    <template #input>
                                        <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="props.item.mcht_id"
                                            :items="mchts" prepend-inner-icon="tabler-building-store" label="가맹점 선택"
                                            item-title="mcht_name" item-value="id" single-line :rules="[requiredValidatorV2(props.item.mcht_id, '가맹점')]" />
                                    </template>
                                </CreateHalfVCol>
                                <CreateHalfVCol :mdl="6" :mdr="6" v-else>
                                    <template #name><span class="font-weight-bold">소유 가맹점</span></template>
                                    <template #input>
                                        {{ mchts.find(obj => obj.id === props.item.mcht_id)?.mcht_name }}
                                    </template>
                                </CreateHalfVCol>
                            </VRow>
                            <VRow class="pt-3">
                                <CreateHalfVCol :mdl="6" :mdr="6" v-if="isAbleModiy(props.item.id as number)">
                                    <template #name>발송 URL</template>
                                    <template #input>
                                        <VTextField v-model="props.item.send_url" type="text" placeholder="https://www.test.com"
                                            :rules="[requiredValidatorV2(props.item.send_url, '발송 URL')]" />
                                    </template>
                                </CreateHalfVCol>
                                <CreateHalfVCol :mdl="6" :mdr="6" v-else>
                                    <template #name><span class="font-weight-bold">발송 URL</span></template>
                                    <template #input>
                                        {{ props.item.send_url }}
                                    </template>
                                </CreateHalfVCol>
                            </VRow>
                            <VRow class="pt-3">
                                <CreateHalfVCol :mdl="6" :mdr="6" v-if="isAbleModiy(props.item.id as number)">
                                    <template #name>노티 사용 유무</template>
                                    <template #input>
                                        <BooleanRadio :radio="props.item.noti_status"
                                            @update:radio="props.item.noti_status = $event" :rules="[requiredValidatorV2(props.item.noti_status, '노티 사용 유무')]">
                                            <template #true>사용</template>
                                            <template #false>미사용</template>
                                        </BooleanRadio>
                                    </template>
                                </CreateHalfVCol>
                                <CreateHalfVCol :mdl="6" :mdr="6" v-else>
                                    <template #name><span class="font-weight-bold">노티 사용 유무</span></template>
                                    <template #input>
                                        {{ props.item.noti_status ? '사용' : '미사용' }}
                                    </template>
                                </CreateHalfVCol>
                            </VRow>
                            <VRow>
                                <VCol v-if="isAbleModiy(props.item.id as number)">
                                    <VTextarea v-model="props.item.note" counter label="메모사항"
                                        prepend-inner-icon="twemoji-spiral-notepad" maxlength="190" auto-grow/>
                                </VCol>
                                <CreateHalfVCol :mdl="6" :mdr="6" v-else>
                                    <template #name><span class="font-weight-bold">메모사항</span></template>
                                    <template #input>
                                        {{ props.item.note }}
                                    </template>
                                </CreateHalfVCol>
                            </VRow>
                            <VRow v-if="isAbleModiy(props.item.id as number)">
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
                                    <VBtn type="button" color="warning" v-else @click="props.item.id = -1">
                                        입력란 제거
                                        <VIcon end icon="tabler-trash" />
                                    </VBtn>
                                </VCol>
                            </VRow>
                        </VCardItem>
                    </VCol>                
                </div>
            </VForm>
        </AppCardActions>
    </VCol>
</template>
