<script lang="ts" setup>
import type { Brand } from '@/views/types'
import FileLogoInput from '@/layouts/utils/FileLogoInput.vue'
import Preview from '@/layouts/utils/Preview.vue'
import KakaotalkPreview from '@/layouts/utils/KakaotalkPreview.vue'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'
import { useTheme } from 'vuetify'
import { themeConfig } from '@themeConfig'
import authV2LoginDefault from '@images/pages/auth-v2-login-default.png'
import authV2LoginDefault2 from '@images/pages/auth-v2-login-default2.png'
import authV2LoginDefault3 from '@images/pages/auth-v2-login-default3.png'

import { Pagination, EffectCoverflow } from 'swiper'
import { Swiper, SwiperSlide } from 'swiper/vue'

// import swiper module styles
import 'swiper/css'
import 'swiper/css/pagination'
import 'swiper/css/effect-coverflow'
import { PropertyDescriptorParsingType } from 'html2canvas/dist/types/css/IPropertyDescriptor'

interface Props {
    item: Brand,
}

const swiper = ref()
const modules = [Pagination, EffectCoverflow];
const props = defineProps<Props>()

const vuetifyTheme = useTheme()
const login_file = ref(<File[]>([]))
const preview = ref(<string>(props.item.login_img ?? authV2LoginDefault))

const color = ref(props.item.theme_css.main_color)
const previewStyle = `
    border: 2px solid rgb(238, 238, 238);
    border-radius: 0.5em;
    margin-block: 0;
    margin-inline: 0.5em;
`;


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
const getRef = (swiperInstance:any) => {
    swiper.value = swiperInstance
}
const setDefaultimage = () => {
    if(swiper.value.activeIndex == 0)
        preview.value = authV2LoginDefault
    else if(swiper.value.activeIndex == 1)
        preview.value = authV2LoginDefault2
    else if(swiper.value.activeIndex == 2)
        preview.value = authV2LoginDefault3
    else
        return
    props.item.default_login_img = preview.value
}
watchEffect(() => {
    setPrimaryColor(color.value)
})

watchEffect(() => {
    if (login_file.value != undefined && login_file.value.length) {
        props.item.login_file = login_file.value[0]
        preview.value = URL.createObjectURL(login_file.value[0])
    }
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
                                <VCol cols="12" md="6" style="padding: 0 0.5em;">
                                    <VFileInput accept="image/*" show-size v-model="login_file"
                                        :label="'배경 이미지(가로 최대 1500px)'" prepend-icon="tabler-camera-up">
                                        <template #selection="{ fileNames }">
                                            <template v-for="fileName in fileNames" :key="fileName">
                                                <VChip label size="small" variant="outlined" color="primary" class="me-2">
                                                    {{ fileName }}
                                                </VChip>
                                            </template>
                                        </template>
                                    </VFileInput>
                                    <br>
                                    <BaseQuestionTooltip :location="'top'" :text="'기본 제공 배경 이미지'"
                                        :content="'기본으로 제공되는 배경 이미지 입니다.<br>하단 스와이프뷰에서 이미지를 선택하신 후, 선택 버튼을 눌러주세요.'">
                                    </BaseQuestionTooltip>
                                    <br>
                                    <br>
                                    <div class="coverflow-example">
                                        <Swiper class="swiper" :modules="modules" :pagination="true" :effect="'coverflow'"
                                            :grab-cursor="true" :centered-slides="true" :slides-per-view="'auto'"
                                            @swiper="getRef"
                                            :coverflow-effect="{
                                                rotate: 50,
                                                stretch: 0,
                                                depth: 100,
                                                modifier: 1,
                                                slideShadows: true
                                            }">
                                            <SwiperSlide class="slide" :style="previewStyle">
                                                <VImg rounded :src="authV2LoginDefault"></VImg>
                                            </SwiperSlide>
                                            <SwiperSlide class="slide" :style="previewStyle">
                                                <VImg rounded :src="authV2LoginDefault2"></VImg>
                                            </SwiperSlide>
                                            <SwiperSlide class="slide" :style="previewStyle">
                                                <VImg rounded :src="authV2LoginDefault3"></VImg>
                                            </SwiperSlide>
                                        </Swiper>
                                    </div>
                                    <div style="text-align: end;">
                                        <VBtn @click="setDefaultimage()">
                                            선택
                                        </VBtn>
                                    </div>
                                </VCol>
                                <VCol cols="12" md="6">
                                    <Preview :preview="preview" :style="``" :preview-style="previewStyle" class="preview" />
                                </VCol>
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
