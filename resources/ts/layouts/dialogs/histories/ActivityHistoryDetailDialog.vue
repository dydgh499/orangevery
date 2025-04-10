<script setup lang="ts">
import { useRequestStore } from '@/views/request';
import { history_types, replaceHistories } from '@/views/services/activity-histories/useStore';
import { ActivityHistory } from '@/views/types';

import { EffectCoverflow, Navigation, Pagination } from 'swiper';
import 'swiper/css';
import 'swiper/css/effect-coverflow';
import 'swiper/css/navigation';
import 'swiper/css/pagination';
import { Swiper, SwiperSlide } from 'swiper/vue';

const { get } = useRequestStore()

const visible = ref(false)
const history_info = ref()
const temp_histories = ref(<ActivityHistory[]>([]))
const histories = ref(<ActivityHistory[]>([]))
const temp_history = ref(<ActivityHistory>({}))
const swiper = ref()
const hide_login = ref(false)

const store = <any>(inject('store'))
const getRef = (swiperInstance:any) => {
    swiper.value = swiperInstance
}

const show = async (item: any) => {
    history_info.value = item
    const res = await get(`/api/v1/manager/services/activity-histories/${item.user_id}/detail`, {
        params: {
            ...store.params,
            search: (document.getElementById('search') as HTMLInputElement)?.value,
            level: item.level,
        }
    })
    temp_histories.value = replaceHistories(res.data)
    visible.value = true
}

const movePage = (move_type: string) => {
    const page_size = 20
    let move_page = 0
    if(move_type === '-') {
        if(swiper.value.activeIndex - page_size < 0)
            move_page = 0
        else
            move_page = swiper.value.activeIndex - page_size 
    }
    else {
        if(swiper.value.activeIndex + page_size >= histories.value.length)
            move_page = histories.value.length - 1
        else
            move_page = swiper.value.activeIndex + page_size 
    }
    swiper.value.slideTo(move_page)
}

const isChangeColor = (before: any, after: any) => {
    if(before != after)
        return 'list-square text-error'
    else
        return 'list-square'
}

const getBackgroundcolor = (idx: number) => {
    if(swiper.value && histories.value.length) 
        return idx === swiper.value.activeIndex ? 'background: rgba(var(--v-theme-primary), 20%); height:6em !important;' : 'background: rgb(var(--v-theme-background)); height:6em !important;'
    else
        return 'background: rgb(var(--v-theme-background)); height:6em !important;'
}


let isDragging = false;
let progressBar = null;

const startDrag = (event: KeyboardEvent) => {
  isDragging = true;
  progressBar = event.target;
  updateSlideByDrag(event);
  document.addEventListener("mousemove", updateSlideByDrag);
  document.addEventListener("mouseup", stopDrag);
};

const stopDrag = () => {
  isDragging = false;
  document.removeEventListener("mousemove", updateSlideByDrag);
  document.removeEventListener("mouseup", stopDrag);
};

const updateSlideByDrag = (event: KeyboardEvent) => {
  if (isDragging && swiper.value) {
    const progressBarRect = progressBar.getBoundingClientRect();
    const progress =
      (event.clientY - progressBarRect.top) / progressBarRect.height;
    const newSlideIndex = Math.round(progress * (histories.value.length - 1));
    swiper.value.slideTo(newSlideIndex);
  }
};


watchEffect(() => {
    if(swiper.value && histories.value.length) {
        temp_history.value = histories.value[swiper.value.activeIndex]
    }
})

watchEffect(() => {
    if(hide_login.value)
        histories.value = temp_histories.value.filter(history => history.history_type !== 4)
    else
        histories.value = temp_histories.value
})

