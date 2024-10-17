<script lang="ts" setup>
import BooleanRadio from '@/layouts/utils/BooleanRadio.vue';
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue';
import CreateHalfVColV2 from '@/layouts/utils/CreateHalfVColV2.vue';
import { payModFilter } from '@/views/merchandises/pay-modules/useStore';
import { useRequestStore } from '@/views/request';
import { useSalesFilterStore } from '@/views/salesforces/useStore';
import type { NotiUrl, PayModule } from '@/views/types';
import { isAbleModiy } from '@axios';
import { requiredValidatorV2 } from '@validators';
import { VForm } from 'vuetify/components';

interface Props {
    item: NotiUrl,
    able_mcht_chanage: boolean,
    pay_modules: PayModule[]
}
const vForm = ref<VForm>()
const props = defineProps<Props>()

const { mchts } = useSalesFilterStore()
const { update, remove } = useRequestStore()

const filterPayMod = computed(() => {
    const filter = props.pay_modules.filter((obj: PayModule) => { return obj.mcht_id == props.item.mcht_id })
    props.item.pmod_id = payModFilter(props.pay_modules, filter, props.item.pmod_id as number)
    return filter
})
</script>
<template>
    <VCol cols="12" md="6">
        <AppCardActions action-collapsed :title="props.item.note">
            <VDivider />
            <VForm ref="vForm">
                <div class="d-flex justify-space-between flex-wrap flex-md-nowrap flex-column flex-md-row">
                    <VCol cols="12">
                        <VCardItem>
                            <CreateHalfVColV2 :mdl="5" :mdr="7" class="pt-3">
                            <template #l_name>
                                <span>소유 가맹점</span>
                            </template>
                            <template #l_input>
                                <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="props.item.mcht_id"
                                        :items="mchts" prepend-inner-icon="tabler-building-store" label="가맹점 선택" v-if="isAbleModiy(props.item.id as number) && props.able_mcht_chanage"
                                        item-title="mcht_name" item-value="id" single-line :rules="[requiredValidatorV2(props.item.mcht_id, '가맹점')]" />
                                <span v-else>{{ mchts.find(obj => obj.id === props.item.mcht_id)?.mcht_name }}</span>
                            </template>
                            <template #r_name>발송 결제모듈</template>
                            <template #r_input>
                                <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.pmod_id"
                                    :items="[{ id: -1, note: '전체' }].concat(filterPayMod)" prepend-inner-icon="ic-outline-send-to-mobile"
                                    label="결제모듈 선택" item-title="note" item-value="id" single-line  v-if="isAbleModiy(props.item.id as number)"/>
                                <span v-else>{{ [{ id: -1, note: '전체' }].concat(filterPayMod).find(obj => obj.id === props.item.pmod_id)?.note }}</span>
                            </template>
                        </CreateHalfVColV2>

                        <CreateHalfVColV2 :mdl="5" :mdr="7" class="pt-3">
                            <template #l_name>발송 URL</template>
                            <template #l_input>
                                <VTextField v-model="props.item.send_url" type="text" placeholder="https://www.test.com"
                                    :rules="[requiredValidatorV2(props.item.send_url, '발송 URL')]" v-if="isAbleModiy(props.item.id as number)"/>
                                <span v-else>{{ mchts.find(obj => obj.id === props.item.mcht_id)?.mcht_name }}</span>
                            </template>
                            <template #r_name>사용여부</template>
                            <template #r_input>
                                <BooleanRadio :radio="props.item.noti_status" v-if="isAbleModiy(props.item.id as number)"
                                    @update:radio="props.item.noti_status = $event" :rules="[requiredValidatorV2(props.item.noti_status, '노티 사용 유무')]">
                                    <template #true>사용</template>
                                    <template #false>미사용</template>
                                </BooleanRadio>
                                <span v-else>{{ props.item.noti_status ? '사용' : '미사용' }}</span>
                            </template>
                        </CreateHalfVColV2>
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
                                    @click="update('/merchandises/noti-urls', props.item, vForm, props.able_mcht_chanage)">
                                    {{ props.item.id == 0 ? "추가" : "수정" }}
                                    <VIcon end icon="tabler-pencil" />
                                </VBtn>
                                <VBtn type="button" color="error" v-if="props.item.id"
                                    @click="remove('/merchandises/noti-urls', props.item, props.able_mcht_chanage)">
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
