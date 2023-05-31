<script lang="ts" setup>
import { axios } from '@axios';
import { requiredValidator, nullValidator } from '@validators';
import type { Complaint, Options } from '@/views/types'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue';
import BooleanRadio from '@/layouts/utils/BooleanRadio.vue';
import { useStore } from '@/views/services/pay-gateways/useStore';
import { issuers, complaint_types } from '@/views/complaints/useStore';

interface Props {
    item: Complaint,
}

const props = defineProps<Props>()
const { pgs } = useStore()


const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))
const errorHandler = inject('$errorHandler');

onMounted(() => {
    props.item.pg_id = props.item.pg_id == 0 ? null : props.item.pg_id
})
</script>
<template>
    <VRow class="match-height">
        <!-- 👉 개인정보 -->
        <VCol cols="12" md="6">
            <VCard>
                <VCardItem>
                    <VCardTitle>고객정보</VCardTitle>
                    <VRow class="pt-5">
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name>고객명</template>
                            <template #input>
                                <VTextField v-model="props.item.cust_nm" prepend-inner-icon="tabler-user"
                                    placeholder="고객명을 입력해주세요" persistent-placeholder :rules="[requiredValidator]" />
                            </template>
                        </CreateHalfVCol>
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name>연락처</template>
                            <template #input>
                                <VTextField v-model="props.item.phone_num" prepend-inner-icon="tabler-device-mobile" type="number"
                                    placeholder="연락처를 입력해주세요" persistent-placeholder :rules="[requiredValidator]" />
                            </template>
                        </CreateHalfVCol>
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name>수기작성성함</template>
                            <template #input>
                                <VTextField v-model="props.item.hand_cust_nm" prepend-inner-icon="tabler-user"
                                    placeholder="수기작성성함을 입력해주세요" persistent-placeholder />
                            </template>
                        </CreateHalfVCol>
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name>수기작성연락처</template>
                            <template #input>
                                <VTextField v-model="props.item.hand_phone_num" prepend-inner-icon="tabler-device-mobile" type="number"
                                    placeholder="수기작성연락처를 입력해주세요" persistent-placeholder />
                            </template>
                        </CreateHalfVCol>
                        <VCol>
                            <VTextarea v-model="props.item.note" counter label="메모사항"
                                prepend-inner-icon="twemoji-spiral-notepad" />
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
                    <CreateHalfVCol :mdl="3" :mdr="9">
                        <template #name>TID</template>
                        <template #input>
                            <VTextField v-model="props.item.tid" prepend-inner-icon="jam-key-f"
                                placeholder="TID를 입력해주세요" persistent-placeholder :rules="[requiredValidator]" />
                        </template>
                    </CreateHalfVCol>
                    <CreateHalfVCol :mdl="3" :mdr="9">
                        <template #name>민원타입</template>
                        <template #input>
                            <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.type" :items="complaint_types"
                                prepend-inner-icon="ic-round-sentiment-dissatisfied" label="민원 타입 선택" item-title="title" item-value="id"
                                single-line />
                        </template>
                    </CreateHalfVCol>
                    <CreateHalfVCol :mdl="3" :mdr="9">
                        <template #name>승인일</template>
                        <template #input>
                            <AppDateTimePicker v-model="props.item.appr_dt"
                                    prepend-inner-icon="ic-baseline-calendar-today" label="승인일" :rules="[requiredValidator]" />
                        </template>
                    </CreateHalfVCol>
                    <CreateHalfVCol :mdl="3" :mdr="9">
                        <template #name>승인번호</template>
                        <template #input>
                            <VTextField v-model="props.item.appr_num" prepend-inner-icon="tabler-receipt-2"
                                placeholder="4723124" persistent-placeholder :rules="[requiredValidator]" />
                        </template>
                    </CreateHalfVCol>
                    <CreateHalfVCol :mdl="3" :mdr="9">
                        <template #name>발급사</template>
                        <template #input>
                            <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.issuer_id" :items="issuers"
                                prepend-inner-icon="tabler-building-fortress" label="발급사 선택" item-title="title" item-value="id"
                                single-line />
                        </template>
                    </CreateHalfVCol>
                    <CreateHalfVCol :mdl="3" :mdr="9">
                        <template #name>PG사</template>
                        <template #input>
                            <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.pg_id" :items="pgs"
                                prepend-inner-icon="ph-buildings" label="PG사 선택" item-title="pg_nm" item-value="id"
                                single-line />
                        </template>
                    </CreateHalfVCol>
                    <CreateHalfVCol :mdl="3" :mdr="9">
                        <template #name>유입경로</template>
                        <template #input>
                            <VTextField v-model="props.item.entry_path" prepend-inner-icon="tabler-door-enter"
                                placeholder="유입경로를 입력해주세요" persistent-placeholder :rules="[requiredValidator]" />
                        </template>
                    </CreateHalfVCol>
                    <CreateHalfVCol :mdl="3" :mdr="9">
                        <template #name>입금상태</template>
                        <template #input>
                            <BooleanRadio :radio="props.item.is_deposit" @update:radio="props.item.is_deposit = $event">
                                <template #true>입금</template>
                                <template #false>미입금</template>
                            </BooleanRadio>
                        </template>
                    </CreateHalfVCol>
                </VCardItem>
            </VCard>
        </VCol>
        <!-- 👉 submit -->
    </VRow>
</template>
