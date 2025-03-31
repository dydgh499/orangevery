<script lang="ts" setup>
import LoginImageDialog from '@/layouts/dialogs/services/LoginImageDialog.vue';
import type { IdentityDesign } from '@/views/types';
import { isAbleModiy } from '@axios';
import { useGenerateImageVariant } from '@core/composable/useGenerateImageVariant';
import authV2MaskDark from '@images/pages/misc-mask-dark.png';
import authV2MaskLight from '@images/pages/misc-mask-light.png';
import { requiredValidatorV2 } from '@validators';

interface Props {
    item: IdentityDesign,
}
const authThemeMask = useGenerateImageVariant(authV2MaskLight, authV2MaskDark)
const props = defineProps<Props>()
const loginImageDialog = ref()

const logo_files = ref(<any>(null))
const logo_preview = ref<string>(props.item.logo_img == undefined ? '/utils/icons/img-preview.svg' : props.item.logo_img)

const favicon_files = ref(<any>(null))
const favicon_preview = ref<string>(props.item.favicon_img == undefined ? '/utils/icons/img-preview.svg' : props.item.favicon_img)

const kakao_files = ref(<any>(null))
const kakao_preview = ref<string>(props.item.og_img == undefined ? '/utils/icons/img-preview.svg' : props.item.og_img)

const triggerFileInput = (file_input: string) => {
    document.getElementById(file_input)?.click();    
};


watchEffect(() => {
    if(logo_files.value != undefined) {
        logo_preview.value = logo_files.value.length ? URL.createObjectURL(logo_files.value[0]) : '/utils/icons/img-preview.svg'
        props.item.logo_file = logo_files.value ? logo_files.value[0] : logo_files.value
    }
})

watchEffect(() => {
    if(favicon_files.value != undefined) {
        favicon_preview.value = favicon_files.value.length ? URL.createObjectURL(favicon_files.value[0]) : '/utils/icons/img-preview.svg'
        props.item.favicon_file = favicon_files.value ? favicon_files.value[0] : favicon_files.value
    }
})

watchEffect(() => {
    if(kakao_files.value != undefined) {
        kakao_preview.value = kakao_files.value.length ? URL.createObjectURL(kakao_files.value[0]) : '/utils/icons/img-preview.svg'
        props.item.og_file = kakao_files.value ? kakao_files.value[0] : kakao_files.value
    }
})

