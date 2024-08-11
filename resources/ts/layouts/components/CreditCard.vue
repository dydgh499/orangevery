<script setup lang="ts">
interface Props {
    card_num: string,
    auth_num: string,
    yymm: string,
    is_old_auth: boolean
}
const props = withDefaults(defineProps<Props>(), {
    card_num: '',
    auth_num: '',
    yymm: '',
})

const cur_card_background = ref(Math.floor(Math.random() * 25 + 1));

const auth_num = ref(props.auth_num)
const card_number = ref(props.card_num)
const card_month = ref('')
const card_year = ref("")

const otherCardMask = "################";

const getCardType = computed(() => {
    let number = card_number.value;
    let re: RegExp;

    re = new RegExp("^4");
    if (number.match(re) != null) return "visa";

    re = new RegExp("^(34|37)");
    if (number.match(re) != null) return "amex";

    re = new RegExp("^5[1-5]");
    if (number.match(re) != null) return "mastercard";

    re = new RegExp("^6011");
    if (number.match(re) != null) return "discover";

    re = new RegExp('^9792');
    if (number.match(re) != null) return 'troy';

    return "visa";
});

watchEffect(() => {
    card_month.value = props.yymm.length >= 2 ? props.yymm.slice(0, 2) : ''
    card_year.value = props.yymm.length == 4 ? props.yymm.slice(2, 4) : ''
    card_number.value = props.card_num
    auth_num.value = props.auth_num
})

</script>
<template>
    <div class="card-form">
        <div class="card-list">
            <div class="card-item">
                <div class="card-item__side -front">
                    <div class="card-item__focus" ref="focusElement"></div>
                    <div class="card-item__cover">
                        <img v-bind:src="'https://raw.githubusercontent.com/muhammederdem/credit-card-form/master/src/assets/images/' + cur_card_background + '.jpeg'"
                            class="card-item__bg">
                    </div>
                    <div class="card-item__wrapper">
                        <div class="card-item__top">
                            <img src="https://raw.githubusercontent.com/muhammederdem/credit-card-form/master/src/assets/images/chip.png"
                                class="card-item__chip">
                            <div class="card-item__type">
                                <transition name="slide-fade-up">
                                    <img v-bind:src="'https://raw.githubusercontent.com/muhammederdem/credit-card-form/master/src/assets/images/' + getCardType + '.png'"
                                        v-if="getCardType" v-bind:key="getCardType" alt="" class="card-item__typeImg">
                                </transition>
                            </div>
                        </div>
                        <label for="cardNumber" class="card-item__number" ref="cardNumber">
                            <span v-for="(n, $index) in otherCardMask" :key="$index">
                                <span v-if="$index === 4 || $index === 8 || $index === 12" style="margin-left: 1em;">
                                </span>
                                <transition name="slide-fade-up">
                                    <!-- 6 ~ 12 까지 마스킹 -->
                                    <div class="card-item__numberItem"
                                        v-if="$index > 5 && $index < 12 && card_number.length > $index && n.trim() !== ''">
                                        *</div>
                                    <!-- 카드번호 길이가 index보다 길으면 ?-->
                                    <div class="card-item__numberItem" :class="{ '-active': n.trim() === '' }"
                                        :key="$index" v-else-if="card_number.length > $index">
                                        {{ card_number[$index] }}
                                    </div>
                                    <!-- 아직 입력되지 않은 자리들 ?-->
                                    <div class="card-item__numberItem" :class="{ '-active': n.trim() === '' }" v-else
                                        :key="$index + 1">{{ n }}</div>
                                </transition>
                            </span>
                        </label>
                        <div class="card-item__content">
                            <label for="cardName" class="card-item__info" ref="cardName" v-if="props.is_old_auth">
                                <div class="card-item__holder">본인확인</div>
                                <transition name="slide-fade-up">
                                    <div class="card-item__name" v-if="auth_num.length" key="1">
                                        <transition-group name="slide-fade-right">
                                            <span class="card-item__nameItem"
                                                v-for="(n, $index) in auth_num.replace(/\s\s+/g, ' ')"
                                                v-bind:key="$index + 1">{{ n }}</span>
                                        </transition-group>
                                    </div>
                                    <div class="card-item__name" v-else key="2">생년월일 또는 사업자번호</div>
                                </transition>
                            </label>
                            <div class="card-item__date" ref="cardDate">
                                <label for="cardMonth" class="card-item__dateTitle">유효기간</label>
                                <label for="cardMonth" class="card-item__dateItem">
                                    <transition name="slide-fade-up">
                                        <span v-if="card_month" v-bind:key="card_month">{{ card_month }}</span>
                                        <span v-else key="2">MM</span>
                                    </transition>
                                </label>
                                /
                                <label for="cardYear" class="card-item__dateItem">
                                    <transition name="slide-fade-up">
                                        <span v-if="card_year" v-bind:key="card_year">{{ card_year }}</span>
                                        <span v-else key="2">YY</span>
                                    </transition>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<style lang="scss" scoped>
