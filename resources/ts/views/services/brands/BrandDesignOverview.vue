<script lang="ts" setup>
import type { Brand } from '@/views/types'
import FileLogoInput from '@/layouts/utils/FileLogoInput.vue';
import KakaotalkPreview from '@/layouts/utils/KakaotalkPreview.vue';
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue';
import { useTheme } from 'vuetify'

interface Props {
    item: Brand,
}

const props = defineProps<Props>()

const vuetifyTheme = useTheme()
const currentThemeName = vuetifyTheme.name.value
const color = ref(props.item.theme_css.main_color)

watchEffect(() => {
    vuetifyTheme.themes.value[currentThemeName].colors.primary = color.value
    props.item.theme_css.main_color = color.value
})
watchEffect(() => {
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
                                        :label="'로고 이미지(*.svg)'" @update:file="props.item.logo_file = $event" />
                                </VRow>
                                <VRow no-gutters>
                                    <FileLogoInput :file="props.item.favicon_file" :preview="props.item.favicon_img"
                                        :label="'파비콘 이미지(*.ico)'" @update:file="props.item.favicon_file = $event" />
                                </VRow>
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

