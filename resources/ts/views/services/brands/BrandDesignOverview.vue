<script lang="ts" setup>
import LoginImageDialog from '@/layouts/dialogs/services/LoginImageDialog.vue';
import type { IdentityDesign } from '@/views/types';
import { requiredValidatorV2 } from '@validators';

interface Props {
    item: IdentityDesign,
}
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
                                    <VColorPicker v-model="props.item.theme_css.main_color" show-swatches swatches-max-height="220px" mode="rgb"/>
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
                                    <span style="margin-right: 1em;">파비콘 이미지</span>
                                    <div :style="$vuetify.display.smAndDown ? 'display: inline-flex;flex-direction: column;' : 'display: inline-flex;'">
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
                            <br>
                            <VCol>
                                <VRow>
                                    <div class="preview-box" :style="'height:3em; width:15em; background: rgb(var(--v-theme-background)); display:inline-flex; align-items: center; border-bottom-color: black; border-radius: 8px 8px 0 0;'">
                                        <div :style="`background-repeat: round; background-image: url(${favicon_preview}); block-size: 32px; width:32px;  margin:0.5em; display: inline-block;`">
                                        </div>
                                        <b>{{ props.item.name }}</b>
                                        <b style="position: relative;top: -0.5em;margin-right: 0.5em;margin-left: auto;">X</b>
                                    </div>
                                    <div class="preview-box" :style="'height:3em; width:2em; display:inline-flex; align-items: center; border-bottom-color: black; border-radius: 8px 8px 0 0;'">
                                        <b> ...</b>
                                    </div>
                                    <div class="preview-box" :style="'height:4em; width:17em; display:inline-flex; align-items: center; border-radius:0px;'">
                                        <VIcon :icon="'material-symbols:refresh'" style="margin: 0 0.5em;"/>
                                        <div class="preview-box" :style="'background: rgb(var(--v-theme-background));height:2.5em; display:flex; align-items: center; border-radius:1.5em 0 0px 1.5em;'">
                                            <b style=" width: 13.7em;padding-left: 1em;">https://{{ props.item.dns }}</b>
                                        </div>
                                    </div>
                                </VRow>
                            </VCol>
                            <br>
                            <VCardTitle>
                                <div style="display: flex;align-items: center;justify-content: space-between;">
                                    <span style="margin-right: 1em;">전산 로고</span>
                                    <div :style="$vuetify.display.smAndDown ? 'display: inline-flex;flex-direction: column;' : 'display: inline-flex;'">
                                        <VChip variant="elevated" color="warning" @click="triggerFileInput('logo-upload')">
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
                                <VImg :src="logo_preview" height="72"/>
                            </VCol>
                            <br>
                        </VCol>
                    </VRow>
                </VCardItem>
            </VCard>
        </VCol>
        <VCol cols="12" md="6">
            <VCard>
                <VCardItem>
                    <VRow class="pt-5">
                        <VCol cols="6">
                            <VCardTitle>
                                <div style="display: flex;align-items: center;justify-content: space-between;">
                                    <span style="margin-right: 1em;">카카오톡 미리보기</span>
                                    <div :style="$vuetify.display.smAndDown ? 'display: inline-flex;flex-direction: column;' : 'display: inline-flex;'">
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
                                                v-model="props.item.og_description" 
                                                auto-grow rows="3"
                                                label="미리보기 문구"
                                            />
                                        </div>
                                    </div>
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol cols="6">
                            <VCardTitle>
                                <div style="display: flex;align-items: center;justify-content: space-between;">
                                    <span style="margin-right: 1em;">로그인페이지 배경</span>
                                    <div :style="$vuetify.display.smAndDown ? 'display: inline-flex;flex-direction: column;' : 'display: inline-flex;'">
                                        <VChip variant="elevated" color="primary" @click="loginImageDialog.show()">
                                            로그인페이지 배경선택
                                        </VChip>
                                    </div>
                                </div>
                            </VCardTitle>
                            <br>
                            <VRow no-gutters>
                                <VCol md="12" style="padding: 0.5em;">
                                    <div class="preview-box">
                                        <div 
                                            class="preview-image-box" 
                                            :style="`background-image: url(${props.item.login_img}); block-size: 345px;`">
                                        </div>
                                    </div>
                                </VCol>
                            </VRow>
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