@import "https://fonts.googleapis.com/css?family=Source+Code+Pro:400,500,600,700|Source+Sans+Pro:400,600,700&display=swap";

.card-form {
  margin: auto;

  @media screen and (max-width: 576px) {
    margin-block: 0;
    margin-inline: auto;
  }

  &__row {
    display: flex;
    align-items: flex-start;

    @media screen and (max-width: 480px) {
      flex-wrap: wrap;
    }
  }

  &__col {
    flex: auto;
    margin-inline-end: 35px;

    &:last-child {
      margin-inline-end: 0;
    }

    @media screen and (max-width: 480px) {
      flex: unset;
      inline-size: 100%;
      margin-block-end: 20px;
      margin-inline-end: 0;

      &:last-child {
        margin-block-end: 0;
      }
    }

    &.-cvv {
      max-inline-size: 150px;

      @media screen and (max-width: 480px) {
        max-inline-size: initial;
      }
    }
  }

  &__group {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-start;

    .card-input__input {
      flex: 1;
      margin-inline-end: 15px;

      &:last-child {
        margin-inline-end: 0;
      }
    }
  }

  &__button {
    border: none;
    border-radius: 5px;
    background: #2364d2;
    block-size: 55px;
    color: #fff;
    cursor: pointer;
    font-family: "Source Sans Pro", sans-serif;
    font-size: 22px;
    font-weight: 500;
    inline-size: 100%;
    margin-block-start: 20px;

    @media screen and (max-width: 480px) {
      margin-block-start: 10px;
    }
  }
}