defineExpose({
    show
});
</script>
<template>
        <VDialog v-model="visible" max-width="1600">
             <VRow style="align-items: center;">
                <VCol md="4" cols="12">
                    <DialogCloseBtn @click="visible = false" />
                    <VCard :title="history_info.nick_name+`님 활동이력(총 ${histories.length}개)`">
                        <VCheckbox v-model="hide_login" class="check-label not-open-today" label="로그인 제외" 
                            style="position: absolute; top: 1em;"/>
                        <VCardText>
                            <div class="coverflow-example">
                                <Swiper class="swiper" 
                                    :direction="'vertical'"
                                    :modules="[Pagination, Navigation, EffectCoverflow]" 
                                    :watchSlidesProgress="true" 
                                    :spaceBetween="30"
                                    :pagination="{type: 'progressbar', clickable: true}" 
                                    :slides-per-view="6"
                                    :centered-slides="true"
                                    :grab-cursor="true" 
                                    @swiper="getRef">
                                    <SwiperSlide class="slide" v-for="(history, key) in histories" :key="key">
                                        <div :style="getBackgroundcolor(key)">
                                            <div style="text-align: center;">
                                                <h6 class="text-base font-weight-semibold me-3">
                                                    {{ history.history_target }}
                                                    {{ history.history_title ? " - "+history.history_title : ''}}
                                                </h6>
                                            </div>
                                            <div style="text-align: center;">
                                                <VChip
                                                    :color="store.getSelectIdColor(history_types.find(obj => obj.id === history.history_type)?.id as number)">
                                                    {{ history_types.find(obj => obj.id === history.history_type)?.title }}
                                                </VChip>
                                                <br>
                                                <b>{{ history.created_at }}</b>
                                            </div>
                                        </div>
                                    </SwiperSlide>
                                </Swiper>
                                <div class="custom-progressbar" @mousedown="startDrag">                                    
                                </div>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="text-align: center;">
                                    <b>{{ history_info.activity_s_at }}</b>
                                    <br>처음
                                </span>

                                <b v-if="swiper && histories.length > 10" 
                                    @click="movePage('-')" class="text-primary page-jump">
                                    {{ "<<" }}
                                    <VTooltip activator="parent" location="top" transition="scale-transition">
                                        <span>20페이지 이동</span>
                                    </VTooltip>
                                </b>
                                <b v-if="swiper && histories.length" class="text-primary" style="margin-inline: 0.5em;">{{ swiper.activeIndex + 1 }}</b>
                                <b v-if="swiper && histories.length > 10" 
                                    @click="movePage('+')" class="text-primary page-jump">
                                    {{ " >>" }}
                                    <VTooltip activator="parent" location="top" transition="scale-transition">
                                        <span>20페이지 이동</span>
                                    </VTooltip>
                                </b>
                                <span style="text-align: center;">                                    
                                    <b>{{ history_info.activity_e_at }}</b>
                                    <br>마지막
                                </span>
                            </div>
                        </VCardText>
                    </VCard>
                </VCol>
                <VCol md="8" cols="12">
                    <Transition :name="'fade-transition'" mode="out-in">
                        <VCard v-if="temp_history" :key="temp_history?.id">
                            <VCardText>
                                <b>
                                    <div class="d-flex justify-space-between">
                                        <h6 class="text-base font-weight-semibold me-3">
                                            {{ temp_history.history_target }}
                                            {{ temp_history.history_title ? " - "+temp_history.history_title : ''}}
                                            <VChip :color="store.getSelectIdColor(temp_history.history_type)">
                                                {{ history_types.find(history_type => history_type['id'] === temp_history.history_type)?.title  }}
                                            </VChip>   
                                        </h6>
                                        <span class="text-sm">
                                            활동시간: {{ temp_history.created_at }}
                                        </span>
                                    </div>
                                </b>
                                <br>
                                <VTable class="text-no-wrap" style="width: 100%;">
                                    <thead>
                                        <tr>
                                            <th class='list-square'>적용대상</th>
                                            <th class='list-square'>
                                                <div class="content">
                                                    이전 값
                                                </div>
                                            </th>
                                            <th class='list-square'>
                                                <div class="content">
                                                    변경 값
                                                </div>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(hist, key) in temp_history.after_history_detail" :key="key">
                                            <th class='list-square'>{{ key }}</th>
                                            <td class='list-square'>
                                                <div class="content">
                                                    {{ temp_history.before_history_detail[key] }}
                                                    <VTooltip activator="parent" location="top" transition="scale-transition">
                                                        <span>{{ temp_history.before_history_detail[key] }}</span>
                                                    </VTooltip>
                                                </div>
                                            </td>
                                            <td :class="isChangeColor(temp_history.before_history_detail[key], hist)">
                                                <div class="content">
                                                    {{ hist }}
                                                    <VTooltip activator="parent" location="top" transition="scale-transition">
                                                        <span>{{ hist }}</span>
                                                    </VTooltip>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </VTable>
                            </VCardText>
                        </VCard>
                    </Transition>
                </VCol>
            </VRow>
        </VDialog>
</template>

<style scoped>
.page-jump {
  cursor: pointer;
  user-select: none;
}

.content {
  overflow: hidden;
  inline-size: 200px !important;
  text-overflow: ellipsis;
  white-space: nowrap;
  word-break: break-all;
}

.swiper {
  padding: 0.5em;
  block-size: 38em;
}

.custom-progressbar {
  position: absolute;
  z-index: 9999;
  background-color: rgba(0, 0, 0, 0%);
  block-size: 75%;
  cursor: pointer;
  inline-size: 5px;
  inset-block-start: 6.5em;
  user-select: none;
}
</style>
