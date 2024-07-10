<script setup lang="ts">

import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue';
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue';
import CreateHalfVColV2 from '@/layouts/utils/CreateHalfVColV2.vue';
import { getUserLevel } from '@/plugins/axios';
import { abnormal_trans_limits, installments } from '@/views/merchandises/pay-modules/useStore';
import type { AuthOption, FreeOption, Options, PaidOption } from '@/views/types';
import corp from '@corp';

interface Props {
    item: {
        free: FreeOption,
        paid: PaidOption,
        auth: AuthOption,
    },
}
const props = defineProps<Props>()

const mchtIdLevels = <Options[]>([
    {id: 0, title: '1단계(검증없음)'},
    {id: 1, title: '2단계(8자이상)'},
])
const mchtPwLevels = <Options[]>([
    {id: 0, title: '1단계(검증없음)'},
    {id: 1, title: '2단계(8자이상)'},
    {id: 2, title: '3단계(8자이상+대소문자,특수문자포함)'},
])
const accountLockLimits = <Options[]>([
    {id: 3, title: '3회(기본)'},
    {id: 4, title: '4회'},
    {id: 5, title: '5회'},
    {id: 6, title: '6회'},
    {id: 7, title: '7회'},
])
// 화면 타입은 영업점 개별 선택
</script>
<template>
    <VRow class="match-height">
        <VCol cols="12" :md="6">
            <VCard>
                <VCardItem>
                    <VCardTitle>결제 사용여부</VCardTitle>
                    <VRow class="pt-5">
                        <VCol md="4">
                            <VRow style="align-items: center;">
                                <VCol :md="6">수기결제</VCol>
                                <VCol :md="6">
                                    <VSwitch hide-details v-model="props.item.free.use_hand_pay" color="primary" />
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol md="4">
                            <VRow style="align-items: center;">
                                <VCol :md="6">인증결제</VCol>
                                <VCol :md="6">
                                    <VSwitch hide-details v-model="props.item.free.use_auth_pay" color="primary" />
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol md="4">
                            <VRow style="align-items: center;">
                                <VCol :md="6">간편결제</VCol>
                                <VCol :md="6">
                                <VSwitch hide-details v-model="props.item.free.use_simple_pay" color="primary" />
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                    <VCardTitle class="pt-10">기능 옵션</VCardTitle>
                    <CreateHalfVColV2 :mdl="6" :mdr="6" class="pt-5">
                        <template #l_name>MID 중복검사 사용</template>
                        <template #l_input>
                            <VSwitch hide-details v-model="props.item.free.use_mid_duplicate" color="primary" />
                        </template>
                        <template #r_name>TID 중복검사 사용</template>
                        <template #r_input>
                            <VSwitch hide-details v-model="props.item.free.use_tid_duplicate" color="primary" />
                        </template>
                    </CreateHalfVColV2>
                    <CreateHalfVColV2 :mdl="6" :mdr="6">
                        <template #l_name>
                            <BaseQuestionTooltip location="top" text="검색옵션 자동 초기화"
                                    :content="`목록 페이지에서 선택한 검색옵션들이 메뉴 이동시 자동으로 초기화됩니다.`"/>
                        </template>
                        <template #l_input>
                                <VSwitch hide-details v-model="props.item.free.init_search_filter" color="primary" />
                        </template>
                        <template #r_name>
                            <BaseQuestionTooltip location="top" text="결제모듈 상세보기" :content="`가맹점 정보 - 결제모듈 정보에서 결제모듈이 리스트로 표현됩니다.<br>(가맹점당 결제모듈 여러개 보유시 사용)`"/>
                        </template>
                        <template #r_input>
                            <VSwitch hide-details v-model="props.item.free.pay_module_detail_view" color="primary" />
                        </template>
                    </CreateHalfVColV2>

                    <VCardTitle class="pt-10">화면 옵션</VCardTitle>
                    <CreateHalfVColV2 :mdl="6" :mdr="6" class="pt-5">
                        <template #l_name>    
                            <BaseQuestionTooltip location="top" text="날짜 상세조회" :content="`(연-월-일 ~ 연-월-일) 조회방식에서<br>(연-월-일 시:분:초 ~ 연-월-일 시:분:초) 방식으로 조회합니다.`"/>
                        </template>
                        <template #l_input>
                            <VSwitch hide-details v-model="props.item.free.use_search_date_detail" color="primary" />
                        </template>
                        <template #r_name>
                            <BaseQuestionTooltip location="top" text="주민번호 뒷자리 숨김" :content="`전산내 표기되는 모든 주민번호뒷자리가 * 표시로 마스킹되어 표시됩니다.`"/>
                        </template>
                        <template #r_input>
                            <VSwitch hide-details v-model="props.item.free.resident_num_masking" color="primary" />
                        </template>
                    </CreateHalfVColV2>

                    <CreateHalfVColV2 :mdl="6" :mdr="6">
                        <template #l_name>
                            <BaseQuestionTooltip location="top" text="고정 테이블 사용" :content="`리스트 화면에서 테이블 헤더가 고정됩니다.`"/>
                        </template>
                        <template #l_input>
                            <VSwitch hide-details v-model="props.item.free.use_fix_table_view"
                                color="primary" />
                        </template>
                        <template #r_name>고정 테이블 사이즈</template>
                        <template #r_input>
                            <VTextField v-model="props.item.free.fix_table_size" placeholder="사이즈 입력"
                                        type="number" suffix="px" v-if="props.item.free.use_fix_table_view" />
                        </template>
                    </CreateHalfVColV2>

                    <VCardTitle class="pt-10">                        
                        보안 옵션<span><b class="text-error" style="font-size: 0.6em;"> (보안 옵션 해제로인한 금융사고는 책임지지 않습니다.)</b></span>
                    </VCardTitle>
                    <CreateHalfVColV2 :mdl="6" :mdr="6" class="pt-5">
                        <template #l_name>
                            <BaseQuestionTooltip location="top" text="가맹점 ID 입력 난이도" :content="`가맹점 ID 추가/수정시 요구되는 검증 난이도입니다.`"/>
                        </template>
                        <template #l_input>
                            <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.free.secure.mcht_id_level"
                                :items="mchtIdLevels" prepend-inneer-icon="fluent-credit-card-clock-20-regular"
                                label="ID 난이도 선택" item-title="title" item-value="id" single-line />
                        </template>
                        <template #r_name>
                            <BaseQuestionTooltip location="top" text="가맹점 PW 입력 난이도" :content="`가맹점 패스워드 추가/수정시 요구되는 검증 난이도입니다.`"/>
                        </template>
                        <template #r_input>
                            <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.free.secure.mcht_pw_level"
                                :items="mchtPwLevels" prepend-inneer-icon="fluent-credit-card-clock-20-regular"
                                label="패스워드 난이도 선택"  item-title="title" item-value="id" single-line />
                        </template>
                    </CreateHalfVColV2>
                    <CreateHalfVColV2 :mdl="6" :mdr="6">
                        <template #l_name>
                            <BaseQuestionTooltip location="top" text="영업점 ID 입력 난이도" :content="`영업점 ID 추가/수정시 요구되는 검증 난이도입니다.`"/>
                        </template>
                        <template #l_input>
                            <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.free.secure.sales_id_level"
                                :items="mchtIdLevels" prepend-inneer-icon="fluent-credit-card-clock-20-regular"
                                label="ID 난이도 선택" item-title="title" item-value="id" single-line />
                        </template>
                        <template #r_name>
                            <BaseQuestionTooltip location="top" text="영업점 PW 입력 난이도" :content="`영업점 패스워드 추가/수정시 요구되는 검증 난이도입니다.`"/>
                        </template>
                        <template #r_input>
                            <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.free.secure.sales_pw_level"
                                :items="mchtPwLevels" prepend-inneer-icon="fluent-credit-card-clock-20-regular"
                                label="패스워드 난이도 선택"  item-title="title" item-value="id" single-line />
                        </template>
                    </CreateHalfVColV2>
                    <CreateHalfVColV2 :mdl="6" :mdr="6">
                        <template #l_name>
                            <BaseQuestionTooltip location="top" text="패스워드 허용회수" :content="`로그인시 패스워드 오입력으로 계정이 잠금되는 시도회수를 설정합니다.`"/>
                        </template>
                        <template #l_input>
                            <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.free.secure.account_lock_limit"
                                :items="accountLockLimits" prepend-inneer-icon="fluent-credit-card-clock-20-regular"
                                label="가맹점 패스워드 허용회수" item-title="title" item-value="id" single-line />
                        </template>
                        <template #r_name>
                        </template>
                        <template #r_input>
                        </template>
                    </CreateHalfVColV2>
                </VCardItem>
            </VCard>
        </VCol>
        <VCol cols="12" :md="6">
            <VCard>
                <VCardItem>
                    <VCardTitle>
                        <BaseQuestionTooltip location="top" text="매출전표 가맹점표기 정보"
                            :content="`가맹점 옵션중 매출전표 가맹점표기 정보(PG/본사)를 본사로 설정할 시<br>매출전표에서 하단 정보들이 보여집니다.`">
                        </BaseQuestionTooltip>
                    </VCardTitle>
                    <CreateHalfVColV2 :mdl="5" :mdr="7" class="pt-5">
                        <template #l_name>회사명</template>
                        <template #l_input>
                            <VTextField  prepend-inner-icon="ph-buildings"
                                v-model="props.item.free.sales_slip.merchandise.company_name" placeholder="회사명 입력"
                                type="text" />
                        </template>
                        <template #r_name>대표자명</template>
                        <template #r_input>
                                <VTextField prepend-inner-icon="tabler-user"
                                    v-model="props.item.free.sales_slip.merchandise.rep_name" placeholder="대표자명 입력"
                                    type="text" />
                        </template>
                    </CreateHalfVColV2>
                    <CreateHalfVColV2 :mdl="5" :mdr="7">
                        <template #l_name>연락처</template>
                        <template #l_input>
                                <VTextField prepend-inner-icon="tabler-device-mobile"
                                    v-model="props.item.free.sales_slip.merchandise.phone_num" placeholder="연락처 입력"
                                    type="text" />
                        </template>
                        <template #r_name>사업자등록번호</template>
                        <template #r_input>
                                <VTextField prepend-inner-icon="ic-outline-business-center" 
                                    v-model="props.item.free.sales_slip.merchandise.business_num"
                                    placeholder="사업자등록번호 입력" type="text" />
                        </template>
                    </CreateHalfVColV2>
                    <CreateHalfVColV2 :mdl="5" :mdr="7">
                        <template #l_name>주소</template>
                        <template #l_input>
                                <VTextField prepend-inner-icon="tabler-map-pin"
                                    v-model="props.item.free.sales_slip.merchandise.addr" placeholder="주소 입력"
                                    type="text" />
                        </template>
                        <template #r_name></template>
                        <template #r_input>
                        </template>
                    </CreateHalfVColV2>

                    <div>
                        <VCardTitle class="pt-10">
                            <BaseQuestionTooltip :location="'top'" text="문자 발송정보" content="전산내 문자발송 서비스에 사용됩니다." />
                        </VCardTitle>

                        <CreateHalfVColV2 :mdl="5" :mdr="7" class="pt-5">
                            <template #l_name>회원 ID</template>
                            <template #l_input>
                                    <VTextField prepend-inner-icon="tabler-user" v-model="props.item.free.bonaeja.user_id"
                                        placeholder="대표자명을 입력해주세요." type="text" />
                            </template>
                            <template #r_name>API KEY</template>
                            <template #r_input>
                                <VTextField prepend-inner-icon="ic-baseline-vpn-key"
                                    v-model="props.item.free.bonaeja.api_key" placeholder="API KEY를 입력해주세요."
                                    type="text" />
                            </template>
                        </CreateHalfVColV2>
                        <CreateHalfVColV2 :mdl="5" :mdr="7">
                            <template #l_name>발신자번호</template>
                            <template #l_input>
                                    <VTextField prepend-inner-icon="tabler-device-mobile"
                                        v-model="props.item.free.bonaeja.sender_phone" placeholder="연락처를 입력해주세요."
                                        type="text" />
                            </template>
                            <template #r_name></template>
                            <template #r_input>
                            </template>
                        </CreateHalfVColV2>
                        
                        <CreateHalfVColV2 :mdl="5" :mdr="7">
                            <template #l_name>
                                <BaseQuestionTooltip :location="'top'" text="포인트 하한금"
                                    content="보내자 보유포인트가 하한금 미만으로 떨어지면 수신자 번호에 알림문자가 발송됩니다." />
                            </template>
                            <template #l_input>
                                <VTextField type="number" v-model="props.item.free.bonaeja.min_balance_limit"
                                    prepend-inner-icon="tabler-currency-won" placeholder="유보금미달 알림금"
                                    persistent-placeholder suffix="만원" />
                            </template>
                            <template #r_name>수신자번호</template>
                            <template #r_input>
                                <VTextField type="number" v-model="props.item.free.bonaeja.receive_phone"
                                        prepend-inner-icon="tabler-device-mobile" placeholder="01012345678"
                                        persistent-placeholder />
                            </template>
                        </CreateHalfVColV2>
                    </div>

                    <div>
                        <VCardTitle class="pt-10">
                            <BaseQuestionTooltip location="top" text="기본 설정 값" :content="`각 정보 추가시 기본으로 세팅되어있는 값들을 변경합니다.`"/> 
                        </VCardTitle>
                        <CreateHalfVColV2 :mdl="5" :mdr="7" class="pt-5">
                            <template #l_name>
                                할부개월
                            </template>
                            <template #l_input>
                                <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.free.default.installment"
                                    :items="installments" prepend-inneer-icon="fluent-credit-card-clock-20-regular"  item-title="title" item-value="id" single-line />
                            </template>
                            <template #r_name>
                                이상거래한도      
                            </template>
                            <template #r_input>
                                <VSelect v-model="props.item.free.default.abnormal_trans_limit" :items="abnormal_trans_limits"
                                    prepend-inner-icon="jam-triangle-danger" item-title="title" item-value="id"  single-line />
                            </template>
                        </CreateHalfVColV2>
                        <CreateHalfVColV2 :mdl="5" :mdr="7">
                            <template #l_name>
                                가맹점 수수료율 노출
                            </template>
                            <template #l_input>
                                <VSwitch hide-details v-model="props.item.free.default.is_show_fee"
                                    color="primary" :false-value=0 :true-value=1 />
                            </template>
                            <template #r_name>             
                            </template>
                            <template #r_input>
                            </template>
                        </CreateHalfVColV2>
                    </div>
                </VCardItem>
            </VCard>
        </VCol>
        <template v-if="getUserLevel() === 50">
            <VCol cols="12" :md="4">
                <VCard>
                    <VCardItem>
                        <VCardTitle>
                            <BaseQuestionTooltip location="top" text="영업점 옵션(유료)"
                                :content="`${corp.pv_options.auth.levels.dev_name}만 확인 가능한 정보입니다.`">
                            </BaseQuestionTooltip>
                        </VCardTitle>
                        <VRow class="pt-5">
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>종속 구조 설정</template>
                                <template #input>
                                    <VSwitch hide-details v-model="props.item.paid.sales_parent_structure" color="primary" />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VCardTitle class="pt-10">
                            <BaseQuestionTooltip location="top" text="가맹점 옵션(유료)"
                                :content="`${corp.pv_options.auth.levels.dev_name}만 확인 가능한 정보입니다.`">
                            </BaseQuestionTooltip>
                        </VCardTitle>
                        <VRow class="pt-5">
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>예금주 검증</template>
                                <template #input>
                                    <VSwitch hide-details v-model="props.item.paid.use_acct_verification" color="primary" />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow>
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>가맹점 전산 사용 ON/OFF</template>
                                <template #input>
                                    <VSwitch hide-details v-model="props.item.paid.subsidiary_use_control" color="primary" />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow>
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>수기결제 SMS</template>
                                <template #input>
                                    <VSwitch hide-details v-model="props.item.paid.use_hand_pay_sms" color="primary" />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow>
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>결제전 휴대폰 인증</template>
                                <template #input>
                                    <VSwitch hide-details v-model="props.item.paid.use_pay_verification_mobile"
                                        color="primary" />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow>
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>단골고객 카드등록</template>
                                <template #input>
                                    <VSwitch hide-details v-model="props.item.paid.use_regular_card" color="primary" />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow>
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>모아서 출금</template>
                                <template #input>
                                    <VSwitch hide-details v-model="props.item.paid.use_collect_withdraw" color="primary" />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow>
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>모아서 출금 스케줄링</template>
                                <template #input>
                                    <VSwitch hide-details v-model="props.item.paid.use_collect_withdraw_scheduler"
                                        color="primary" />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow>
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>출금 수수료</template>
                                <template #input>
                                    <VSwitch hide-details v-model="props.item.paid.use_withdraw_fee" color="primary" />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow>
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>정산계좌 숨김사용</template>
                                <template #input>
                                    <VSwitch hide-details v-model="props.item.paid.use_hide_account" color="primary" />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow>
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>
                                    <BaseQuestionTooltip location="top" text="다중 수기결제"
                                        :content="`사용 가맹점당 3개의 결제모듈이 존재해야 활성화 됩니다.`">
                                    </BaseQuestionTooltip>
                                </template>
                                <template #input>
                                    <VSwitch hide-details v-model="props.item.paid.use_multiple_hand_pay" color="primary" />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow>
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>
                                    <BaseQuestionTooltip location="top" text="지정시간 결제제한"
                                        :content="`지정시간대에 결제, 이체를 설정한 상한금 이상으로 할 수 없습니다.`">
                                    </BaseQuestionTooltip>
                                </template>
                                <template #input>
                                    <VSwitch hide-details v-model="props.item.paid.use_specified_limit" color="primary" />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow>
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>syslink 선정산 사용여부</template>
                                <template #input>
                                    <VSwitch hide-details v-model="props.item.paid.use_syslink" color="primary" />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        
                    </VCardItem>
                </VCard>
            </VCol>
            <VCol cols="12" :md="4">
                <VCard>
                    <VCardItem>
                        <VCardTitle>
                            <BaseQuestionTooltip location="top" text="결제모듈 옵션(유료)"
                                :content="`${corp.pv_options.auth.levels.dev_name}만 확인 가능한 정보입니다.`">
                            </BaseQuestionTooltip>
                        </VCardTitle>
                        <VRow class="pt-5">
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>실시간 결제모듈</template>
                                <template #input>
                                    <VSwitch hide-details v-model="props.item.paid.use_realtime_deposit" color="primary" />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow>
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>카드사 필터링</template>
                                <template #input>
                                    <VSwitch hide-details v-model="props.item.paid.use_issuer_filter" color="primary" />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow>
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>중복결제 검증</template>
                                <template #input>
                                    <VSwitch hide-details v-model="props.item.paid.use_dup_pay_validation" color="primary" />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow>
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>결제금지시간 지정</template>
                                <template #input>
                                    <VSwitch hide-details v-model="props.item.paid.use_forb_pay_time" color="primary" />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow>
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>결제한도 지정</template>
                                <template #input>
                                    <VSwitch hide-details v-model="props.item.paid.use_pay_limit" color="primary" />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow>
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>온라인 결제 사용</template>
                                <template #input>
                                    <VSwitch hide-details v-model="props.item.paid.use_online_pay" color="primary" />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow>
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>TID 발급버튼 사용</template>
                                <template #input>
                                    <VSwitch hide-details v-model="props.item.paid.use_tid_create" color="primary" />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow>
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>MID 발급버튼 사용</template>
                                <template #input>
                                    <VSwitch hide-details v-model="props.item.paid.use_mid_create" color="primary" />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow>
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>PMID 사용 여부</template>
                                <template #input>
                                    <VSwitch hide-details v-model="props.item.paid.use_pmid" color="primary" />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                    </VCardItem>
                </VCard>
            </VCol>
            <VCol cols="12" :md="4">
                <VCard>
                    <VCardItem>
                        <VCardTitle>
                            <BaseQuestionTooltip location="top" text="브랜드 옵션(유료)"
                                :content="`${corp.pv_options.auth.levels.dev_name}만 확인 가능한 정보입니다.`">
                            </BaseQuestionTooltip>
                        </VCardTitle>
                        <VRow class="pt-5">
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>노티 사용</template>
                                <template #input>
                                    <VSwitch hide-details v-model="props.item.paid.use_noti" color="primary" />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow>
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>본사 지정계좌 출금</template>
                                <template #input>
                                    <VSwitch hide-details v-model="props.item.paid.use_head_office_withdraw" color="primary" />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow>
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>이상거래, 결제실패, 실시간이체 관리 영업점 노출여부</template>
                                <template #input>
                                    <VSwitch hide-details v-model="props.item.auth.visibles.abnormal_trans_sales"
                                        color="primary" />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow>
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>
                                    <BaseQuestionTooltip location="top" text="기간별 사업자정보 사용"
                                        :content="`기간별로 사업자정보가 매출전표에 표기됩니다.(공급자 정보: 본사정보로 체크되어야합니다.)`">
                                    </BaseQuestionTooltip>
                                </template>
                                <template #input>
                                    <VSwitch hide-details v-model="props.item.paid.use_before_brand_info" color="primary" />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow>
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>가맹점 블랙리스트</template>
                                <template #input>
                                    <VSwitch hide-details v-model="props.item.paid.use_mcht_blacklist" color="primary" />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow>
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>부분취소</template>
                                <template #input>
                                    <VSwitch hide-details v-model="props.item.paid.use_part_cancel" color="primary" />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VCardTitle class="pt-10">
                            <BaseQuestionTooltip location="top" text="정산 옵션(유료)"
                                :content="`${corp.pv_options.auth.levels.dev_name}만 확인 가능한 정보입니다.`">
                            </BaseQuestionTooltip>
                        </VCardTitle>
                        <VRow class="pt-5">
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                    <template #name>지급대행 사용</template>
                                    <template #input>
                                    <VSwitch hide-details v-model="props.item.paid.use_finance_van_deposit" color="primary" />
                                </template>
                            </CreateHalfVCol>
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                    <template #name>지급보류 사용</template>
                                    <template #input>
                                    <VSwitch hide-details v-model="props.item.paid.use_settle_hold" color="primary" />
                                </template>
                            </CreateHalfVCol>
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                    <template #name>정산시 승인/취소 수 사용</template>
                                    <template #input>
                                    <VSwitch hide-details v-model="props.item.paid.use_settle_count" color="primary" />
                                </template>
                            </CreateHalfVCol>                            
                        </VRow>
                    </VCardItem>
                </VCard>
            </VCol>
        </template>
    </VRow>
</template>
