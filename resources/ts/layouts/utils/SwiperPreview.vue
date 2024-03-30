<script setup lang="ts">
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'
import { isAbleModiy } from '@axios'
import Preview from '@/layouts/utils/Preview.vue'
import { Pagination, EffectCoverflow, Navigation } from 'swiper'
import { Swiper, SwiperSlide } from 'swiper/vue'
import 'swiper/css'
import 'swiper/css/pagination'
import 'swiper/css/navigation';
import 'swiper/css/effect-coverflow'

interface Props {
    items: any[],
    preview: string,
    label: string,
    lmd: number,
    rmd: number,
}
const props = defineProps<Props>()

const files = ref(<File[]>([]))
const ext = ref<string>('')
const swiper = ref()
const modules = [Pagination, EffectCoverflow, Navigation]
const previewStyle = `
    border: 2px solid rgb(130, 130, 130);
    border-radius: 0.5em;
    margin-block: 0;
    margin-inline: 0.5em;
`;

const getFileExtension = (file_name: string) => {
    const dot = file_name.lastIndexOf('.') + 1
    return file_name.substring(dot, file_name.length).toLowerCase()
}

const emits = defineEmits(['update:file', 'update:path']);

const getRef = (swiperInstance:any) => {
    swiper.value = swiperInstance
}

const upload = () => {
    if(files.value.length) {
        ext.value = getFileExtension(files.value[0].name)
        emits('update:file', files.value ? files.value[0] : files.value)
        emits('update:path', URL.createObjectURL(files.value[0])) 
    }
}

watchEffect(() => {
    if(files.value.length === 0)
        ext.value = getFileExtension(props.preview)
})
watchEffect(() => {
    // 업로드 파일 삭제 시
    if(files.value.length === 0 && props.preview.includes('blob:'))
        emits('update:path', '/utils/icons/img-preview.svg')
})
</script>
<template>
    <VCol cols="12" :md="props.lmd" style="padding: 0 0.5em;">
        <template v-if="isAbleModiy(0)">
            <VFileInput accept="image/*" show-size v-model="files" :label="label+' 이미지'"
                prepend-icon="tabler-camera-up" @change="upload()">
                <template #selection="{ fileNames }">
                    <template v-for="fileName in fileNames" :key="fileName">
                        <VChip label size="small" variant="outlined" color="primary" class="me-2">
                            {{ fileName }}
                        </VChip>
                    </template>
                </template>
            </VFileInput>
            <br>
            <BaseQuestionTooltip :location="'top'" :text="'기본 '+label+' 이미지'"
                :content="'기본으로 제공되는 '+label+' 이미지 입니다.<br>하단 스와이프뷰에서 이미지를 선택하신 후, 선택 버튼을 눌러주세요.'">
            </BaseQuestionTooltip>
            <br>
            <br>
            <div class="coverflow-example">
                <Swiper class="swiper" :modules="modules" :pagination="true" :effect="'coverflow'" :grab-cursor="true" :navigation="true" 
                    :centered-slides="true" :slides-per-view="'auto'" @swiper="getRef" :coverflow-effect="{
                        rotate: 50,
                        stretch: 0,
                        depth: 100,
                        modifier: 1,
                        slideShadows: true
                    }">
                    <SwiperSlide class="slide" :style="previewStyle" v-for="(src, key) in props.items" :key="key">
                        <VImg rounded :src="src"></VImg>
                    </SwiperSlide>
                </Swiper>
            </div>
            <div style="text-align: end;">
                <VBtn @click="emits('update:path', props.items[swiper.activeIndex])">
                    선택
                </VBtn>
            </div>
        </template>
        <span v-else>{{ label+' 이미지' }}</span>
    </VCol>
    <VCol cols="12" :md="props.rmd">
        <Preview :preview="preview" :style="`inline-size:20em !important;`" :preview-style="previewStyle" class="preview" :ext="ext" />
    </VCol>
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
    block-size: 150px;
    inline-size: 150px;

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
