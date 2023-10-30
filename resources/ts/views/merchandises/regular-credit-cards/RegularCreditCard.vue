<script lang="ts" setup>
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'
import RegularCreditCardTr from '@/views/merchandises/regular-credit-cards/RegularCreditCardTr.vue'
import type { Merchandise, RegularCreditCard } from '@/views/types'
import { useRequestStore } from '@/views/request'

interface Props {
    item: Merchandise,
}
const props = defineProps<Props>()
const { setNullRemove } = useRequestStore()
const regular_credit_cards = reactive<RegularCreditCard[]>(props.item.regular_credit_cards || [])
const new_regular_credit_cards = reactive<RegularCreditCard[]>([])
const addNewRegularCreditCard = () => {
    const regular_credit_card = <RegularCreditCard>({
        id: 0,
        mcht_id: props.item.id,
        card_num: '',
        nick_name: "",
    })
    new_regular_credit_cards.push(regular_credit_card)
}
watchEffect(() => {
    setNullRemove(regular_credit_cards)
    setNullRemove(new_regular_credit_cards)
})
</script>
<template>
    <VCardTitle style="margin: 1em 0;">
        <BaseQuestionTooltip :location="'top'" :text="'단골고객 카드정보 세팅'" :content="'수기결제 시 등록된 카드번호로만 결제가 가능합니다.'">
        </BaseQuestionTooltip>
    </VCardTitle>
    <VTable style="width: 100%;margin-bottom: 1em;text-align: center;">
        <thead>
            <tr>
                <th scope="col" style="text-align: center;">No.</th>
                <th scope="col" style="text-align: center;">별칭</th>
                <th scope="col" style="text-align: center;">카드번호</th>
                <th scope="col" style="text-align: center;"></th>
            </tr>
        </thead>
        <tbody>
            <RegularCreditCardTr v-for="(item, index) in regular_credit_cards"
                :key="item.id" style="margin-top: 1em;" :item="item" :index="index" />
            <RegularCreditCardTr v-for="(item, index) in new_regular_credit_cards" :key="index"
                style="margin-top: 1em;" :item="item" :index="(index+regular_credit_cards.length)" />
        </tbody>
        <tfoot v-show="Boolean(props.item.id == 0)">
            <tr>
                <td colspan="4" class="text-center">
                    가맹점을 추가하신 후 사용 가능합니다.
                </td>
            </tr>
        </tfoot>
    </VTable>
    <VRow v-show="Boolean(props.item.id != 0)">
        <VCol class="d-flex gap-4">
            <VBtn type="button" style="margin-left: auto;" @click="addNewRegularCreditCard()">
                카드정보 신규추가
                <VIcon end icon="tabler-plus" />
            </VBtn>
        </VCol>
    </VRow>
</template>
