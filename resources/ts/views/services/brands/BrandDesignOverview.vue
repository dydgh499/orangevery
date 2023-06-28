<script lang="ts" setup>
import type { Brand } from '@/views/types'
import FileLogoInput from '@/layouts/utils/FileLogoInput.vue'
import KakaotalkPreview from '@/layouts/utils/KakaotalkPreview.vue'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import { useTheme } from 'vuetify'
import { themeConfig } from '@themeConfig'

interface Props {
    item: Brand,
}

const props = defineProps<Props>()

const vuetifyTheme = useTheme()
const color = ref(props.item.theme_css.main_color)

const setPrimaryColor = (color: string) => {
    //const currentThemeName = vuetifyTheme.name.value
    // ℹ️ We need to store this color value in localStorage so vuetify plugin can pick on next reload
    localStorage.setItem(`${themeConfig.app.title}-lightThemePrimaryColor`, color)
    localStorage.setItem(`${themeConfig.app.title}-darkThemePrimaryColor`, color)
    vuetifyTheme.themes.value['light'].colors.primary = color
    vuetifyTheme.themes.value['dark'].colors.primary = color

    props.item.theme_css.main_color = color  
    // ℹ️ Update initial loader color
    localStorage.setItem(`${themeConfig.app.title}-initial-loader-color`, color)
}
const moveNewTap = (url: string) => {
    window.open(url)
}
watchEffect(() => {
    setPrimaryColor(color.value)
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
                            <VCol>
                                <VRow no-gutters>
                                    <FileLogoInput :file="props.item.logo_file" :preview="props.item.logo_img"
                                        :label="'로고 이미지(85 * 85px *.svg)'" @update:file="props.item.logo_file = $event" :validates="['svg']"/>
                                </VRow>
                                <VRow no-gutters>
                                    <FileLogoInput :file="props.item.favicon_file" :preview="props.item.favicon_img"
                                        :label="'파비콘 이미지(32 * 32px*.ico)'" @update:file="props.item.favicon_file = $event" :validates="['ico']"/>
                                </VRow>
                            </VCol>
                            <VCol>
                                <div class="d-inline-flex align-center flex-wrap gap-4 justify-content-evenly float-right">
                                    <VBtn variant="tonal" @click="moveNewTap('https://convertio.co/kr/png-svg/')">
                                        SVG 추출하러 가기
                                        <VTooltip
                                            activator="parent"
                                            location="top"
                                        >
                                        홈페이지의 이미지 품질을 위해 로고 이미지는 *.SVG 파일만 지원합니다.
                                        </VTooltip>
                                    </VBtn>
                                    <VBtn variant="tonal" color="secondary" @click="moveNewTap('https://convertio.co/kr/png-ico/')">
                                        ICO 추출하러 가기
                                        <VTooltip
                                            activator="parent"
                                            location="top"
                                        >
                                        웹 표준과 브라우저 호환성을 위해 파비콘 이미지는 *.ico 파일만 지원합니다.
                                        </VTooltip>
                                    </VBtn>
                                </div>
                            </VCol>
                        </VCol>
                        <VCol md="6">
                            <VCol>
                                <VRow>
                                    <CreateHalfVCol :mdl="3" :mdr="9">
                                        <template #name><span></span>테마 색상</template>
                                        <template #input>
                                            <VColorPicker v-model="color" show-swatches swatches-max-height="360px"/>
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
                        <KakaotalkPreview :file="props.item.og_file" :preview="props.item.og_img" :name="toRef(props.item, 'name')"
                            :og_description="toRef(props.item, 'og_description')" @update:file="props.item.og_file = $event">
                        </KakaotalkPreview>
                    </VRow>
                </VCardItem>
            </VCard>
        </VCol>
        <!-- 👉 submit -->
    </VRow>
</template>

