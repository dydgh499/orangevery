<script setup lang="ts">
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'
import Preview from '@/layouts/utils/Preview.vue'
import { Pagination, EffectCoverflow } from 'swiper'
import { Swiper, SwiperSlide } from 'swiper/vue'
import 'swiper/css'
import 'swiper/css/pagination'
import 'swiper/css/effect-coverflow'

interface Props {
    items: any[],
    default_img: string,
    item_name: string,
    lmd: number,
    rmd: number,
}
const props = defineProps<Props>()

const files = ref(<File[]>([]))
const preview = ref(<string>(props.default_img))
const swiper = ref()
const modules = [Pagination, EffectCoverflow]
const previewStyle = `
    border: 2px solid rgb(238, 238, 238);
    border-radius: 0.5em;
    margin-block: 0;
    margin-inline: 0.5em;
`;
const emits = defineEmits(['update:file', 'update:default']);

const getRef = (swiperInstance:any) => {
    swiper.value = swiperInstance
}
console.log(preview.value)
emits('update:default', preview.value)
const setDefaultImage = () => {
    preview.value = props.items[swiper.value.activeIndex]
    emits('update:default', preview.value)
}

watchEffect(() => {
    if (files.value != undefined && files.value.length) {
        preview.value = URL.createObjectURL(files.value[0])
        emits('update:file', files.value ? files.value[0] : files.value)
    }
})
</script>
<template>
    <VCol cols="12" :md="props.lmd" style="padding: 0 0.5em;">
        <VFileInput accept="image/*" show-size v-model="files" :label="item_name+' 이미지'"
            prepend-icon="tabler-camera-up">
            <template #selection="{ fileNames }">
                <template v-for="fileName in fileNames" :key="fileName">
                    <VChip label size="small" variant="outlined" color="primary" class="me-2">
                        {{ fileName }}
                    </VChip>
                </template>
            </template>
        </VFileInput>
        <br>
        <BaseQuestionTooltip :location="'top'" :text="'기본 '+item_name+' 이미지'"
            :content="'기본으로 제공되는 '+item_name+' 이미지 입니다.<br>하단 스와이프뷰에서 이미지를 선택하신 후, 선택 버튼을 눌러주세요.'">
        </BaseQuestionTooltip>
        <br>
        <br>
        <div class="coverflow-example">
            <Swiper class="swiper" :modules="modules" :pagination="true" :effect="'coverflow'" :grab-cursor="true"
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
            <VBtn @click="setDefaultImage()">
                선택
            </VBtn>
        </div>
    </VCol>
    <VCol cols="12" :md="props.rmd">
        <Preview :preview="preview" :style="``" :preview-style="previewStyle" class="preview" />
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
