<script lang="ts" setup>
import type { Brand } from '@/views/types'
import FileLogoInput from '@/layouts/utils/FileLogoInput.vue'
import KakaotalkPreview from '@/layouts/utils/KakaotalkPreview.vue'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import SwiperPreview from '@/layouts/utils/SwiperPreview.vue'
import { useTheme } from 'vuetify'
import { themeConfig } from '@themeConfig'
import authV2LoginDefault1 from '@images/pages/auth-v2-login-default1.png'
import authV2LoginDefault2 from '@images/pages/auth-v2-login-default2.png'
import authV2LoginDefault3 from '@images/pages/auth-v2-login-default3.png'

interface Props {
    item: Brand,
}
const props = defineProps<Props>()

const vuetifyTheme = useTheme()
const login_imgs = [
    authV2LoginDefault1,
    authV2LoginDefault2,
    authV2LoginDefault3,
]
const color = ref(props.item.theme_css.main_color)

const setPrimaryColor = (color: string) => {
    localStorage.setItem(`${themeConfig.app.title}-lightThemePrimaryColor`, color)
    localStorage.setItem(`${themeConfig.app.title}-darkThemePrimaryColor`, color)
    vuetifyTheme.themes.value['light'].colors.primary = color
    vuetifyTheme.themes.value['dark'].colors.primary = color

    props.item.theme_css.main_color = color
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
                    <VRow>
                        <VCol md="6">
                            <VCardTitle>로고 등록</VCardTitle>
                            <br>
                            <VCol>
                                <VRow no-gutters>
                                    <FileLogoInput :preview="props.item.logo_img ?? ''" :label="'로고 이미지(85 * 85px *.svg)'"
                                        @update:file="props.item.logo_file = $event" :validates="['svg']" />
                                </VRow>
                                <VRow no-gutters>
                                    <FileLogoInput :preview="props.item.favicon_img ?? ''"
                                        :label="'파비콘 이미지(32 * 32px*.ico)'" @update:file="props.item.favicon_file = $event"
                                        :validates="['ico']" />
                                </VRow>
                            </VCol>
                            <VCol>
                                <div class="d-inline-flex align-center flex-wrap gap-4 justify-content-evenly float-right">
                                    <VBtn variant="tonal" @click="moveNewTap('https://convertio.co/kr/png-svg/')">
                                        SVG 추출하러 가기
                                        <VTooltip activator="parent" location="top">
                                            홈페이지의 이미지 품질을 위해 로고 이미지는 *.SVG 파일만 지원합니다.
                                        </VTooltip>
                                    </VBtn>
                                    <VBtn variant="tonal" color="secondary"
                                        @click="moveNewTap('https://convertio.co/kr/png-ico/')">
                                        ICO 추출하러 가기
                                        <VTooltip activator="parent" location="top">
                                            웹 표준과 브라우저 호환성을 위해 파비콘 이미지는 *.ico 파일만 지원합니다.
                                        </VTooltip>
                                    </VBtn>
                                </div>
                                <br>
                                <br>
                                <VCol style="text-align: center;">
                                    <b>좌우상하 공백을 제거한 후 이미지들 등록해주세요.</b>
                                </VCol>
                            </VCol>
                        </VCol>
                        <VCol md="6">
                            <VCardTitle>테마 색상</VCardTitle>
                            <br>
                            <VCol>
                                <VRow no-gutters>
                                    <CreateHalfVCol :mdl="3" :mdr="9">
                                        <template #name></template>
                                        <template #input>
                                            <VColorPicker v-model="color" show-swatches swatches-max-height="360px" mode="rgb"/>
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
                        <VCol cols="12">
                            <VRow no-gutters>
                                <KakaotalkPreview :file="props.item.og_file" :preview="props.item.og_img ?? ''"
                                    :name="toRef(props.item, 'name')" :og_description="toRef(props.item, 'og_description')"
                                    @update:file="props.item.og_file = $event">
                                </KakaotalkPreview>
                            </VRow>
                        </VCol>
                    </VRow>
                    <br>
                    <VCardTitle>로그인 페이지 배경 이미지</VCardTitle>
                    <VRow class="pt-5">
                        <VCol cols="12">
                            <VRow no-gutters>
                                <SwiperPreview :items="login_imgs" :default_img="props.item.login_img ?? login_imgs[Math.floor(Math.random() * login_imgs.length)]"
                                    :item_name="'배경'" :lmd="6" :rmd="6"
                                    @update:file="props.item.login_file = $event"
                                    @update:default="props.item.login_img = $event">
                                </SwiperPreview>
                            </VRow>
                        </VCol>
                    </VRow>
                </VCardItem>
            </VCard>
        </VCol>
        <!-- 👉 submit -->
    </VRow>
</template>
<style lang="scss" scoped>
.coverflow-example {
  position: relative;
}

.swiper {
  block-size: 100%;
  inline-size: 100%;
  padding-block-end: 50px;

  .slide {
    block-size: 200px;
    inline-size: 200px;

    img {
      display: block;
      border: 1px solid rgba(5, 5, 5, 20%);
      border-radius: 0.5em;
      block-size: 100%;
      inline-size: 100%;
      object-fit: cover;
    }
  }
}
</style>
