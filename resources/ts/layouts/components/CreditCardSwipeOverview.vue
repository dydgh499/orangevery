<script setup lang="ts">
import { EffectCards } from 'swiper'
import { Swiper, SwiperSlide } from 'swiper/vue'
import type { Merchandise } from '@/views/types'
import 'swiper/css'
import 'swiper/css/effect-cards';
interface Props {
    merchandise: Merchandise
}
const props = defineProps<Props>()
const snackbar = <any>(inject('snackbar'))

const swiper = ref()
const emits = defineEmits(['update:card_num']);

const getRef = (swiperInstance: any) => {
    swiper.value = swiperInstance
}
const setCardNum = () => {
    const regular_credit_card = props.merchandise.regular_credit_cards[swiper.value.activeIndex]
    emits('update:card_num', regular_credit_card.card_num)
    snackbar.value.show('카드를 선택하셨습니다.(' + regular_credit_card.note + ')', 'success')
}
</script>

<template>
    <div style="margin: 1em;" v-if="props.merchandise.regular_credit_cards?.length">
        <Swiper class="swiper" :modules="[EffectCards]" :pagination="true" :effect="'cards'" :grab-cursor="true"
            @swiper="getRef">
            <SwiperSlide class="slide" v-for="(regular_credit_card, key) in props.merchandise.regular_credit_cards"
                :key="key">
                <div style="text-align: center;">
                    <div class="card-item__top">
                        <img src="https://raw.githubusercontent.com/muhammederdem/credit-card-form/master/src/assets/images/chip.png"
                            class="card-mark">
                    </div>
                    <VChip size="default" variant="elevated" label>
                        <span v-if="regular_credit_card.card_num.length >= 16">
                            {{ regular_credit_card.card_num.slice(0, 4) + " **** **** " +
                                regular_credit_card.card_num.slice(12, 16)
                            }}
                        </span>
                        <span v-else>
                            {{ regular_credit_card.card_num }}
                        </span>
                    </VChip>
                    <br>
                    <VChip size="default" variant="elevated" label style="position: relative; top: 1.5em;">
                        {{ regular_credit_card.note }}
                    </VChip>
                </div>
            </SwiperSlide>
        </Swiper>
        <div color="success" variant="tonal" style="margin: 1em 0;text-align: end;">
            <VBtn @click="setCardNum()">카드선택</VBtn>
        </div>
    </div>
    <div v-else style="margin: 1em; text-align: center;">선택 가능한 카드가 존재하지 않습니다.</div>
</template>

<style>
.card-mark {
  position: absolute;
  block-size: 30px;
  inline-size: 30px;
  inset-block-start: 0.5em;
  inset-inline-start: 1em;
}

.swiper {
  block-size: 150px;
  inline-size: 240px;
}

.swiper-slide {
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 12px;
  color: #fff;
  font-size: 22px;
  font-weight: bold;
}

.swiper-slide:nth-child(1n) {
  background-image: url("https://raw.githubusercontent.com/muhammederdem/credit-card-form/master/src/assets/images/3.jpeg");
}

.swiper-slide:nth-child(2n) {
  background-image: url("https://raw.githubusercontent.com/muhammederdem/credit-card-form/master/src/assets/images/4.jpeg");
}

.swiper-slide:nth-child(3n) {
  background-image: url("https://raw.githubusercontent.com/muhammederdem/credit-card-form/master/src/assets/images/5.jpeg");
}

.swiper-slide:nth-child(4n) {
  background-image: url("https://raw.githubusercontent.com/muhammederdem/credit-card-form/master/src/assets/images/6.jpeg");
}

.swiper-slide:nth-child(5n) {
  background-image: url("https://raw.githubusercontent.com/muhammederdem/credit-card-form/master/src/assets/images/7.jpeg");
}

.swiper-slide:nth-child(6n) {
  background-image: url("https://raw.githubusercontent.com/muhammederdem/credit-card-form/master/src/assets/images/8.jpeg");
}

.swiper-slide:nth-child(7n) {
  background-image: url("https://raw.githubusercontent.com/muhammederdem/credit-card-form/master/src/assets/images/9.jpeg");
}

.swiper-slide:nth-child(8n) {
  background-image: url("https://raw.githubusercontent.com/muhammederdem/credit-card-form/master/src/assets/images/10.jpeg");
}

.swiper-slide:nth-child(9n) {
  background-image: url("https://raw.githubusercontent.com/muhammederdem/credit-card-form/master/src/assets/images/11.jpeg");
}

.swiper-slide:nth-child(10n) {
  background-image: url("https://raw.githubusercontent.com/muhammederdem/credit-card-form/master/src/assets/images/12.jpeg");
}
</style>

