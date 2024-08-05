<script lang="ts" setup>
import BooleanRadio from '@/layouts/utils/BooleanRadio.vue';
import CreateHalfVColV2 from '@/layouts/utils/CreateHalfVColV2.vue';
import { complaint_statuses, complaint_types } from '@/views/complaints/useStore';
import { useSalesFilterStore } from '@/views/salesforces/useStore';
import { useStore } from '@/views/services/pay-gateways/useStore';
import type { Complaint } from '@/views/types';
import { getUserLevel } from '@axios';

interface Props {
    item: Complaint,
}

const props = defineProps<Props>()
const { pgs } = useStore()
const { mchts } = useSalesFilterStore()

</script>
<template>
    <VRow class="match-height">
        <!-- 👉 개인정보 -->
        <VCol cols="12" md="6">
            <VCard>
                <VCardItem>
                    <VCardTitle>고객정보</VCardTitle>
                    <CreateHalfVColV2 :mdl="4" :mdr="8" class="pt-5">
                        <template #l_name>
                            <span v-if="getUserLevel() >= 35">고객명</span>
                            <span v-else class="font-weight-bold">고객명</span>
                        </template>
                        <template #l_input>
                            <VTextField v-if="getUserLevel() >= 35" v-model="props.item.cust_name" prepend-inner-icon="tabler-user"
                                    placeholder="고객명을 입력해주세요" persistent-placeholder />
                            <span v-else>{{ props.item.cust_name }}</span>
                        </template>
                        <template #r_name>
                            <span v-if="getUserLevel() >= 35">연락처</span>
                            <span v-else class="font-weight-bold">연락처</span>
                        </template>
                        <template #r_input>
                            <VTextField v-if="getUserLevel() >= 35"
                                    v-model="props.item.phone_num" prepend-inner-icon="tabler-device-mobile"
                                    type="number" placeholder="연락처를 입력해주세요" persistent-placeholder />
                            <span v-else>{{ props.item.phone_num }}</span>
                        </template>
                    </CreateHalfVColV2>

                    <CreateHalfVColV2 :mdl="4" :mdr="8">
                        <template #l_name>
                            <span v-if="getUserLevel() >= 35">수기작성성함</span>
                            <span v-else class="font-weight-bold">수기작성성함</span>
                        </template>
                        <template #l_input>

                            <VTextField v-if="getUserLevel() >= 35"
                                    v-model="props.item.hand_cust_name" prepend-inner-icon="tabler-user"
                                    placeholder="수기작성성함을 입력해주세요" persistent-placeholder/>
                            <span v-else>{{ props.item.hand_cust_name }}</span>
                        </template>
                        <template #r_name>
                            <span v-if="getUserLevel() >= 35">수기작성연락처</span>
                            <span v-else class="font-weight-bold">수기작성연락처</span>
                        </template>
                        <template #r_input>
                            <VTextField v-model="props.item.hand_phone_num" prepend-inner-icon="tabler-device-mobile"
                                    type="number" placeholder="수기작성연락처를 입력해주세요" persistent-placeholder v-if="getUserLevel() >= 35" />
                            <span v-else>{{ props.item.hand_phone_num }}</span>
                        </template>
                    </CreateHalfVColV2>
                    <VRow>
                        <VCol>
                            <VTextarea v-model="props.item.note" counter label="메모사항"
                                prepend-inner-icon="twemoji-spiral-notepad" maxlength="250" auto-grow  :readonly="getUserLevel() >= 35 ? false : true"/>                                
                        </VCol>                        
                        <VDivider />
                    </VRow>
                </VCardItem>
            </VCard>
        </VCol>
        <!-- 👉 계약정보 -->
        <VCol cols="12" md="6">
            <VCard>
                <VCardItem>
                    <VCardTitle>민원정보</VCardTitle>

                    <CreateHalfVColV2 :mdl="4" :mdr="8" class="pt-5">
                        <template #l_name>
                            <span v-if="getUserLevel() >= 35">가맹점</span>
                            <span v-else class="font-weight-bold">가맹점</span>
                        </template>
                        <template #l_input>
                            <VAutocomplete v-if="getUserLevel() >= 35"
                                :menu-props="{ maxHeight: 400 }" v-model="props.item.mcht_id" :items="mchts"
                                prepend-inner-icon="tabler-building-store" label="가맹점 선택" item-title="mcht_name"
                                item-value="id" single-line />
                            <span v-else>{{ mchts.find(obj => obj.id === props.item.mcht_id)?.mcht_name }}</span>
                        </template>
                        <template #r_name>
                            <span v-if="getUserLevel() >= 35">거래번호</span>
                            <span v-else class="font-weight-bold">거래번호</span>
                        </template>
                        <template #r_input>
                            <VTextField v-if="getUserLevel() >= 35"
                                v-model="props.item.tid" prepend-inner-icon="jam-key-f" placeholder="거래번호를 입력해주세요"
                                persistent-placeholder />
                            <span v-else>{{ props.item.tid }}</span>
                        </template>
                    </CreateHalfVColV2>

                    <CreateHalfVColV2 :mdl="4" :mdr="8">
                        <template #l_name>
                            <span v-if="getUserLevel() >= 35">민원타입</span>
                            <span v-else class="font-weight-bold">민원타입</span>
                        </template>
                        <template #l_input>
                            <VSelect v-if="getUserLevel() >= 35"
                                :menu-props="{ maxHeight: 400 }" v-model="props.item.type" :items="complaint_types"
                                prepend-inner-icon="ic-round-sentiment-dissatisfied" label="민원 타입 선택" item-title="title"
                                item-value="id" single-line />
                            <span v-else>{{ complaint_types.find(obj => obj.id === props.item.type)?.title }}</span>
                        </template>
                        <template #r_name>
                            <span v-if="getUserLevel() >= 35">승인일</span>
                            <span v-else class="font-weight-bold">승인일</span>
                        </template>
                        <template #r_input>
                            <VTextField v-if="getUserLevel() >= 35"
                                type="date" v-model="props.item.appr_dt" prepend-inner-icon="ic-baseline-calendar-today"
                                style="max-width: 13em;"/>
                            <span v-else>{{ props.item.appr_dt }}</span>
                        </template>
                    </CreateHalfVColV2>

                    <CreateHalfVColV2 :mdl="4" :mdr="8">
                        <template #l_name>
                            <span v-if="getUserLevel() >= 35">승인번호</span>
                            <span v-else class="font-weight-bold">승인번호</span>
                        </template>
                        <template #l_input>
                            <VTextField v-if="getUserLevel() >= 35"
                                v-model="props.item.appr_num" prepend-inner-icon="tabler-receipt-2"
                                placeholder="4723124" />
                            <span v-else>{{ props.item.appr_num }}</span>
                        </template>
                        <template #r_name>
                            <span v-if="getUserLevel() >= 35">발급사</span>
                            <span v-else class="font-weight-bold">발급사</span>
                        </template>
                        <template #r_input>
                            <VTextField v-if="getUserLevel() >= 35"
                                v-model="props.item.issuer" prepend-inner-icon="tabler-building-fortress"
                                placeholder="4723124" persistent-placeholder />
                            <span v-else>{{ props.item.issuer }}</span>
                        </template>
                    </CreateHalfVColV2>
                    <CreateHalfVColV2 :mdl="4" :mdr="8">
                        <template #l_name>
                            <span v-if="getUserLevel() >= 35">PG사</span>
                        </template>
                        <template #l_input>
                            <VSelect v-if="getUserLevel() >= 35"
                                :menu-props="{ maxHeight: 400 }" v-model="props.item.pg_id" :items="pgs"
                                prepend-inner-icon="ph-buildings" label="PG사 선택" item-title="pg_name" item-value="id"
                                single-line  />
                        </template>
                        <template #r_name>
                            <span v-if="getUserLevel() >= 35">유입경로</span>
                            <span v-else class="font-weight-bold">유입경로</span>
                        </template>
                        <template #r_input>

                            <VTextField v-if="getUserLevel() >= 35"
                                v-model="props.item.entry_path" prepend-inner-icon="tabler-door-enter"
                                placeholder="유입경로를 입력해주세요" persistent-placeholder />
                            <span v-else>{{ props.item.entry_path }}</span>
                        </template>
                    </CreateHalfVColV2>

                    <CreateHalfVColV2 :mdl="4" :mdr="8">
                        <template #l_name>
                            <span v-if="getUserLevel() >= 35">민원상태</span>
                            <span v-else class="font-weight-bold">민원상태</span>
                        </template>
                        <template #l_input>
                            <VSelect v-if="getUserLevel() >= 35"
                                :menu-props="{ maxHeight: 400 }" v-model="props.item.complaint_status" :items="complaint_statuses"
                                item-title="title" item-value="id" single-line />
                            <span v-else>{{ complaint_statuses.find(obj => obj.id === props.item.complaint_status)?.title }}</span>
                        </template>
                        <template #r_name>
                            <span v-if="getUserLevel() >= 35">입금상태</span>
                            <span v-else class="font-weight-bold">입금상태</span>
                        </template>
                        <template #r_input>

                            <BooleanRadio v-if="getUserLevel() >= 35"
                                :radio="props.item.is_deposit" @update:radio="props.item.is_deposit = $event">
                                <template #true>입금</template>
                                <template #false>미입금</template>
                            </BooleanRadio>
                            <span v-else>{{ props.item.is_deposit ? "입금" : "미입금" }}</span>
                        </template>
                    </CreateHalfVColV2>
                </VCardItem>
            </VCard>
        </VCol>
    </VRow>
</template>
