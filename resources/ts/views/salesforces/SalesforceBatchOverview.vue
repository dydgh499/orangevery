<script lang="ts" setup>
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import BooleanRadio from '@/layouts/utils/BooleanRadio.vue'
import { useStore } from '@/views/services/pay-gateways/useStore'
import { abnormal_trans_limits } from '@/views/merchandises/pay-modules/useStore'
import type { Salesforce } from '@/views/types'
import { axios } from '@axios'
import corp from '@corp'

interface Props {
    item: Salesforce,
}
const props = defineProps<Props>()

const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))
const errorHandler = <any>(inject('$errorHandler'))

const { pgs, cus_filters } = useStore()

const custom = ref(<any>({ id: null, type: 1, name: '사용안함' }))
const sales_fee = ref()
const pg_id = ref()

const abnormal_trans_limit = ref()
const pay_dupe_limit = ref()
const pay_dupe_least = ref()

const pay_disable_s_tm = ref()
const pay_disable_e_tm = ref()
const pay_mid = ref()
const pay_tid = ref()
const api_key = ref()
const sub_key = ref()

const pay_day_limit = ref()
const pay_month_limit = ref()
const pay_year_limit = ref()
const show_pay_view = ref()

const noti_url = ref()
const noti_note = ref()
const noti_status = ref(true)

