<script lang="ts" setup>
import { notiViewable } from '@/views/merchandises/noti-urls/useStore';
import { payModFilter } from '@/views/merchandises/pay-modules/useStore';
import { useRequestStore } from '@/views/request';
import { useSalesFilterStore } from '@/views/salesforces/useStore';
import type { NotiUrl, PayModule } from '@/views/types';
import { requiredValidatorV2, urlValidator } from '@validators';
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
    <VCol cols="12" md="3">
        <AppCardActions action-collapsed :title="props.item.note">
            <VDivider />
            <VForm ref="vForm">
                <div class="d-flex justify-space-between flex-wrap flex-md-nowrap flex-column flex-md-row">
                    <VCol cols="12">
                        <VCardItem>
                            <VRow v-if="props.able_mcht_chanage">
                                <VCol md="6" cols="6">소유 가맹점</VCol>
                                <VCol md="6">
                                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="props.item.mcht_id" :items="mchts"
                                            prepend-inner-icon="tabler-building-store" label="가맹점 선택" item-title="mcht_name" item-value="id"
                                            single-line :rules="[requiredValidatorV2(props.item.mcht_id, '가맹점')]" :eager="true" />
                                </VCol>
                            </VRow>
                            <VRow v-else>
                                <VCol md="6" cols="6">
                                    <span class="font-weight-bold">소유 가맹점</span>
                                </VCol>
                                <VCol md="6">
                                    {{ mchts.find(obj => obj.id === props.item.mcht_id)?.mcht_name }}
                                </VCol>
                            </VRow>
                            <VCardSubtitle></VCardSubtitle>
                            <br>

                            <VRow v-if="notiViewable()">
                                <VCol md="6" cols="12">
                                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.pmod_id"
                                    :items="[{ id: -1, note: '전체' }].concat(filterPayMod)" prepend-inner-icon="ic-outline-send-to-mobile"
                                    label="발송 결제모듈" item-title="note" item-value="id"/>
                                </VCol>
                                <VCol md="6">
                                    <VSwitch hide-details :false-value=0 :true-value=1 
                                            v-model="props.item.noti_status"
                                            label="활성여부" color="primary"
                                        />
                                </VCol>
                            </VRow>
                            <VRow v-else>
                                <VCol cols="6">
                                    <span>{{ filterPayMod.find(obj => obj.id === props.item.pmod_id)?.note }}</span>
                                </VCol>
                                <VCol cols="6">
                                    <span>{{ props.item.noti_status ? '활성' : '미활성' }}</span>
                                </VCol>
                            </VRow>

                            <VRow v-if="notiViewable()">
                                <VCol cols="12">
                                    <VTextField v-model="props.item.send_url"
                                    label="발송 URL"
                                    :rules="[requiredValidatorV2(props.item.send_url, '발송 URL'), urlValidator(props.item.send_url)]" 
                                    />
                                </VCol>
                            </VRow>
                            <VRow v-else>
                                <VCol cols="6">
                                    발송 URL
                                </VCol>
                                <VCol cols="6">
                                    <span>{{ props.item.send_url }}</span>
                                </VCol>
                            </VRow>

                            <VRow v-if="notiViewable()">
                                <VCol cols="12">
                                    <VTextField v-model="props.item.note" counter label="별칭"
                                        prepend-inner-icon="twemoji-spiral-notepad" maxlength="190" auto-grow/>
                                </VCol>
                            </VRow>
                            <VRow v-else>
                                <VCol cols="6">
                                    별칭
                                </VCol>
                                <VCol cols="6">
                                    <span>{{ props.item.note }}</span>
                                </VCol>
                            </VRow>
                        
                        <VRow v-if="notiViewable()">
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
