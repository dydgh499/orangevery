<script setup lang="ts">
import { useRequestStore } from '@/views/request';
import { useSalesFilterStore } from '@/views/salesforces/useStore';
import { history_types } from '@/views/services/operator-histories/useStore';
import { useStore } from '@/views/services/pay-gateways/useStore';
import { OperatorHistory } from '@/views/types';
import corp from '@corp';

import { EffectCoverflow, Navigation, Pagination } from 'swiper';
import 'swiper/css';
import 'swiper/css/effect-coverflow';
import 'swiper/css/navigation';
import 'swiper/css/pagination';
import { Swiper, SwiperSlide } from 'swiper/vue';

const { get } = useRequestStore()

const visible = ref(false)
const history_info = ref()
const temp_histories = ref(<OperatorHistory[]>([]))
const histories = ref(<OperatorHistory[]>([]))
const temp_history = ref(<OperatorHistory>({}))
const swiper = ref()
const hide_login = ref(false)

const store = <any>(inject('store'))
const { mchts, all_sales } = useSalesFilterStore()
const { pgs, pss, settle_types, terminals, finance_vans } = useStore()

let is_dragging = false;
let progress_bar = <any>(null);

const startDrag = (event: KeyboardEvent) => {
  is_dragging = true;
  progress_bar = event.target
  updateSlideByDrag(event)
  document.addEventListener("mousemove", updateSlideByDrag)
  document.addEventListener("mouseup", stopDrag)
}

const stopDrag = () => {
  is_dragging = false;
  document.removeEventListener("mousemove", updateSlideByDrag)
  document.removeEventListener("mouseup", stopDrag)
}

const getRef = (swiperInstance:any) => {
    swiper.value = swiperInstance
}

const updateSlideByDrag = (event: KeyboardEvent) => {
  if (is_dragging && swiper.value) {
    const progress_bar_rect = progress_bar.getBoundingClientRect()
    const progress = (event.clientY - progress_bar_rect.top) / progress_bar_rect.height
    const newSlideIndex = Math.round(progress * (histories.value.length - 1));
    swiper.value.slideTo(newSlideIndex);
  }
}

const changeKeyName = (history_detail: any) => {
    const keys = [
        'sales0_id','sales1_id','sales2_id','sales3_id','sales4_id','sales5_id',    
        'sales0_fee','sales1_fee','sales2_fee','sales3_fee','sales4_fee','sales5_fee',
        'sales0_settle_amount','sales1_settle_amount','sales2_settle_amount',
        'sales3_settle_amount','sales4_settle_amount','sales5_settle_amount',
    ]
    keys.forEach((key) => {
        if("validation.attributes." + key in history_detail) {
            const level = key.slice(0, 6)
            let key_name = corp.pv_options.auth.levels[level+'_name']
            if(key.includes('fee')) {
                key_name += ' 수수료';
                history_detail['validation.attributes.'+key] *= 100
            }
            else if(key.includes('_settle_amount')) {
                key_name += ' 정산금';
            }
            history_detail[key_name] =  history_detail['validation.attributes.'+key]
            delete history_detail['validation.attributes.'+key]
        }
    })
    replaceIdtoName(history_detail)
    return history_detail
}

const replaceIdtoName = (history_detail: any) => {
    const levels = corp.pv_options.auth.levels
    const _replaceToName = (lists: any[], key: string, name: string) => {
        if(key in history_detail) {
            const value = lists.find(obj => obj.id == history_detail[key])
            history_detail[key] = value ? value[name] : history_detail[key]
        }
    }
    _replaceToName(pgs, "PG사", 'pg_name')
    _replaceToName(pss, "구간", 'name')
    _replaceToName(mchts, "가맹점", 'mcht_name')
    _replaceToName(terminals, "장비", 'name')
    _replaceToName(settle_types, "정산일", 'name')    
    _replaceToName(finance_vans, "금융벤 ID", 'nick_name')

    _replaceToName(all_sales[0], levels.sales0_name, 'sales_name')
    _replaceToName(all_sales[1], levels.sales1_name, 'sales_name')
    _replaceToName(all_sales[2], levels.sales2_name, 'sales_name')
    _replaceToName(all_sales[3], levels.sales3_name, 'sales_name')
    _replaceToName(all_sales[4], levels.sales4_name, 'sales_name')
    _replaceToName(all_sales[5], levels.sales5_name, 'sales_name')
}

const show = async (item: any) => {
    history_info.value = item
    const res = await get(`/api/v1/manager/services/operator-histories/${item.oper_id}/detail`, {
        params: {
            ...store.params,
            search: (document.getElementById('search') as HTMLInputElement)?.value,
        }
    })
    temp_histories.value = res.data
    for (let i = 0; i < temp_histories.value.length; i++) {
        temp_histories.value[i].before_history_detail = changeKeyName(temp_histories.value[i].before_history_detail)
        temp_histories.value[i].after_history_detail = changeKeyName(temp_histories.value[i].after_history_detail)
        if(temp_histories.value[i].history_target === '구분 정보') {
            if(temp_histories.value[i].before_history_detail['타입'] === 0) {
                delete temp_histories.value[i].after_history_detail['타입']
                delete temp_histories.value[i].before_history_detail['타입']
                temp_histories.value[i].after_history_detail['구분 타입'] = '장비'
                temp_histories.value[i].before_history_detail['구분 타입'] = '장비'
            }
            else if(temp_histories.value[i].before_history_detail['타입'] === 1) {
                delete temp_histories.value[i].after_history_detail['타입']
                delete temp_histories.value[i].before_history_detail['타입']
                temp_histories.value[i].after_history_detail['구분 타입'] = '커스텀 필터'
                temp_histories.value[i].before_history_detail['구분 타입'] = '커스텀 필터'
            }
        }
        else if(corp.pv_options.paid.use_issuer_filter && temp_histories.value[i].history_target === '결제모듈')
            temp_histories.value[i].before_history_detail['카드사 필터'] = JSON.stringify(temp_histories.value[i].before_history_detail['카드사 필터'])
    }
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
                                    :pagination="{type: 'progress_bar', clickable: true}" 
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
                                <div class="custom-progress-bar" @mousedown="startDrag">                                    
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

.custom-progress-bar {
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
