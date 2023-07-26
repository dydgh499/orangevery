<script lang="ts" setup>
import { getAllMerchandises } from '@/views/merchandises/useStore'
import { requiredValidator, nullValidator } from '@validators'
import type { Complaint, Merchandise } from '@/views/types'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import BooleanRadio from '@/layouts/utils/BooleanRadio.vue'
import { useStore } from '@/views/services/pay-gateways/useStore'
import { complaint_types } from '@/views/complaints/useStore'

interface Props {
    item: Complaint,
}

const props = defineProps<Props>()
const { pgs } = useStore()

const merchandises = reactive<Merchandise[]>([])
const mcht = ref({ id: null, mcht_name: '가맹점 선택' })

Object.assign(merchandises, await getAllMerchandises())
onMounted(() => {
    props.item.pg_id = props.item.pg_id == 0 ? null : props.item.pg_id
    props.item.is_deposit = Boolean(props.item.is_deposit)
})
watchEffect(() => {
    props.item.mcht_id = mcht.value.id
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
                                <VTextField v-model="props.item.cust_name" prepend-inner-icon="tabler-user"
                                    placeholder="고객명을 입력해주세요" persistent-placeholder :rules="[requiredValidator]" />
                            </template>
                        </CreateHalfVCol>
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name>연락처</template>
                            <template #input>
                                <VTextField v-model="props.item.phone_num" prepend-inner-icon="tabler-device-mobile"
                                    type="number" placeholder="연락처를 입력해주세요" persistent-placeholder
                                    :rules="[requiredValidator]" />
                            </template>
                        </CreateHalfVCol>
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name>수기작성성함</template>
                            <template #input>
                                <VTextField v-model="props.item.hand_cust_name" prepend-inner-icon="tabler-user"
                                    placeholder="수기작성성함을 입력해주세요" persistent-placeholder />
                            </template>
                        </CreateHalfVCol>
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name>수기작성연락처</template>
                            <template #input>
                                <VTextField v-model="props.item.hand_phone_num" prepend-inner-icon="tabler-device-mobile"
                                    type="number" placeholder="수기작성연락처를 입력해주세요" persistent-placeholder />
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
                        <template #name>가맹점 선택</template>
                        <template #input>
                            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="mcht" :items="merchandises"
                                prepend-inner-icon="tabler-building-store" label="가맹점 선택" item-title="mcht_name"
                                item-value="id" single-line :rules=[nullValidator] return-object />
                        </template>
                    </CreateHalfVCol>
                    <CreateHalfVCol :mdl="3" :mdr="9">
                        <template #name>TID</template>
                        <template #input>
                            <VTextField v-model="props.item.tid" prepend-inner-icon="jam-key-f" placeholder="TID를 입력해주세요"
                                persistent-placeholder :rules="[requiredValidator]" />
                        </template>
                    </CreateHalfVCol>
                    <CreateHalfVCol :mdl="3" :mdr="9">
                        <template #name>민원타입</template>
                        <template #input>
                            <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.type" :items="complaint_types"
                                prepend-inner-icon="ic-round-sentiment-dissatisfied" label="민원 타입 선택" item-title="title"
                                item-value="id" single-line :rules="[nullValidator]" />
                        </template>
                    </CreateHalfVCol>
                    <CreateHalfVCol :mdl="3" :mdr="9">
                        <template #name>승인일</template>
                        <template #input>
                            <AppDateTimePicker v-model="props.item.appr_dt" prepend-inner-icon="ic-baseline-calendar-today"
                                label="승인일" :rules="[requiredValidator]" />
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
                            <VTextField v-model="props.item.issuer" prepend-inner-icon="tabler-building-fortress"
                                placeholder="4723124" persistent-placeholder :rules="[requiredValidator]" />
                        </template>
                    </CreateHalfVCol>
                    <CreateHalfVCol :mdl="3" :mdr="9">
                        <template #name>PG사</template>
                        <template #input>
                            <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.pg_id" :items="pgs"
                                prepend-inner-icon="ph-buildings" label="PG사 선택" item-title="pg_name" item-value="id"
                                single-line :rules="[nullValidator]" />
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
                            <BooleanRadio :radio="props.item.is_deposit" @update:radio="props.item.is_deposit = $event"
                                :rules="[nullValidator]">
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