</script>
<template>
    <VRow class="match-height">
        <VCol cols="12" md="6">
            <VCard>
                <VCardItem>
                    <VRow>
                        <VCol md="6">
                            <VCardTitle>테마 색상</VCardTitle>
                            <br>
                            <VCol>
                                <VRow no-gutters>
                                    <VColorPicker 
                                        :disabled="isAbleModiy(props.item.id) === false"
                                        v-model="props.item.theme_css.main_color" 
                                        :show-swatches="isAbleModiy(props.item.id)"
                                        swatches-max-height="220px" mode="rgb"/>
                                </VRow>
                            </VCol>
                        </VCol>
                        <VCol md="6">
                            <VCardTitle>
                                <div style="display: flex;align-items: center;justify-content: space-between;">
                                    <span style="margin-right: 1em;">운영사명</span>
                                </div>
                            </VCardTitle>
                            <VCol>
                                <VRow>
                                    <VTextField
                                        :readonly="isAbleModiy(props.item.id) === false"
                                        v-model="props.item.name" 
                                        variant="underlined"
                                        prepend-inner-icon="twemoji-desktop-computer"
                                        placeholder="운영사명을 입력해주세요" persistent-placeholder :rules="[requiredValidatorV2(props.item.name, '운영사명')]" />

                                </VRow>
                            </VCol>
                            <br>
                            <VCardTitle>
                                <div style="display: flex;align-items: center;justify-content: space-between;">
                                    <span style="margin-right: 1em;">도메인주소</span>
                                </div>
                            </VCardTitle>
                            <VCol>
                                <VRow>
                                    <VTextField
                                            :readonly="isAbleModiy(props.item.id) === false"
                                            v-model="props.item.dns" 
                                            variant="underlined"
                                            prepend-inner-icon="carbon:dns-services"
                                            placeholder="DNS를 입력해주세요" persistent-placeholder :rules="[requiredValidatorV2(props.item.name, 'DNS')]" />
                                </VRow>
                            </VCol>
                            <br>
                            <VDivider style="margin-bottom: 1em;"/>
                            
                            <VCardTitle>
                                <div style="display: flex;align-items: center;justify-content: space-between;">
                                    <span style="margin-right: 1em;">카카오톡 미리보기 <b style="font-size: 0.8em;">(1260x630)</b></span>
                                    <div 
                                        v-if="isAbleModiy(props.item.id)"
                                        :style="$vuetify.display.smAndDown ? 'display: inline-flex;flex-direction: column;' : 'display: inline-flex;'">
                                        <VChip variant="elevated" color="primary" @click="triggerFileInput('kakao-upload')">
                                            미리보기 이미지 업로드
                                        </VChip>
                                        <VFileInput 
                                            accept="image/*" v-model="kakao_files" 
                                            id="kakao-upload"
                                            style="display: none;"/>
                                    </div>
                                </div>
                            </VCardTitle>
                            <br>
                            <VRow no-gutters>
                                <VCol md="12" style="padding: 0.5em;">
                                    <div class="preview-box">
                                        <div class="preview-image-box" :style="`background-image: url(${kakao_preview});`"></div>
                                        <div class="preview-text-box">
                                            <p class="title">{{ props.item.name }}</p>
                                            <VTextarea 
                                                :readonly="isAbleModiy(props.item.id) === false"
                                                
                                                v-model="props.item.og_description" 
                                                auto-grow rows="3"
                                                label="미리보기 문구"
                                            />
                                        </div>
                                    </div>
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                </VCardItem>
            </VCard>
        </VCol>
        <VCol cols="12" md="6">
            <VCard>
                <VCardItem>
                    <VRow>
                        <VCol cols="12">
                            <VCardTitle>
                                <div style="display: flex;align-items: center;justify-content: space-between;">
                                    <span style="margin-right: 1em;">파비콘 이미지 <b style="font-size: 0.8em;">(32x32)</b></span>
                                    <div 
                                        v-if="isAbleModiy(props.item.id)"
                                        :style="$vuetify.display.smAndDown ? 'display: inline-flex;flex-direction: column;' : 'display: inline-flex;'">
                                        <VChip variant="elevated" color="warning" @click="triggerFileInput('favicon-upload')">
                                            파비콘 이미지 업로드
                                        </VChip>
                                        <VFileInput 
                                            accept="image/*" v-model="favicon_files" 
                                            id="favicon-upload"
                                            style="display: none;"/>
                                    </div>
                                </div>
                            </VCardTitle>
                            <VCol>
                                <div>
                                    <div class="preview-box" :style="'height:3em; width:16em; background: rgb(var(--v-theme-background)); display:inline-flex; align-items: center; border-bottom-color: black; border-radius: 8px 8px 0 0; position: relative;top: 11px;'">
                                        <div :style="`background-repeat: round; background-image: url(${favicon_preview}); block-size: 32px; width:32px;  margin:0.5em; display: inline-block;`">
                                        </div>
                                        <b>{{ props.item.name }}</b>
                                        <b style="position: relative;top: -0.5em;margin-right: 0.5em;margin-left: auto;">X</b>
                                    </div>
                                    <div class="preview-box" :style="'height:3em; width:3em; display:inline-flex; align-items: center; border-bottom-color: black; border-radius: 8px 8px 0 0;'">
                                        <b> ...</b>
                                    </div>
                                    <div class="preview-box" :style="'height:3em; width:3em; display:inline-flex; align-items: center; border-bottom-color: black; border-radius: 8px 8px 0 0;'">
                                        <b> ...</b>
                                    </div>
                                </div>
                                <div>
                                    <div class="preview-box" :style="'height:4em; width:100%; display:inline-flex; align-items: center; border-radius:0px;'">
                                        <VIcon :icon="'material-symbols:refresh'" style="margin: 0 0.5em;"/>
                                        <div class="preview-box" :style="'background: rgb(var(--v-theme-background));height:2.5em; display:flex; align-items: center; border-radius:1.5em 0 0px 1.5em;'">
                                            <b style=" width: 44.5em;padding-left: 1em;">https://{{ props.item.dns }}</b>
                                        </div>
                                    </div>
                                </div>
                            </VCol>
                        </VCol>
                    </VRow>
                    <VRow>
                        <VCol cols="7">
                            <VCardTitle>
                                <div style="display: flex;align-items: center;justify-content: space-between;">
                                    <span style="margin-right: 1em;">로그인페이지 배경</span>
                                    <div 
                                        :style="$vuetify.display.smAndDown ? 'display: inline-flex;flex-direction: column;' : 'display: inline-flex;'">
                                        <VChip variant="elevated" color="primary" @click="loginImageDialog.show()">
                                            로그인페이지 배경선택
                                        </VChip>
                                    </div>
                                </div>
                            </VCardTitle>
                            <br>
                            <VRow no-gutters class="auth-wrapper bg-surface">
                                <VCol md="12" class="d-none d-md-flex">
                                    <div class="position-relative bg-background rounded-lg w-100 ma-8 me-0">
                                        <div class="d-flex align-center justify-center w-100 h-100">
                                            <VImg max-width="220" :src="props.item.login_img" class="auth-illustration mt-16 mb-2" />
                                        </div>
                                        <VImg :src="authThemeMask" class="auth-footer-mask" />
                                    </div>
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol cols="5" class="auth-card-v2">
                            <VCardTitle>
                                <div style="display: flex;align-items: center;justify-content: space-between;">
                                    <span style="margin-right: 1em;">전산 로고 <b style="font-size: 0.8em;">(256x256)</b></span>
                                    <div :style="$vuetify.display.smAndDown ? 'display: inline-flex;flex-direction: column;' : 'display: inline-flex;'">
                                        <VChip 
                                            v-if="isAbleModiy(props.item.id)"
                                            variant="elevated" color="warning" @click="triggerFileInput('logo-upload')">
                                            로고 이미지 업로드
                                        </VChip>
                                        <VFileInput 
                                            accept="image/*" v-model="logo_files" 
                                            id="logo-upload"
                                            style="display: none;"/>
                                    </div>
                                </div>
                            </VCardTitle>
                            <br>
                            <VCol>
                                <VCard flat class="mt-sm-0 pa-4" :max-width="320" style=" margin-right: auto;margin-left: auto;">
                                    <VCardText style="padding: 0; padding-bottom: 24px;">
                                        <div class="mb-6">
                                            <VImg :src="logo_preview" width="32" height="32"/>
                                        </div>
                                        <h4 class="mb-1 font-weight-bold">
                                            <span class="text-capitalize">{{ props.item.name }}</span>에 오신것을 환영합니다! 👋🏻
                                        </h4>
                                    </VCardText>
                                    <VCardText style="padding: 0; font-size: 0.9em;">
                                        <div style="padding: 0.25em 0.5em; border: 1px solid rgba(var(--v-border-color), 0.9); border-radius: 5px; margin-bottom: 1em;">
                                            <span>아이디 입력</span>
                                        </div>
                                        <div style="padding: 0.25em 0.5em; border: 1px solid rgba(var(--v-border-color), 0.9); border-radius: 5px; margin-bottom: 1em;">
                                            <span>패스워드 입력</span>
                                        </div>
                                        <div class="d-flex align-center flex-wrap mb-4">
                                            <div class="text-primary" style="cursor: pointer;">
                                                패스워드를 잊으셨나요?
                                            </div>
                                        </div>
                                        <VBtn block size="small">
                                            LOGIN
                                        </VBtn>
                                    </VCardText>
                                </VCard>
                            </VCol>
                        </VCol>
                    </VRow>
                </VCardItem>
            </VCard>
        </VCol>
        <LoginImageDialog ref="loginImageDialog" :item="props.item" :key="props.item.login_img"/>
    </VRow>
</template>
<style lang="scss" scoped>
.preview-box {
  border: 1px solid #e5e5e5;
  border-radius: 8px;
}

.preview-image-box {
  background-position: 50%;
  background-repeat: no-repeat;
  background-size: cover;
  block-size: 210px;
  border-block-end: 1px solid #e5e5e5;
  border-start-end-radius: 8px;
  border-start-start-radius: 8px;
  inline-size: 100%;
}

.preview-text-box {
  padding-block: 8px;
  padding-inline: 12px;
}

.description {
  overflow: visible;
  font-size: 13px;
  margin-block-start: -4px;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.title {
  font-weight: 500;
}
</style>
<style lang="scss">
@use "@core-scss/template/pages/page-auth.scss";
</style>
