<script lang="ts" setup>
import { useRequestStore } from '@/views/request';
import { useSalesFilterStore } from '@/views/salesforces/useStore';
import type { Category } from '@/views/types';
import { requiredValidatorV2 } from '@validators';
import { VForm } from 'vuetify/components';

const vForm = ref<VForm>()
const category = ref(<Category>{})
const visible = ref(false)
const is_add = ref(false)
const store = <any>(inject('store'))

const { mchts } = useSalesFilterStore()
const { formRequest, remove } = useRequestStore()

const show = (_category: Category, _is_add: boolean) => {
    category.value = _category
    is_add.value = _is_add
    visible.value = true
}

const CategoryUpdate = async () => {
    await formRequest('/merchandises/shopping-mall/categories', category.value, vForm.value, false)
    store.setTable()
}

const CategoryDelete = async () => {
    await remove('/merchandises/shopping-mall/categories', category.value, false)
    store.setTable()
}

defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="visible" persistent max-width="800">
        <DialogCloseBtn @click="visible = false" />
        <VCard>
            <VForm ref="vForm">
                <VCardItem>
                    <VCardTitle>카테고리 {{ is_add ? '추가' : '수정'}}</VCardTitle>
                    <VRow class="pt-3">
                        <VCol cols="12" md="6" >
                            <VRow no-gutters>
                                <VCol>
                                    <label>가맹점 선택</label>
                                </VCol>
                                <VCol md="8">
                                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="category.mcht_id" :items="mchts"
                                        variant="underlined"
                                        prepend-icon="tabler-building-store" 
                                        item-title="mcht_name" item-value="id" single-line
                                        :rules="[requiredValidatorV2(category.mcht_id, '가맹점')]" 
                                        placeholder="가맹점 선택" 
                                    />
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12" md="6">
                            <VRow no-gutters>
                                <VCol>
                                    <label>카테고리명</label>
                                </VCol>
                                <VCol md="8">
                                    <div style="display: flex;">
                                        <VTextField 
                                            v-model="category.category_name" 
                                            prepend-inner-icon="tabler:category" 
                                            placeholder="카테고리명"
                                            :rules="[requiredValidatorV2(category.category_name, '카테고리명')]" 
                                            variant="underlined"
                                        >
                                        </VTextField>
                                    </div>
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                    <VDivider style="margin: 1em 0;"/>
                    <VRow>
                        <VCol cols="12" class="d-flex gap-4">
                            <VBtn type="button" style="margin-left: auto;" @click="CategoryUpdate()">
                                {{ category.id == 0 ? "추가" : "수정" }}
                                <VIcon end icon="tabler-pencil" />
                            </VBtn>
                            <VBtn type="button" color="error" v-if="category.id" @click="CategoryDelete()">
                                삭제
                                <VIcon end icon="tabler-trash" />
                            </VBtn>
                        </VCol>
                    </VRow>
                </VCardItem>
            </VForm>
        </VCard>
    </VDialog>
</template>

<style scoped>
:deep(.v-row) {
  align-items: center;
}
</style>
