<script setup lang="ts">

import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue';
import CreateHalfVColV2 from '@/layouts/utils/CreateHalfVColV2.vue';
import { installments, module_types } from '@/views/merchandises/pay-modules/useStore';
import { useStore } from '@/views/services/pay-gateways/useStore';
import type { AuthOption, FreeOption, Options, P2pAppOption, PaidOption } from '@/views/types';

interface Props {
    item: {
        free: FreeOption,
        paid: PaidOption,
        auth: AuthOption,
        p2p: P2pAppOption,
    },
}
const props = defineProps<Props>()
const { pgs, pss, psFilter, setFee } = useStore()

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

const filterPgs = computed(() => {
    const filter = pss.filter(item => { return item.pg_id == props.item.p2p.pg_id })
    props.item.p2p.ps_id = psFilter(filter, props.item.p2p.ps_id)
    return filter
})

</script>
<template>
    <VRow class="match-height">
        <VCol cols="12" :md="6">
            <VCard>
                <VCardItem>
                    <VCardTitle>기능 옵션</VCardTitle>
                    <CreateHalfVColV2 :mdl="8" :mdr="4" class="pt-5">
                        <template #l_name>MID 중복검사 사용</template>
                        <template #l_input>
                            <VSwitch hide-details v-model="props.item.free.use_mid_duplicate" color="primary" />
                        </template>
                        <template #r_name>TID 중복검사 사용</template>
                        <template #r_input>
                            <VSwitch hide-details v-model="props.item.free.use_tid_duplicate" color="primary" />
                        </template>
                    </CreateHalfVColV2>
                    <CreateHalfVColV2 :mdl="8" :mdr="4">
                        <template #l_name>
                            <BaseQuestionTooltip location="top" text="엑셀 다운로드시 검색필터 적용"
                                    :content="`검색필터에서 숨김처리한 내용들은 엑셀출력시 출력되지 않습니다.`"/>
                        </template>
                        <template #l_input>
                                <VSwitch hide-details v-model="props.item.free.excel_search_filter" color="primary" />
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
                            <BaseQuestionTooltip location="top" text="고정 테이블 사용" :content="`목록화면에서 조회테이블 헤더가 고정됩니다.`"/>
                        </template>
                        <template #l_input>
                            <VSwitch hide-details v-model="props.item.free.use_fix_table_view"
                                color="primary" />
                        </template>
                        <template #r_name>
                            <BaseQuestionTooltip location="top" text="고정 테이블 사이즈" :content="`목록화면에서 조회테이블의 세로높이 입니다.`"/>
                        </template>
                        <template #r_input>
                            <VTextField v-model="props.item.free.fix_table_size" placeholder="사이즈 입력"
                                        type="number" suffix="px" v-if="props.item.free.use_fix_table_view" />
                        </template>
                    </CreateHalfVColV2>

                    <VCardTitle class="pt-10">                        
                        보안 옵션<span><b class="text-error" style="font-size: 0.6em;"> (보안 옵션 완화로인한 금융사고는 책임지지 않습니다.)</b></span>
                    </VCardTitle>
                    <CreateHalfVColV2 :mdl="6" :mdr="6" class="pt-5">
                        <template #l_name>
                            <BaseQuestionTooltip location="top" text="가맹점 ID 난이도" :content="`가맹점 ID 추가/수정시 요구되는 검증 난이도입니다.`"/>
                        </template>
                        <template #l_input>
                            <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.free.secure.mcht_id_level"
                                :items="mchtIdLevels" prepend-inneer-icon="fluent-credit-card-clock-20-regular"
                                label="ID 난이도 선택" item-title="title" item-value="id" single-line />
                        </template>
                        <template #r_name>
                            <BaseQuestionTooltip location="top" text="가맹점 PW 난이도" :content="`가맹점 패스워드 추가/수정시 요구되는 검증 난이도입니다.`"/>
                        </template>
                        <template #r_input>
                            <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.free.secure.mcht_pw_level"
                                :items="mchtPwLevels" prepend-inneer-icon="fluent-credit-card-clock-20-regular"
                                label="패스워드 난이도 선택"  item-title="title" item-value="id" single-line />
                        </template>
                    </CreateHalfVColV2>
                    <CreateHalfVColV2 :mdl="6" :mdr="6">
                        <template #l_name>
                            <BaseQuestionTooltip location="top" text="영업점 ID 난이도" :content="`영업점 ID 추가/수정시 요구되는 검증 난이도입니다.`"/>
                        </template>
                        <template #l_input>
                            <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.free.secure.sales_id_level"
                                :items="mchtIdLevels" prepend-inneer-icon="fluent-credit-card-clock-20-regular"
                                label="ID 난이도 선택" item-title="title" item-value="id" single-line />
                        </template>
                        <template #r_name>
                            <BaseQuestionTooltip location="top" text="영업점 PW 난이도" :content="`영업점 패스워드 추가/수정시 요구되는 검증 난이도입니다.`"/>
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
                            <BaseQuestionTooltip location="top" text="운영자만 로그인" :content="`가맹점과 영업점은 본 전산에서 로그인할 수 없습니다.`"/>
                        </template>
                        <template #r_input>
                            <VSwitch hide-details v-model="props.item.free.secure.login_only_operate"
                                    color="primary" :false-value=0 :true-value=1 />
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
                            <template #r_name>포인트 하한금 알림번호</template>
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
                                <VTextField prepend-inner-icon="jam-triangle-danger" v-model="props.item.free.default.abnormal_trans_limit"
                                            type="number" suffix="만원" label="이상거래 한도"/>
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
                        <VCardTitle class="pt-10">
                            <BaseQuestionTooltip location="top" text="P2P APP" :content="`P2P APP 회원가입시 기본적으로 추가될 값 입니다.<br><b>PG사 관리의 대표 결제 정보<b>가 입력 되어있어야합니다.`"/> 
                        </VCardTitle>
                        <CreateHalfVColV2 :mdl="5" :mdr="7">
                            <template #l_name>
                                원천사
                            </template>
                            <template #l_input>
                                <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.p2p.pg_id" :items="pgs"
                                        prepend-inner-icon="ph-buildings" label="원천사 선택" item-title="pg_name" item-value="id"
                                />    
                            </template>
                            <template #r_name>
                                구간
                            </template>
                            <template #r_input>
                                <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.p2p.ps_id" :items="filterPgs"
                                    prepend-inner-icon="mdi-vector-intersection" label="구간 선택" item-title="name" item-value="id"
                                    :hint="`${setFee(pss, props.item.p2p.ps_id)}`" persistent-hint/>

                            </template>
                        </CreateHalfVColV2>
                        <CreateHalfVColV2 :mdl="5" :mdr="7">
                            <template #l_name>
                                모듈타입
                            </template>
                            <template #l_input>
                                <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.p2p.module_type"
                                    :items="module_types"
                                    prepend-inner-icon="ic-outline-send-to-mobile" label="결제모듈 타입" item-title="title"
                                    item-value="id" />
                            </template>
                            <template #r_name>
                                수기 구인증 여부
                            </template>
                            <template #r_input>
                                <VSwitch hide-details v-model="props.item.p2p.is_old_auth"
                                    color="primary" :false-value=0 :true-value=1 />
                            </template>
                        </CreateHalfVColV2>
                    <CreateHalfVColV2 :mdl="5" :mdr="7">
                        <template #l_name>
                            <BaseQuestionTooltip location="top" text="1원인증" :content="`1원 인증입니다. 사용을위해 개발사에 문의 부탁드립니다.`"/>
                        </template>
                        <template #l_input>
                            <VSwitch hide-details v-model="props.item.p2p.account_validate"
                                color="primary" :false-value=0 :true-value=1 />
                        </template>
                        <template #r_name>
                            <BaseQuestionTooltip location="top" text="전자계약 사용여부" :content="`전자계약 사용여부입니다. 사용을위해 개발사에 문의 부탁드립니다.`"/>
                        </template>
                        <template #r_input>                            
                            <VSwitch hide-details v-model="props.item.p2p.contract_validate"
                                    color="primary" :false-value=0 :true-value=1 />
                        </template>
                    </CreateHalfVColV2>
                    <CreateHalfVColV2 :mdl="5" :mdr="7">
                            <template #l_name>
                                <BaseQuestionTooltip location="top" text="CI 본인인증" :content="`카카오 본인인증입니다. 사용을위해 개발사에 문의 부탁드립니다.`"/>
                            </template>
                            <template #l_input>
                                <VSwitch hide-details v-model="props.item.p2p.ci_validate"
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
    </VRow>
</template>
