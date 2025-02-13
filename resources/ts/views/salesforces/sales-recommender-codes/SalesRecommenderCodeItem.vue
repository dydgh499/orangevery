<script lang="ts" setup>
import { payWindowStore } from '@/views/quick-view/payWindowStore';
import { useRequestStore } from '@/views/request';
import type { SalesRecommenderCode } from '@/views/types';
import { requiredValidatorV2 } from '@validators';
import { VForm } from 'vuetify/components';
interface Props {
    item: SalesRecommenderCode,
}
const vForm = ref<VForm>()
const props = defineProps<Props>()
const { copy } = payWindowStore()
const { update, remove } = useRequestStore()
const removeItem = () => {
    if(props.item.id) 
        remove('/salesforces/sales-recommender-codes', props.item, false)
    else 
        props.item.id = -1
}
</script>
<template>
    <VRow>
        <VCol cols="12">
            <div
                icon
                class="remove-btn"
                @click="removeItem()" 
            >
                <VIcon :icon="`tabler-x`" />
            </div>
            <VCard>
                <VForm ref="vForm">
                    <VCol class="d-flex justify-space-between small-font">
                        <div>
                            <div>
                                <b>
                                    추천인코드
                                </b>                         
                            </div>
                            <div style="margin-top: 0.5em;">
                                <VChip v-if="props.item.id" color="success">
                                    {{ props.item.recommend_code }}
                                </VChip>
                                <VChip v-else color="default">
                                    코드발급후 자동생성
                                </VChip>
                            </div>
                        </div>
                        <VBtn 
                            type="button" 
                            color="primary" 
                            size="small" 
                            variant="tonal"
                            style="margin-top: auto; margin-left: 1em;"
                            @click="copy(props.item.recommend_code, '추천인코드')">
                            코드복사
                        </VBtn>
                    </VCol>
                    <VCol class="d-flex justify-space-between small-font">
                        <div>
                            <div>
                                <b>가맹점 수수료율</b> 
                            </div>
                            <div>
                                <VTextField 
                                    v-model="props.item.mcht_fee" 
                                    variant="underlined"
                                    placeholder="수수료율 입력" suffix="%" 
                                    style="min-width: 8em;"
                                    :rules="[requiredValidatorV2(props.item.mcht_fee, '수수료율')]"
                                />
                            </div>
                        </div>
                        <VBtn 
                            type="button" 
                            :color="props.item.id ? 'primary' : 'warning'" 
                            size="small" 
                            style="margin-top: auto; margin-left: 1em;"
                            @click="update('/salesforces/sales-recommender-codes', props.item, vForm, false)">
                            {{ props.item.id == 0 ? "코드발급" : "수정하기" }}
                        </VBtn>
                    </VCol>
                </VForm>
            </VCard>
        </VCol>
    </VRow>
</template>
<style scoped lang="scss">
:deep(.v-input--density-compact .v-field--variant-plain, .v-input--density-compact .v-field--variant-underlined) {
  --v-input-control-height: 34px !important;
}

.remove-btn {
  position: absolute;
  z-index: 9;
  border-radius: 0.2rem;
  background-color: rgba(var(--v-theme-error), 1) !important;
  block-size: 1.6em;
  color: rgba(var(--v-theme-background)) !important;
  cursor: pointer;
  inline-size: 1.6em;
  inset-inline-end: 1em;
  margin-block-start: -0.5em;
  transform: scale(1);
  transition: 0.5s ease all;

  /* 기본 상태에서의 transition 설정 */

  &:hover {
    color: rgba(var(--v-theme-surface)) !important;
    transform: scale(1.1);

    /* 회전과 크기 조정 함께 적용 */
    transform-origin: center;

    /* 회전 중심 설정 */
  }
}
</style>
