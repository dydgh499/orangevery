<script setup lang="ts">
import Preview from '@/layouts/utils/Preview.vue'
import { isAbleModiy } from '@axios'
import { EffectCoverflow, Navigation, Pagination } from 'swiper'
import 'swiper/css'
import 'swiper/css/effect-coverflow'
import 'swiper/css/navigation'
import 'swiper/css/pagination'
import { Swiper, SwiperSlide } from 'swiper/vue'

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
const containerStyle = `
    max-width: ${(props.label === '로그인 배경' ? "27": "13")}em;
    margin-right: auto;margin-left: auto;
`
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
    <VCol cols="12" :md="props.lmd" style="padding: 0 2.5em;">
        <template v-if="isAbleModiy(0)">
            <div class="coverflow-example">
                <Swiper class="swiper" 
                :modules="modules" 
                :pagination="{type: 'progressbar', clickable: true}" 
                :effect="'coverflow'" 
                :grab-cursor="true" 
                :navigation="true" 
                :centered-slides="true" 
                :slides-per-view="'auto'" 
                @swiper="getRef" 
                :coverflow-effect="{
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
        <span v-else>{{ props.label+' 이미지' }}</span>
    </VCol>
    <VCol cols="12" :md="props.rmd" :style="containerStyle">
        <Preview :preview="preview" :style="`inline-size:20em !important;`" :preview-style="previewStyle" class="preview" :ext="ext" />
        <VFileInput accept="image/*" show-size v-model="files" :label="props.label+' 이미지'"
            prepend-icon=""
            prepend-inner-icon="tabler-camera-up" @change="upload()" style="padding: 0.5em;">
            <template #selection="{ fileNames }">
                <template v-for="fileName in fileNames" :key="fileName">
                    <VChip label size="small" variant="outlined" color="primary" class="me-2">
                        {{ fileName }}
                    </VChip>
                </template>
            </template>
        </VFileInput>
    </VCol>
</template>
<style lang="scss" scoped>
:deep(.swiper-wrapper) {
  margin-block-start: 1em;
}

.swiper {
  block-size: 100%;
  inline-size: 100%;
  padding-block-end: 30px;

  .slide {
    block-size: 120px;
    inline-size: 120px;

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