.card-item {
  block-size: 210px;
  margin-inline: auto;
  max-inline-size: 380px;

  @media screen and (max-width: 480px) {
    block-size: 210px;
    max-inline-size: 310px;
  }

  @media screen and (max-width: 360px) {
    block-size: 180px;
  }

  &.-active {
    .card-item__side {
      &.-front {
        transform: perspective(1000px) rotateY(180deg) rotateX(0deg) rotateZ(0deg);
      }

      &.-back {
        transform: perspective(1000px) rotateY(0) rotateX(0deg) rotateZ(0deg);

        // box-shadow: 0 20px 50px 0 rgba(81, 88, 206, 0.65);
      }
    }
  }

  &__focus {
    position: absolute;
    z-index: 3;
    overflow: hidden;
    border: 2px solid rgba(255, 255, 255, 65%);
    border-radius: 5px;
    block-size: 100%;
    inline-size: 100%;
    inset-block-start: 0;
    inset-inline-start: 0;
    opacity: 0;
    pointer-events: none;
    transition: all 0.35s cubic-bezier(0.71, 0.03, 0.56, 0.85);

    &::after {
      position: absolute;
      border-radius: 5px;
      background: rgb(8, 20, 47);
      block-size: 100%;
      content: "";
      filter: blur(25px);
      inline-size: 100%;
      inset-block-start: 0;
      inset-inline-start: 0;
      opacity: 0.5;
    }

    &.-active {
      opacity: 1;
    }
  }

  &__side {
    overflow: hidden;
    border-radius: 15px;
    backface-visibility: hidden;
    block-size: 100%;
    box-shadow: 0 5px 20px 0 rgba(14, 42, 90, 55%);
    transform: perspective(2000px) rotateY(0deg) rotateX(0deg) rotate(0deg);
    transform-style: preserve-3d;
    transition: all 0.8s cubic-bezier(0.71, 0.03, 0.56, 0.85);

    &.-back {
      position: absolute;
      z-index: 2;
      padding: 0;
      block-size: 100%;
      inline-size: 100%;
      inset-block-start: 0;
      inset-inline-start: 0;
      transform: perspective(2000px) rotateY(-180deg) rotateX(0deg) rotate(0deg);

      .card-item__cover {
        transform: rotateY(-180deg);
      }
    }
  }

  &__bg {
    display: block;
    block-size: 100%;
    inline-size: 100%;
    max-block-size: 100%;
    max-inline-size: 100%;
    object-fit: cover;
  }

  &__cover {
    position: absolute;
    overflow: hidden;
    border-radius: 15px;
    background-color: #1c1d27;
    block-size: 100%;
    inline-size: 100%;
    inset-block-start: 0;
    inset-inline-start: 0;

    &::after {
      position: absolute;
      background: rgba(6, 2, 29, 45%);
      block-size: 100%;
      content: "";
      inline-size: 100%;
      inset-block-start: 0;
      inset-inline-start: 0;
    }
  }

  &__top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-block-end: 35px;
    padding-block: 0;
    padding-inline: 10px;

    @media screen and (max-width: 480px) {
      margin-block-end: 20px;
    }

    @media screen and (max-width: 360px) {
      margin-block-end: 15px;
    }
  }

  &__chip {
    inline-size: 45px;

    @media screen and (max-width: 480px) {
      inline-size: 45px;
    }

    @media screen and (max-width: 360px) {
      inline-size: 40px;
    }
  }

  &__type {
    position: relative;
    display: flex;
    justify-content: flex-end;
    block-size: 40px;
    inline-size: 100%;
    margin-inline-start: auto;
    max-inline-size: 100px;

    @media screen and (max-width: 480px) {
      block-size: 40px;
      max-inline-size: 90px;
    }

    @media screen and (max-width: 360px) {
      block-size: 30px;
    }
  }

  &__typeImg {
    max-block-size: 100%;
    max-inline-size: 100%;
    object-fit: contain;
    object-position: top right;
  }

  &__info {
    display: block;
    color: #fff;
    cursor: pointer;
    font-weight: 500;
    inline-size: 100%;
    max-inline-size: calc(100% - 85px);
    padding-block: 10px;
    padding-inline: 15px;

    @media screen and (max-width: 480px) {
      padding: 10px;
    }
  }

  &__holder {
    font-size: 13px;
    margin-block-end: 5px;
    opacity: 0.7;

    @media screen and (max-width: 480px) {
      font-size: 12px;
      margin-block-end: 5px;
    }
  }

  &__wrapper {
    position: relative;
    z-index: 4;
    block-size: 100%;
    font-family: "Source Code Pro", monospace;
    padding-block: 20px;
    padding-inline: 15px;
    user-select: none;

    @media screen and (max-width: 480px) {
      padding-block: 20px;
      padding-inline: 10px;
    }
  }

  &__name {
    overflow: hidden;
    max-inline-size: 100%;
    text-overflow: ellipsis;
    text-transform: uppercase;
    white-space: nowrap;

    @media screen and (max-width: 480px) {
      font-size: 16px;
    }
  }

  &__nameItem {
    position: relative;
    display: inline-block;
    min-inline-size: 8px;
  }

  &__number {
    display: inline-block;
    color: #fff;
    cursor: pointer;
    font-size: 20px;
    font-weight: 500;
    inline-size: 100%;
    line-height: 1;
    text-align: center;

    @media screen and (max-width: 480px) {
      padding-block: 10px;
      padding-inline: 10px;
    }

    @media screen and (max-width: 360px) {
      font-size: 19px;
      padding-block: 10px;
      padding-inline: 10px;
    }
  }

  &__numberItem {
    display: inline-block;
    inline-size: 12px;

    &.-active {
      inline-size: 30px;
    }

    @media screen and (max-width: 480px) {
      inline-size: 13px;

      &.-active {
        inline-size: 15px;
      }
    }

    @media screen and (max-width: 360px) {
      inline-size: 12px;

      &.-active {
        inline-size: 8px;
      }
    }
  }

  &__content {
    display: flex;
    align-items: flex-start;
    color: #fff;
    margin-block-start: 1em;
  }

  &__date {
    display: inline-flex;
    flex-shrink: 0;
    flex-wrap: wrap;
    padding: 10px;
    cursor: pointer;
    font-size: 18px;
    inline-size: 82px;
    margin-inline-start: auto;
    white-space: nowrap;

    @media screen and (max-width: 480px) {
      font-size: 16px;
    }
  }

  &__dateItem {
    position: relative;

    span {
      display: inline-block;
      inline-size: 22px;
    }
  }

  &__dateTitle {
    font-size: 13px;
    inline-size: 100%;
    opacity: 0.7;

    @media screen and (max-width: 480px) {
      font-size: 12px;
      padding-block-end: 5px;
    }
  }
}

.slide-fade-up-enter-active {
  position: relative;
  transition: all 0.25s ease-in-out;
  transition-delay: 0.1s;
}

.slide-fade-up-leave-active {
  position: absolute;
  transition: all 0.25s ease-in-out;
}

.slide-fade-up-enter {
  opacity: 0;
  pointer-events: none;
  transform: translateY(15px);
}

.slide-fade-up-leave-to {
  opacity: 0;
  pointer-events: none;
  transform: translateY(-15px);
}

.slide-fade-right-enter-active {
  position: relative;
  transition: all 0.25s ease-in-out;
  transition-delay: 0.1s;
}

.slide-fade-right-leave-active {
  position: absolute;
  transition: all 0.25s ease-in-out;
}

.slide-fade-right-enter {
  opacity: 0;
  pointer-events: none;
  transform: translateX(10px) rotate(45deg);
}

.slide-fade-right-leave-to {
  opacity: 0;
  pointer-events: none;
  transform: translateX(-10px) rotate(45deg);
}
</style>
