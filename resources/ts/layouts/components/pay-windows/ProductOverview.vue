<script setup lang="ts">
import { installments } from '@/views/merchandises/pay-modules/useStore';
import { BasePayInfo, OptionGroup, Options, PayModule, ProductOptionGroup } from '@/views/types';
import { requiredValidatorV2 } from '@validators';

interface Props {
    common_info: BasePayInfo,
    pay_module: PayModule, 
}

const props = defineProps<Props>()
const params = <any>(inject('params'))
const option_groups = ref(<OptionGroup[]>([]))
const option_selects = ref(<any>({}))

const updateParam = (product_id: number, group_id: number, index: number) => {
    const group = params.value.product_option_groups.find(obj => obj.id === group_id)
    if (group) {
        const option = group.product_options.find(obj => obj.id === product_id)
        option_groups.value.push({
            group_id: group_id,
            option_id: option.id,
            option_name: option.option_name,
            option_price: option.option_price,
            count: 1
        })
        params.value[`${group_id}-${index}`] = product_id
    }
}

const setCount = (group: OptionGroup, type: string) => {
    if (type === '+')
        group.count += 1
    else if (group.count > 1)
        group.count -= 1
}

const setDelete = (index: number) => {
    for (let i = option_groups.value.length - 1; i >= 0; i--) {
        if (i === index) {
            option_groups.value.splice(i, 1);
        }
    }
}

const isSelectDisabled = computed(() => {
    return (option_group: ProductOptionGroup): boolean => {
        if (option_group.is_able_duplicate) 
            return false
        else
            return !!option_groups.value.find(obj => obj.group_id === option_group.id)
    }
})

const filterInstallment = computed(() => {
    return installments.filter((obj: Options) => { return obj.id <= (props.pay_module.installment || 0) })
})

watchEffect(() => {
    const option_amount = option_groups.value.reduce((total, item) => total + (item.option_price * item.count), 0);
    props.common_info.amount = params.value.amount + option_amount
    props.common_info.option_groups = JSON.stringify(option_groups.value.map(obj => ({
        option_id: obj.option_id,
        option_name: obj.option_name,
        option_price: obj.option_price,
        count: obj.count,
    })))
})
</script>
<template>
    <VRow>
        <VCol md="6" cols="12">
            <VImg rounded :src="params.item_img" class="product-img" :style="'width:100%; height: 20em;'"/>
        </VCol>
        <VCol md="6" cols="12" :class="`product-sub-info-wrapper-${$vuetify.display.smAndDown ? 'mobile' : 'pc'}`">
            <div class="product-sub-info">
                <h3>{{ params.item_name }}</h3>
                <VDivider style="margin-top: 1em;" />
            </div>
            <div v-if="params.product_option_groups.length">
                <div 
                    class="product-sub-info" 
                    v-for="(option_group, index) in params.product_option_groups"
                >
                    <VRow no-gutters style="align-items: center;">
                        <VCol md="6" cols="12">
                            <h4>{{ option_group.group_name }}</h4>
                        </VCol>
                        <VCol md="6" cols="12">
                            <VSelect :model-value="option_selects[`${option_group.id}-${index}`]"
                                :menu-props="{ maxHeight: 400 }"
                                :items="[{ option_name: option_group.group_name + '선택', id: null }].concat(option_group.product_options)"
                                density="compact"
                                @update:modelValue="(value) => updateParam(value, option_group.id, index)"
                                :disabled="isSelectDisabled(option_group)"
                                item-title="option_name" item-value="id" />
                            <VTooltip activator="parent" location="top" transition="scale-transition" v-if="option_group.is_able_duplicate">
                                중복선택이 가능합니다.
                            </VTooltip>
                        </VCol>
                    </VRow>
                    <VDivider v-if="params.product_option_groups.length -1 === index" style="margin-top: 1em;" />
                </div>
            </div>

            <VCard v-for="(group, index) in option_groups" class="product-sub-info" :key="index">
                <VCol cols="12" md="12">
                    <VRow>
                        <VCol cols="5" md="5">
                            <span>{{ group.option_name }}</span>
                        </VCol>
                        <VCol cols="4" md="4">
                            <template v-if="params.product_option_groups.find(obj => obj.id === group.group_id).is_able_count">
                                <VBtn icon="tabler:minus" size="24" @click="setCount(group, '-')" />
                                <span style="margin: 0 0.5em;">{{ group.count }}</span>
                                <VBtn icon="tabler:plus" size="24" @click="setCount(group, '+')" />
                            </template>
                        </VCol>
                        <VCol cols="3" md="3" style="padding: 12px 0;">
                            <span>{{ group.option_price.toLocaleString() }}원</span>
                            <VBtn icon="tabler-x" size="20" variant="plain" color="default"
                                @click="setDelete(index)" style="position: absolute;right: 0.25em;" />
                        </VCol>
                    </VRow>
                </VCol>
            </VCard>
            <VDivider v-if="option_groups.length" />
        </VCol>
    </VRow>
    <VRow>
        <VCol md="6" cols="12" style="padding: 0 12px;">
            <VRow no-gutters style="min-height: 3.5em;">
                <VCol cols="6" :md="6">
                    <label>할부기간</label>
                </VCol>
                <VCol cols="6" :md="6">
                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.common_info.installment" name="installment"
                        variant="underlined"
                        :items="filterInstallment" prepend-icon="fluent-credit-card-clock-20-regular"
                        label="할부기간 선택" item-title="title" item-value="id" single-line :rules="[requiredValidatorV2(props.common_info.installment, '할부기간')]" />
                </VCol>
            </VRow>
        </VCol>
        <VCol md="6" cols="12" style="padding: 0 12px;">
            <VRow no-gutters style="min-height: 3.5em;">
                <VCol md="6">
                    <b>총결제금액</b>
                </VCol>
                <VCol md="6" style="text-align: end;">
                    <span class="product-amount">
                        <h3 style="display: inline-block;">{{ props.common_info.amount.toLocaleString() }}</h3>원
                    </span> 
                </VCol>
            </VRow>
        </VCol>
    </VRow>
</template>
