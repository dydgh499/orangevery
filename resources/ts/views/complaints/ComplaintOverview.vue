<script lang="ts" setup>
import { useSalesFilterStore } from '@/views/salesforces/useStore'
import type { Complaint } from '@/views/types'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import BooleanRadio from '@/layouts/utils/BooleanRadio.vue'
import { useStore } from '@/views/services/pay-gateways/useStore'
import { complaint_types, complaint_statuses } from '@/views/complaints/useStore'
import { getUserLevel } from '@axios'

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
                    <VRow class="pt-5">
                        <CreateHalfVCol :mdl="3" :mdr="9" v-if="getUserLevel() >= 35">
                            <template #name>고객명</template>
                            <template #input>
                                <VTextField v-model="props.item.cust_name" prepend-inner-icon="tabler-user"
                                    placeholder="고객명을 입력해주세요" persistent-placeholder />
                            </template>
                        </CreateHalfVCol>
                        <CreateHalfVCol :mdl="3" :mdr="9" v-else>
                            <template #name><span class="font-weight-bold">고객명</span></template>
                            <template #input>
                                {{ props.item.cust_name }}
                            </template>
                        </CreateHalfVCol>

                        <CreateHalfVCol :mdl="3" :mdr="9" v-if="getUserLevel() >= 35">
                            <template #name>연락처</template>
                            <template #input>
                                <VTextField v-model="props.item.phone_num" prepend-inner-icon="tabler-device-mobile"
                                    type="number" placeholder="연락처를 입력해주세요" persistent-placeholder />
                            </template>
                        </CreateHalfVCol>
                        <CreateHalfVCol :mdl="3" :mdr="9" v-else>
                            <template #name><span class="font-weight-bold">연락처</span></template>
                            <template #input>
                                {{ props.item.phone_num }}
                            </template>
                        </CreateHalfVCol>

                        <CreateHalfVCol :mdl="3" :mdr="9" v-if="getUserLevel() >= 35">
                            <template #name>수기작성성함</template>
                            <template #input>
                                <VTextField v-model="props.item.hand_cust_name" prepend-inner-icon="tabler-user"
                                    placeholder="수기작성성함을 입력해주세요" persistent-placeholder/>
                            </template>
                        </CreateHalfVCol>
                        <CreateHalfVCol :mdl="3" :mdr="9" v-else>
                            <template #name><span class="font-weight-bold">수기작성성함</span></template>
                            <template #input>
                                {{ props.item.hand_cust_name }}
                            </template>
                        </CreateHalfVCol>
                        <CreateHalfVCol :mdl="3" :mdr="9" v-if="getUserLevel() >= 35">
                            <template #name>수기작성연락처</template>
                            <template #input>
                                <VTextField v-model="props.item.hand_phone_num" prepend-inner-icon="tabler-device-mobile"
                                    type="number" placeholder="수기작성연락처를 입력해주세요" persistent-placeholder v-if="getUserLevel() >= 35" />
                            </template>
                        </CreateHalfVCol>
                        <CreateHalfVCol :mdl="3" :mdr="9" v-else>
                            <template #name><span class="font-weight-bold">수기작성연락처</span></template>
                            <template #input>
                                {{ props.item.hand_phone_num }}
                            </template>
                        </CreateHalfVCol>
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
                    <CreateHalfVCol :mdl="3" :mdr="9" v-if="getUserLevel() >= 35">
                        <template #name>가맹점 선택</template>
                        <template #input>
                            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="props.item.mcht_id" :items="mchts"
                                prepend-inner-icon="tabler-building-store" label="가맹점 선택" item-title="mcht_name"
                                item-value="id" single-line />
                        </template>
                    </CreateHalfVCol>
                    <CreateHalfVCol :mdl="3" :mdr="9" v-else>
                        <template #name><span class="font-weight-bold">가맹점</span></template>
                        <template #input>
                            {{ mchts.find(obj => obj.id === props.item.mcht_id)?.mcht_name }}
                        </template>
                    </CreateHalfVCol>

                    <CreateHalfVCol :mdl="3" :mdr="9" v-if="getUserLevel() >= 35">
                        <template #name>거래번호</template>
                        <template #input>
                            <VTextField v-model="props.item.tid" prepend-inner-icon="jam-key-f" placeholder="거래번호를 입력해주세요"
                                persistent-placeholder />
                        </template>
                    </CreateHalfVCol>                    
                    <CreateHalfVCol :mdl="3" :mdr="9" v-else>
                        <template #name><span class="font-weight-bold">거래번호</span></template>
                        <template #input>
                            {{ props.item.tid }}
                        </template>
                    </CreateHalfVCol>

                    <CreateHalfVCol :mdl="3" :mdr="9" v-if="getUserLevel() >= 35">
                        <template #name>민원타입</template>
                        <template #input>
                            <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.type" :items="complaint_types"
                                prepend-inner-icon="ic-round-sentiment-dissatisfied" label="민원 타입 선택" item-title="title"
                                item-value="id" single-line />
                        </template>
                    </CreateHalfVCol>
                    <CreateHalfVCol :mdl="3" :mdr="9" v-else>
                        <template #name><span class="font-weight-bold">민원타입</span></template>
                        <template #input>
                            {{ complaint_types.find(obj => obj.id === props.item.type)?.title }}
                        </template>
                    </CreateHalfVCol>

                    <CreateHalfVCol :mdl="3" :mdr="9" v-if="getUserLevel() >= 35">
                        <template #name>승인일</template>
                        <template #input>
                            <VTextField type="date" v-model="props.item.appr_dt" prepend-inner-icon="ic-baseline-calendar-today"
                                label="승인일" />
                        </template>
                    </CreateHalfVCol>              
                    <CreateHalfVCol :mdl="3" :mdr="9" v-else>
                        <template #name><span class="font-weight-bold">승인일</span></template>
                        <template #input>
                            {{ props.item.appr_dt }}
                        </template>
                    </CreateHalfVCol>

                    <CreateHalfVCol :mdl="3" :mdr="9" v-if="getUserLevel() >= 35">
                        <template #name>승인번호</template>
                        <template #input>
                            <VTextField v-model="props.item.appr_num" prepend-inner-icon="tabler-receipt-2"
                                placeholder="4723124" />
                        </template>
                    </CreateHalfVCol>              
                    <CreateHalfVCol :mdl="3" :mdr="9" v-else>
                        <template #name><span class="font-weight-bold">승인번호</span></template>
                        <template #input>
                            {{ props.item.appr_num }}
                        </template>
                    </CreateHalfVCol>

                    <CreateHalfVCol :mdl="3" :mdr="9" v-if="getUserLevel() >= 35">
                        <template #name>발급사</template>
                        <template #input>
                            <VTextField v-model="props.item.issuer" prepend-inner-icon="tabler-building-fortress"
                                placeholder="4723124" persistent-placeholder />
                        </template>
                    </CreateHalfVCol>              
                    <CreateHalfVCol :mdl="3" :mdr="9" v-else>
                        <template #name><span class="font-weight-bold">발급사</span></template>
                        <template #input>
                            {{ props.item.issuer }}
                        </template>
                    </CreateHalfVCol>

                    <CreateHalfVCol :mdl="3" :mdr="9" v-if="getUserLevel() >= 35">
                        <template #name>PG사</template>
                        <template #input>
                            <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.pg_id" :items="pgs"
                                prepend-inner-icon="ph-buildings" label="PG사 선택" item-title="pg_name" item-value="id"
                                single-line  />
                        </template>
                    </CreateHalfVCol>
                    <CreateHalfVCol :mdl="3" :mdr="9" v-if="getUserLevel() >= 35">
                        <template #name>유입경로</template>
                        <template #input>
                            <VTextField v-model="props.item.entry_path" prepend-inner-icon="tabler-door-enter"
                                placeholder="유입경로를 입력해주세요" persistent-placeholder />
                        </template>
                    </CreateHalfVCol>
                    <CreateHalfVCol :mdl="3" :mdr="9" v-else>
                        <template #name><span class="font-weight-bold">유입경로</span></template>
                        <template #input>
                            {{ props.item.entry_path }}
                        </template>
                    </CreateHalfVCol>

                    <CreateHalfVCol :mdl="3" :mdr="9" v-if="getUserLevel() >= 35">
                        <template #name>민원상태</template>
                        <template #input>
                            <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.complaint_status" :items="complaint_statuses"
                             label="민원 상태 선택" item-title="title"
                                item-value="id" single-line />
                        </template>
                    </CreateHalfVCol>              
                    <CreateHalfVCol :mdl="3" :mdr="9" v-else>
                        <template #name><span class="font-weight-bold">민원상태</span></template>
                        <template #input>
                            {{ complaint_statuses.find(obj => obj.id === props.item.complaint_status)?.title }}
                        </template>
                    </CreateHalfVCol>

                    <CreateHalfVCol :mdl="3" :mdr="9" v-if="getUserLevel() >= 35">
                        <template #name>입금상태</template>
                        <template #input>
                            <BooleanRadio :radio="props.item.is_deposit" @update:radio="props.item.is_deposit = $event">
                                <template #true>입금</template>
                                <template #false>미입금</template>
                            </BooleanRadio>
                        </template>
                    </CreateHalfVCol>              
                    <CreateHalfVCol :mdl="3" :mdr="9" v-else>
                        <template #name><span class="font-weight-bold">입금상태</span></template>
                        <template #input>
                            {{ props.item.is_deposit ? "입금" : "미입금" }}
                        </template>
                    </CreateHalfVCol>
                </VCardItem>
            </VCard>
        </VCol>
    </VRow>
</template>
