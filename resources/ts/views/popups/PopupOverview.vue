<script lang="ts" setup>
import type { Popup } from '@/views/types'
import { requiredValidatorV2 } from '@validators'
import Editor from '@/layouts/utils/Editor.vue'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'

interface Props {
    item: Popup,
}
const props = defineProps<Props>()
</script>
<template>
    <VRow class="match-height">
        <!-- 👉 개인정보 -->
        <VCol cols="12" md="12">
            <VCard>
                <VCardItem>
                    <VCardTitle>팝업 작성</VCardTitle>
                    <VRow  class="pt-5">                        
                        <VCol md="1">
                            오픈 기간
                        </VCol>
                        <VCol md="5">
                            <VRow no-gutters>
                                <VCol md="5">
                                    <VTextField type="date" v-model="props.item.open_s_dt"
                                        prepend-inner-icon="ic-baseline-calendar-today" label="시작일 입력" single-line />

                                </VCol>
                                <span style="margin: 0 1em; line-height: 2.5em;">~</span>
                                <VCol md="5">
                                    <VTextField type="date" v-model="props.item.open_e_dt"
                                    prepend-inner-icon="ic-baseline-calendar-today" label="종료일 입력" single-line/>
                                    
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                    <VRow>
                        <CreateHalfVCol :mdl="1" :mdr="11">
                            <template #name>제목</template>
                            <template #input>
                                <VTextField id="nameHorizontalIcons" v-model="props.item.popup_title"
                                    prepend-inner-icon="ic-round-subtitles" placeholder="제목을 입력해주세요"
                                    persistent-placeholder :rules="[requiredValidatorV2(props.item.popup_title, '제목')]" />
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                    <VRow>
                        <CreateHalfVCol :mdl="1" :mdr="11">
                            <template #name>내용</template>
                            <template #input>
                                <Editor :content="props.item.popup_content" @update:content="props.item.popup_content = $event"/>
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                </VCardItem>
            </VCard>
        </VCol>
    </VRow>
</template>