const post = async (page: string, params: any) => {
    try {
        if (await alert.value.show('정말 일괄적용하시겠습니까?')) {
            const r = await axios.post('/api/v1/manager/salesforces/batch/' + page, params)
            snackbar.value.show('성공하였습니다.', 'success')
        }
    }
    catch (e: any) {
        snackbar.value.show(e.response.data.message, 'error')
        const r = errorHandler(e)
    }
}
const common = computed(() => {
    return {
        'id': props.item.id,
        'level': props.item.level,
        'pg_id': pg_id.value
    }
})
const setFee = () => {
    post('set-fee', {
        ...common.value,
        'sales_fee': parseFloat(sales_fee.value),
    })
}
const setCustomFilter = () => {
    post('set-custom-filter', {
        ...common.value,
        'custom_id': custom.value.id,
    })
}
const setAbnormalTransLimit = () => {
    post('set-abnormal-trans-limit', {
        ...common.value,
        'abnormal_trans_limit': abnormal_trans_limit.value,
    })
}
const setDupPayCountValidation = () => {
    post('set-dupe-pay-count-validation', {
        ...common.value,
        'pay_dupe_limit': pay_dupe_limit.value,
    })
}
const setDupPayLeastValidation = () => {
    post('set-dupe-pay-least-validation', {
        ...common.value,
        'pay_dupe_least': pay_dupe_least.value,
    })

}
const setPayLimit = (type: string) => {
    post('set-pay-limit', {
        ...common.value,
        'pay_day_limit': pay_day_limit.value,
        'pay_month_limit': pay_month_limit.value,
        'pay_year_limit': pay_year_limit.value,
        'type': type,
    })
}
const setForbiddenPayTime = () => {
    post('set-pay-disable-time', {
        ...common.value,
        'pay_disable_s_tm': pay_disable_s_tm.value,
        'pay_disable_e_tm': pay_disable_e_tm.value,
    })
}
const setShowPayView = () => {
    post('set-show-pay-view', {
        ...common.value,
        'show_pay_view': show_pay_view.value,
    })
}
//
const setMid = () => {
    post('set-mid', {
        ...common.value,
        'mid': pay_mid.value,
    })
}
const setTid = () => {
    post('set-tid', {
        ...common.value,
        'tid': pay_tid.value,
    })
}
const setApiKey = () => {
    post('set-api-key', {
        ...common.value,
        'api_key': api_key.value,
    })
}
const setSubKey = () => {
    post('set-sub-key', {
        ...common.value,
        'sub_key': sub_key.value,
    })
}
const setNotiUrl = () => {
    post('set-noti-url', {
        ...common.value,
        'noti_status': noti_status.value,
        'send_url': noti_url.value,
        'note': noti_note.value,
    })
}
</script>
<template>
    <VCardTitle style="margin: 1em 0;">
        <BaseQuestionTooltip :location="'top'" :text="'가맹점 일괄적용'" :content="'해당 영업점이 포함되어있는 가맹점에 모두 적용됩니다.'">
        </BaseQuestionTooltip>
    </VCardTitle>
    <div v-if="props.item.id != 0" style="width: 100%;">
        <CreateHalfVCol :mdl="3" :mdr="9">
            <template #name>수수료율</template>
            <template #input>
                <div class="batch-container">
                    <VTextField v-model="sales_fee" type="number" suffix="%" />
                    <VBtn style='margin-left: 0.5em;' variant="tonal" @click="setFee()">
                        즉시적용
                        <VIcon end icon="tabler-direction-sign" />
                    </VBtn>
                </div>
            </template>
        </CreateHalfVCol>
        <CreateHalfVCol :mdl="3" :mdr="9">
            <template #name>커스텀 필터</template>
            <template #input>
                <div class="batch-container">
                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="custom"
                        :items="[{ id: null, type: 1, name: '사용안함' }].concat(cus_filters)"
                        prepend-inner-icon="tabler:folder-question" label="커스텀 필터" item-title="name" item-value="id"
                        persistent-hint return-object />
                    <VBtn style='margin-left: 0.5em;' variant="tonal" @click="setCustomFilter()">
                        즉시적용
                        <VIcon end icon="tabler-direction-sign" />
                    </VBtn>
                </div>
            </template>
        </CreateHalfVCol>
    </div>
    <div v-else style="width: 100%; text-align: center;">
        <CreateHalfVCol :mdl="0" :mdr="12">
            <template #name></template>
            <template #input>
                영업점을 추가하신 후 사용 가능합니다.
            </template>
        </CreateHalfVCol>
    </div>
    <VCardTitle style="margin: 1em 0;">
        <BaseQuestionTooltip :location="'top'" :text="'결제모듈 일괄적용'" :content="'해당 영업점이 포함되어있는 가맹점의 모든 결제모듈에 모두 적용됩니다.'">
        </BaseQuestionTooltip>
    </VCardTitle>
    <div v-if="props.item.id != 0" style="width: 100%;">
        <CreateHalfVCol :mdl="3" :mdr="9">
            <template #name>
                <BaseQuestionTooltip :location="'top'" :text="'PG사 필터'" :content="'해당 PG사가 적용되어있는 결제모듈에 한해 적용됩니다.'">
                </BaseQuestionTooltip>
            </template>
            <template #input>
                <VSelect :menu-props="{ maxHeight: 400 }" v-model="pg_id" :items="[{ id: null, pg_name: '전체' }].concat(pgs)"
                    prepend-inner-icon="ph-buildings" label="PG사 선택" item-title="pg_name" item-value="id" single-line />
            </template>
        </CreateHalfVCol>
        <CreateHalfVCol :mdl="3" :mdr="9">
            <template #name>이상거래 한도</template>
            <template #input>
                <div class="batch-container">
                    <VSelect v-model="abnormal_trans_limit" :items="abnormal_trans_limits"
                        prepend-inner-icon="jam-triangle-danger" label="이상거래 한도설정" item-title="title" item-value="id" />
                    <VBtn style='margin-left: 0.5em;' variant="tonal" @click="setAbnormalTransLimit()">
                        즉시적용
                        <VIcon end icon="tabler-direction-sign" />
                    </VBtn>
                </div>
            </template>
        </CreateHalfVCol>
        <CreateHalfVCol :mdl="3" :mdr="9">
            <template #name>중복거래 하한금</template>
            <template #input>
                <div class="batch-container">
                    <VTextField type="number" v-model="pay_dupe_least" prepend-inner-icon="tabler-currency-won" suffix="만원" />
                    <VBtn style='margin-left: 0.5em;' variant="tonal" @click="setDupPayLeastValidation()">
                        즉시적용
                        <VIcon end icon="tabler-direction-sign" />
                    </VBtn>
                </div>
            </template>
        </CreateHalfVCol>
        <CreateHalfVCol :mdl="3" :mdr="9" v-if="corp.pv_options.paid.use_dup_pay_validation">
            <template #name>중복결제 허용회수</template>
            <template #input>
                <div class="batch-container">
                    <VTextField v-model="pay_dupe_limit" label="중복결제 허용회수" type="number" suffix="회 허용" />
                    <VBtn style='margin-left: 0.5em;' variant="tonal" @click="setDupPayCountValidation()">
                        즉시적용
                        <VIcon end icon="tabler-direction-sign" />
                    </VBtn>
                </div>
            </template>
        </CreateHalfVCol>
        <CreateHalfVCol :mdl="3" :mdr="9" v-if="corp.pv_options.paid.use_pay_limit">
            <template #name>일 결제 한도</template>
            <template #input>
                <div class="batch-container">
                    <VTextField prepend-inner-icon="tabler-currency-won" v-model="pay_day_limit" type="number"
                        suffix="만원" />
                    <VBtn style='margin-left: 0.5em;' variant="tonal" @click="setPayLimit('day')">
                        즉시적용
                        <VIcon end icon="tabler-direction-sign" />
                    </VBtn>
                </div>

            </template>
        </CreateHalfVCol>
        <CreateHalfVCol :mdl="3" :mdr="9" v-if="corp.pv_options.paid.use_pay_limit">
            <template #name>월 결제 한도</template>
            <template #input>
                <div class="batch-container">
                    <VTextField prepend-inner-icon="tabler-currency-won" v-model="pay_month_limit" type="number"
                        suffix="만원" />
                    <VBtn style='margin-left: 0.5em;' variant="tonal" @click="setPayLimit('month')">
                        즉시적용
                        <VIcon end icon="tabler-direction-sign" />
                    </VBtn>
                </div>

            </template>
        </CreateHalfVCol>
        <CreateHalfVCol :mdl="3" :mdr="9" v-if="corp.pv_options.paid.use_pay_limit">
            <template #name>연 결제 한도</template>
            <template #input>
                <div class="batch-container">
                    <VTextField prepend-inner-icon="tabler-currency-won" v-model="pay_year_limit" type="number"
                        suffix="만원" />
                    <VBtn style='margin-left: 0.5em;' variant="tonal" @click="setPayLimit('year')">
                        즉시적용
                        <VIcon end icon="tabler-direction-sign" />
                    </VBtn>
                </div>

            </template>
        </CreateHalfVCol>
        <CreateHalfVCol :mdl="3" :mdr="9" v-if="corp.pv_options.paid.use_forb_pay_time">
            <template #name>결제금지 시간</template>
            <template #input>
                <div class="batch-container">
                    <VTextField v-model="pay_disable_s_tm" type="time" />
                    <span class="text-center mx-auto">~</span>
                    <VTextField v-model="pay_disable_e_tm" type="time" />
                    <VBtn style='margin-left: 0.5em;' variant="tonal" @click="setForbiddenPayTime()">
                        즉시적용
                        <VIcon end icon="tabler-direction-sign" />
                    </VBtn>
                </div>
            </template>
        </CreateHalfVCol>
        <CreateHalfVCol :mdl="3" :mdr="9">
            <template #name>결제창 노출여부</template>
            <template #input>
                <div class="batch-container">
                    <BooleanRadio :radio="Boolean(show_pay_view)" @update:radio="show_pay_view = $event">
                        <template #true>노출</template>
                        <template #false>숨김</template>
                    </BooleanRadio>
                    <VBtn style='margin-left: 0.5em;' variant="tonal" @click="setShowPayView()">
                        즉시적용
                        <VIcon end icon="tabler-direction-sign" />
                    </VBtn>
                </div>
            </template>
        </CreateHalfVCol>
        <CreateHalfVCol :mdl="3" :mdr="9" v-if="corp.pv_options.paid.use_mid_batch">
            <template #name>MID</template>
            <template #input>
                <div class="batch-container">
                    <VTextField v-model="pay_mid" label="MID" type="text" />
                    <VBtn style='margin-left: 0.5em;' variant="tonal" @click="setMid()">
                        즉시적용
                        <VIcon end icon="tabler-direction-sign" />
                    </VBtn>
                </div>
            </template>
        </CreateHalfVCol>
        <CreateHalfVCol :mdl="3" :mdr="9" v-if="corp.pv_options.paid.use_tid_batch">
            <template #name>TID</template>
            <template #input>
                <div class="batch-container">
                    <VTextField v-model="pay_tid" label="TID" type="text" />
                    <VBtn style='margin-left: 0.5em;' variant="tonal" @click="setTid()">
                        즉시적용
                        <VIcon end icon="tabler-direction-sign" />
                    </VBtn>
                </div>
            </template>
        </CreateHalfVCol>
        <CreateHalfVCol :mdl="3" :mdr="9" v-if="corp.pv_options.paid.use_api_key_batch">
            <template #name>API KEY(license)</template>
            <template #input>
                <div class="batch-container">
                    <VTextField v-model="api_key" label="API KEY" type="text" />
                    <VBtn style='margin-left: 0.5em;' variant="tonal" @click="setApiKey()">
                        즉시적용
                        <VIcon end icon="tabler-direction-sign" />
                    </VBtn>
                </div>
            </template>
        </CreateHalfVCol>
        <CreateHalfVCol :mdl="3" :mdr="9" v-if="corp.pv_options.paid.use_sub_key_batch">
            <template #name>SUB KEY(iv)</template>
            <template #input>
                <div class="batch-container">
                    <VTextField v-model="sub_key" label="SUB KEY" type="text" />
                    <VBtn style='margin-left: 0.5em;' variant="tonal" @click="setSubKey()">
                        즉시적용
                        <VIcon end icon="tabler-direction-sign" />
                    </VBtn>
                </div>
            </template>
        </CreateHalfVCol>
    </div>
    <div v-else style="width: 100%; text-align: center;">
        <CreateHalfVCol :mdl="0" :mdr="12">
            <template #name></template>
            <template #input>
                영업점을 추가하신 후 사용 가능합니다.
            </template>
        </CreateHalfVCol>
    </div>
    <VCardTitle style="margin: 1em 0;">
        <BaseQuestionTooltip :location="'top'" :text="'노티 URL 일괄적용'"
            :content="'해당 영업점이 포함되어있는 가맹점의 모든 노티 URL이 추가됩니다.<br>(같은 노티 URL의 중복등록은 불가능합니다.)'">
        </BaseQuestionTooltip>
    </VCardTitle>
    <div v-if="props.item.id != 0" style="width: 100%;">
        <CreateHalfVCol :mdl="3" :mdr="9">
            <template #name>노티 URL</template>
            <template #input>
                <div class="batch-container">
                    <VTextField v-model="noti_url" type="text" placeholder="https://www.naver.com" />
                </div>
            </template>
        </CreateHalfVCol>
        <CreateHalfVCol :mdl="3" :mdr="9">
            <template #name>노티 사용 유무</template>
            <template #input>
                <div class="batch-container">
                    <BooleanRadio :radio="Boolean(noti_status)" @update:radio="noti_status = $event">
                        <template #true>사용</template>
                        <template #false>미사용</template>
                    </BooleanRadio>
                </div>
            </template>
        </CreateHalfVCol>
        <VRow>
            <VCol>
                <VTextarea v-model="noti_note" counter label="메모사항" prepend-inner-icon="twemoji-spiral-notepad" />
            </VCol>
        </VRow>
        <div style="float: inline-end;">
            <VBtn variant="tonal" @click="setNotiUrl()">
                즉시적용
                <VIcon end icon="tabler-direction-sign" />
            </VBtn>
        </div>
    </div>
    <div v-else style="width: 100%; text-align: center;">
        <CreateHalfVCol :mdl="0" :mdr="12">
            <template #name></template>
            <template #input>
                영업점을 추가하신 후 사용 가능합니다.
            </template>
        </CreateHalfVCol>
    </div>
</template>
<style>
.batch-container {
  display: flex;
  align-items: center;
}
</style>
