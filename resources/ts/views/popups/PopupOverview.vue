<script lang="ts" setup>
import Editor from '@/layouts/utils/Editor.vue'
import type { Popup } from '@/views/types'
import { requiredValidatorV2 } from '@validators'

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
                        <VCol md="1" cols="3">
                            오픈기간
                        </VCol>
                        <VCol md="5" cols="12" style="display: inline-flex;">
                            <VTextField type="date" v-model="props.item.open_s_dt"
                                prepend-inner-icon="ic-baseline-calendar-today" label="시작일 입력" single-line 
                                style="max-width: 12em;"/>
                            <span style="margin: 0 0.5em; line-height: 2.5em;">~</span>
                                <VTextField type="date" v-model="props.item.open_e_dt"
                                prepend-inner-icon="ic-baseline-calendar-today" label="종료일 입력" single-line
                                style="max-width: 12em;"/>
                        </VCol>
                    </VRow>
                    <VRow>
                        <VCol>제목</VCol>
                        <VCol md="11" cols="12">
                            <VTextField id="nameHorizontalIcons" v-model="props.item.popup_title"
                                    prepend-inner-icon="ic-round-subtitles" placeholder="제목을 입력해주세요"
                                    persistent-placeholder :rules="[requiredValidatorV2(props.item.popup_title, '제목')]" />
                        </VCol>
                    </VRow>
                    <VRow>
                        <VCol>
                            <Editor :content="props.item.popup_content" @update:content="props.item.popup_content = $event"/>
                        </VCol>
                    </VRow>
                </VCardItem>
            </VCard>
        </VCol>
    </VRow>
</template>
