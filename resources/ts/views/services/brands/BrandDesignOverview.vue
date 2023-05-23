<script lang="ts" setup>
import { axios } from '@axios';
import { nullValidator, requiredValidator } from '@validators';
import type { Brand } from '@/views/types'
import FileLogoInput from '@/views/utils/FileLogoInput.vue';
import KaKaoTalkPriview from '@/views/utils/KaKaoTalkPriview.vue';
import CreateHalfVCol from '@/views/utils/CreateHalfVCol.vue';
import { useTheme } from 'vuetify'

interface Props {
    item: Brand,
}

const vuetifyTheme = useTheme()
const props = defineProps<Props>()

const currentThemeName = vuetifyTheme.name.value
const color = ref<string>(vuetifyTheme.themes.value[currentThemeName].colors.primary)

const images = [
    {
        file: ref(props.item.logo_img),
        label: '로고 이미지(*.svg)',
    },
    {
        file: ref(props.item.dark_logo_img),
        label: '다크모드 로고(*.svg)',
    },
    {
        file: ref(props.item.favicon_img),
        label: '파비콘 이미지(*.ico)',
    },
]
watchEffect(() => {
    images[0].file.value = props.item.logo_img
    images[1].file.value = props.item.dark_logo_img
    images[2].file.value = props.item.contract_img
})
watchEffect(() => {
    vuetifyTheme.themes.value[currentThemeName].colors.primary = color.value
})
</script>
<template>
    <VRow class="match-height">
        <!-- 👉 운영정보 -->
        <VCol cols="12" md="6">
            <VCard>
                <VCardItem>
                    <VCardTitle>페이지 디자인</VCardTitle>
                    <VRow class="pt-5">
                        <VCol md="6">
                            <VCol v-for="(logo, index) in images" :key=index>
                                <VRow no-gutters>
                                    <FileLogoInput :file="logo.file" :label="logo.label">
                                    </FileLogoInput>
                                </VRow>
                            </VCol>
                        </VCol>
                        <VCol md="6">
                            <VCol>
                                <VRow>
                                    <CreateHalfVCol>
                                        <template #name><span></span>테마 색상</template>
                                        <template #input>
                                            <VColorPicker v-model="color" show-swatches swatches-max-height="360px" />
                                        </template>
                                    </CreateHalfVCol>
                                </VRow>
                            </VCol>
                        </VCol>
                    </VRow>
                </VCardItem>
            </VCard>
        </VCol>
        <!-- 👉 계약정보 -->
        <VCol cols="12" md="6">
            <VCard>
                <VCardItem>
                    <VCardTitle>카카오톡 미리보기</VCardTitle>
                    <VRow class="pt-5">
                        <KaKaoTalkPriview :file="toRef(props.item, 'og_img')" :name="toRef(props.item, 'name')" :og_description="toRef(props.item, 'og_description')">
                        </KaKaoTalkPriview>
                    </VRow>
                </VCardItem>
            </VCard>
        </VCol>
        <!-- 👉 submit -->
    </VRow>
</template>

