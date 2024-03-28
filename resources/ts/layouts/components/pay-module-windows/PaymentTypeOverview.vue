<script lang="ts" setup>
import { useStore } from '@/views/services/pay-gateways/useStore'
import type { PayModule } from '@/views/types'
import { 
    module_types, installments
 } from '@/views/merchandises/pay-modules/useStore'
import { useSalesFilterStore } from '@/views/salesforces/useStore'
import BooleanRadio from '@/layouts/utils/BooleanRadio.vue'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import { requiredValidatorV2 } from '@validators'
import { getUserLevel } from '@axios'
interface Props {
    item: PayModule,
    able_mcht_chanage: boolean,
}

const props = defineProps<Props>()
const snackbar = <any>(inject('snackbar'))

const { mchts } = useSalesFilterStore()
const { pgs, pss, settle_types, psFilter, setFee } = useStore()


const onModuleTypeChange = () => {
    props.item.note = module_types.find(obj => obj.id === props.item.module_type)?.title || ''
}
const filterPgs = computed(() => {
    const filter = pss.filter(item => { return item.pg_id == props.item.pg_id })
    props.item.ps_id = psFilter(filter, props.item.ps_id)
    return filter
})

</script>
<template>
    <VCardItem>
        <VRow v-if="props.able_mcht_chanage && getUserLevel() >= 35">
            <CreateHalfVCol :mdl="5" :mdr="7">
                <template #name>소유 가맹점</template>
                <template #input>
                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="props.item.mcht_id" :items="mchts"
                        prepend-inner-icon="tabler-building-store" label="가맹점 선택" item-title="mcht_name" item-value="id"
                        single-line :rules="[requiredValidatorV2(props.item.mcht_id, '가맹점')]" :eager="true" />
                </template>
            </CreateHalfVCol>
        </VRow>
        <VRow v-else-if="props.able_mcht_chanage">
            <CreateHalfVCol :mdl="5" :mdr="7">
                <template #name><span class="font-weight-bold">소유 가맹점</span></template>
                <template #input>
                    {{ mchts.find(obj => obj.id === props.item.mcht_id)?.mcht_name }}
                </template>
            </CreateHalfVCol>
        </VRow>
        <!-- 👉 결제 모듈 타입 -->
        <VRow v-if="getUserLevel() >= 35">
            <CreateHalfVCol :mdl="5" :mdr="7">
                <template #name>결제모듈 타입</template>
                <template #input>
                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.module_type"
                        @update:modelValue="onModuleTypeChange" :items="module_types"
                        prepend-inner-icon="ic-outline-send-to-mobile" label="결제모듈 선택" item-title="title"
                        item-value="id" single-line :rules="[requiredValidatorV2(props.item.module_type, '결제모듈 타입')]" />
                </template>
            </CreateHalfVCol>
        </VRow>
        <VRow v-else>
            <CreateHalfVCol :mdl="5" :mdr="7">
                <template #name><span class="font-weight-bold">결제모듈 타입</span></template>
                <template #input>
                    {{ module_types.find(obj => obj.id === props.item.module_type)?.title }}
                </template>
            </CreateHalfVCol>
        </VRow>
        <!-- 👉 수기결제 타입(구인증, 비인증) -->
        <VRow v-show="props.item.module_type == 1 || props.item.module_type == 5" v-if="getUserLevel() >= 35">
            <CreateHalfVCol :mdl="5" :mdr="7">
                <template #name>수기결제 타입</template>
                <template #input>
                    <BooleanRadio :radio="props.item.is_old_auth" @update:radio="props.item.is_old_auth = $event">
                        <template #true>구인증</template>
                        <template #false>비인증</template>
                    </BooleanRadio>
                </template>
            </CreateHalfVCol>
        </VRow>
        <VRow v-else>
            <CreateHalfVCol :mdl="5" :mdr="7">
                <template #name><span class="font-weight-bold">수기결제 타입</span></template>
                <template #input>
                    {{ props.item.is_old_auth ? "구인증" : "비인증" }}
                </template>
            </CreateHalfVCol>
        </VRow>
        <!-- 👉 할부한도 (수기,인증,간편,실시간,비인증) -->
        <VRow v-if="getUserLevel() >= 35">
            <CreateHalfVCol :mdl="5" :mdr="7">
                <template #name>할부한도</template>
                <template #input>
                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.installment" :items="installments"
                        prepend-inneer-icon="fluent-credit-card-clock-20-regular" label="할부한도 선택" item-title="title"
                        item-value="id" single-line />
                </template>
            </CreateHalfVCol>
        </VRow>
        <VRow v-else>
            <CreateHalfVCol :mdl="5" :mdr="7">
                <template #name><span class="font-weight-bold">할부한도</span></template>
                <template #input>
                    {{ installments.find(obj => obj.id === props.item.installment)?.title }}
                </template>
            </CreateHalfVCol>
        </VRow>
        <!-- 👉 PG사 -->
        <VRow v-if="getUserLevel() >= 35">
            <CreateHalfVCol :mdl="5" :mdr="7">
                <template #name>
                    <span>PG사</span>
                </template>
                <template #input>
                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.pg_id" :items="pgs"
                        prepend-inner-icon="ph-buildings" label="PG사 선택" item-title="pg_name" item-value="id"
                        single-line :rules="[requiredValidatorV2(props.item.pg_id, 'PG사')]" />
                </template>
            </CreateHalfVCol>
        </VRow>
        <!-- 👉 PG 구간 -->
        <VRow v-if="getUserLevel() >= 35">
            <CreateHalfVCol :mdl="5" :mdr="7">
                <template #name>구간</template>
                <template #input>
                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.ps_id" :items="filterPgs"
                        prepend-inner-icon="mdi-vector-intersection" label="구간 선택" item-title="name" item-value="id"
                        :hint="`${setFee(pss, props.item.ps_id)}`" persistent-hint single-line
                        :rules="[requiredValidatorV2(props.item.ps_id, '구간')]" />
                </template>
            </CreateHalfVCol>
        </VRow>
        <VRow v-if="getUserLevel() >= 35">
            <CreateHalfVCol :mdl="5" :mdr="7">
                <template #name>정산일</template>
                <template #input>
                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.settle_type" :items="settle_types"
                        prepend-inner-icon="ic-outline-send-to-mobile" label="정산일 선택" item-title="name" item-value="id"
                        :rules="[requiredValidatorV2(props.item.settle_type, '정산일')]" />
                </template>
            </CreateHalfVCol>
        </VRow>
        <VRow v-else>
            <CreateHalfVCol :mdl="5" :mdr="7">
                <template #name><span class="font-weight-bold">정산일</span></template>
                <template #input>
                    {{ settle_types.find(obj => obj.id === props.item.settle_type)?.name }}
                </template>
            </CreateHalfVCol>
        </VRow>
        <VRow v-if="getUserLevel() >= 35">
            <CreateHalfVCol :mdl="5" :mdr="7">
                <template #name>이체 수수료</template>
                <template #input>
                    <VTextField v-model="props.item.settle_fee" type="number" suffix="₩"
                        :rules="[requiredValidatorV2(props.item.settle_fee, '이체 수수료')]" />
                </template>
            </CreateHalfVCol>
        </VRow>
        <VRow v-else>
            <CreateHalfVCol :mdl="5" :mdr="7">
                <template #name><span class="font-weight-bold">이체 수수료</span></template>
                <template #input>
                    {{ props.item.settle_fee }} ₩
                </template>
            </CreateHalfVCol>
        </VRow>
        <!-- 👉 비고 -->
        <VRow v-if="getUserLevel() >= 35">
            <VCol>
                <VTextarea v-model="props.item.note" counter label="결제모듈 별칭" placeholder='결제모듈 명칭을 적어주세요.😀'
                    prepend-inner-icon="twemoji-spiral-notepad" auto-grow />
            </VCol>
        </VRow>
    </VCardItem>
</template>
