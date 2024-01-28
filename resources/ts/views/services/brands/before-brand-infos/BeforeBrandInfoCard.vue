<script lang="ts" setup>
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'
import BeforeBrandInfoCardTr from '@/views/services/brands/before-brand-infos/BeforeBrandInfoCardTr.vue'
import type { Brand, BeforeBrandInfo } from '@/views/types'
import { useRequestStore } from '@/views/request'

interface Props {
    item: Brand,
}
const props = defineProps<Props>()
const { setNullRemove } = useRequestStore()
const before_brand_infos = reactive<BeforeBrandInfo[]>(props.item.before_brand_infos || [])

const addNewBeforeBrandInfoCard = () => {
    const before_brand_info = <BeforeBrandInfo>({
        id: 0,
    })
    before_brand_infos.push(before_brand_info)
}
watchEffect(() => {
    setNullRemove(before_brand_infos)
})
</script>
<template>
    <VCardTitle style="margin: 1em 0;">
        <BaseQuestionTooltip :location="'top'" :text="'이전 운영사 정보'" :content="'매출 기간별로 운영사 정보가 적용됩니다.<br>(공급자 정보가 본사정보로 체크되어있는 가맹점에 한해 적용됩니다.)'">
        </BaseQuestionTooltip>
    </VCardTitle>
    <VTable class="text-no-wrap" style=" margin-bottom: 1em;text-align: center;">
        <thead>
            <tr>
                <th scope="col" class='list-square'>No.</th>
                <th scope="col" class='list-square' style="width: 200px;">운영사명</th>
                <th scope="col" class='list-square' style="width: 150px;">대표자명</th>
                <th scope="col" class='list-square' style="width: 150px;">전화번호</th>
                <th scope="col" class='list-square' style="width: 150px;">사업자번호</th>
                <th scope="col" class='list-square' style="width: 200px;">주소</th>
                <th scope="col" class='list-square' style="width: 350px;">적용 기간</th>
                <th scope="col" class='list-square'></th>
            </tr>
        </thead>
        <tbody>
            <BeforeBrandInfoCardTr v-for="(item, index) in before_brand_infos"
                :key="item.id" style="margin-top: 1em;" :item="item" :index="index" />
        </tbody>
        <tfoot v-show="Boolean(props.item.id == 0)">
            <tr>
                <td colspan="4" class="text-center">
                    운영사를 추가하신 후 사용 가능합니다.
                </td>
            </tr>
        </tfoot>
    </VTable>
    <VRow v-show="Boolean(props.item.id != 0)">
        <VCol class="d-flex gap-4">
            <VBtn type="button" style="margin-left: auto;" @click="addNewBeforeBrandInfoCard()">
                이전 운영사 정보 신규추가
                <VIcon end icon="tabler-plus" />
            </VBtn>
        </VCol>
    </VRow>
</template>
<style scoped>
:deep(.v-table__wrapper) {
  block-size: auto !important;
}
</style>
