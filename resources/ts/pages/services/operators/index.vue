<script setup lang="ts">
import OperatorIPDialog from '@/layouts/dialogs/services/OperatorIPDialog.vue'
import OperatorDialog from '@/layouts/dialogs/users/OperatorDialog.vue'

import PasswordChangeDialog from '@/layouts/dialogs/users/PasswordChangeDialog.vue'
import ImageDialog from '@/layouts/dialogs/utils/ImageDialog.vue'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import { operator_levels, operatorActionAuthStore, useSearchStore } from '@/views/services/operators/useStore'
import UserExtraMenu from '@/views/users/UserExtraMenu.vue'
import { avatars } from '@/views/users/useStore'
import { getUserLevel } from '@axios'
import { DateFilters } from '@core/enums'

const { store, head, exporter } = useSearchStore()
const { headOfficeAuthValidate } = operatorActionAuthStore()

const password = ref()
const imageDialog = ref()
const operatorDialog = ref()
const operatorIPDialog = ref()

provide('password', password)
provide('store', store)
provide('head', head)
provide('exporter', exporter)
provide('imageDialog', imageDialog)

const showAvatar = (preview: string) => {
    imageDialog.value.show(preview)
}
const showOperatorIPDialog = async () => {
    const [result, token] = await headOfficeAuthValidate('접속 허용 IP를 확인하기 위해 휴대폰번호 인증이 필요합니다.<br>계속하시겠습니까?')
    if(result) {
        operatorIPDialog.value.show(token)
    }
}

</script>
<template>
    <div>
        <BaseIndexView placeholder="ID 및 성명 검색" :metas="[]" :add="false" add_name="운영자" :date_filter_type="DateFilters.NOT_USE">
            <template #filter>
            </template>
            <template #index_extra_field>
                <VBtn prepend-icon="tabler:user-cog" @click="operatorDialog.show({id:0, profile_img: avatars[Math.floor(Math.random() * avatars.length)]})" size="small" 
                    v-if="getUserLevel() > 35"
                    :style="$vuetify.display.smAndDown ? 'margin: 0.25em;' : ''">
                    운영자 추가
                </VBtn>
                <VBtn prepend-icon="tabler:user-cog" @click="showOperatorIPDialog()" size="small" 
                    v-if="getUserLevel() > 35" color="error"
                    :style="$vuetify.display.smAndDown ? 'margin: 0.25em;' : ''">
                    접속허용 IP 관리
                </VBtn>
                <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.page_size" density="compact" variant="outlined"
                    :items="[10, 20, 30, 50, 100, 200]" label="조회 개수" id="page-size-filter" eager  @update:modelValue="store.updateQueryString({page_size: store.params.page_size})" 
                    :style="$vuetify.display.smAndDown ? 'margin: 0.25em;' : ''"/>
                <div>
                    <VSwitch hide-details :false-value=0 :true-value=1 v-model="store.params.is_lock" label="잠금계정 조회"
                        color="warning" @update:modelValue="store.updateQueryString({ is_lock: store.params.is_lock })" v-if="getUserLevel() > 35"
                        :style="$vuetify.display.smAndDown ? 'margin: 0.25em;' : ''"/>
                </div>
            </template>
            <template #headers>
                <tr>
                    <th v-for="(header, key) in head.flat_headers" :key="key" v-show="header.visible" class='list-square'>
                        <span>
                            {{ header.ko }}
                        </span>
                    </th>
                </tr>
            </template>
            <template #body>
                <tr v-for="(item, index) in store.getItems" :key="index">
                    <template v-for="(_header, _key, _index) in head.headers" :key="_index">
                        <template v-if="head.getDepth(_header, 0) != 1">
                        </template>
                        <template v-else>
                            <td v-show="_header.visible" class='list-square'>
                                <span v-if="_key == `id`" class="edit-link" @click="operatorDialog.show(item)">
                                    #{{ item[_key] }}
                                    <VTooltip activator="parent" location="top" transition="scale-transition" v-if="$vuetify.display.smAndDown === false">
                                        수정하기
                                    </VTooltip>
                                </span>
                                <span v-else-if="_key == `level`">
                                    <VChip :color="item[_key] === 35 ? 'default' : 'primary'">
                                        {{ operator_levels.find(obj => obj.id === item[_key])?.title }}
                                    </VChip>
                                </span>
                                <span v-else-if="_key == 'is_lock'">
                                    <VChip :color="store.booleanTypeColor(item[_key])">
                                        {{ item[_key] ? 'LOCK' : 'X' }}
                                    </VChip>
                                </span>
                                <span v-else-if="_key == 'is_active'">
                                    <VChip :color="store.booleanTypeColor(!item[_key])" v-if="item['level'] >= 35">
                                        {{ item[_key] ? '활성화' : '비활성화' }}
                                    </VChip>
                                    <span v-else>-</span>
                                </span>
                                <span v-else-if="_key == 'is_notice_realtime_warning'">
                                    <VChip :color="store.booleanTypeColor(!item[_key])" v-if="item['level'] >= 35">
                                        {{ item[_key] ? 'ON' : 'OFF' }}
                                    </VChip>
                                    <span v-else>-</span>
                                </span>
                                <span v-else-if="_key == 'is_2fa_use'">
                                    <VChip :color="store.booleanTypeColor(!item[_key])">
                                        {{ item[_key] ? 'O' : 'X' }}
                                    </VChip>
                                </span>
                                <span v-else-if="_key == 'profile_img'">
                                    <VAvatar :image="item[_key]" class="me-3 preview" @click="showAvatar(item['profile_img'])"/>
                                </span>
                                <span v-else-if="_key == 'extra_col'">
                                    <UserExtraMenu :item="item" :type="2" :key="item['id']" />
                                </span>
                                <span v-else-if="_key == 'updated_at'" :class="item[_key] !== item['created_at'] ? 'text-primary' : ''">
                                    {{ item[_key] }}
                                </span>     
                                <span v-else>
                                    {{ item[_key] }}
                                </span>
                            </td>
                        </template>
                    </template>
                </tr>
            </template>
        </BaseIndexView>
        <PasswordChangeDialog ref="password" />
        <ImageDialog ref="imageDialog" :style="`inline-size:20em !important;`"/>
        <OperatorDialog ref="operatorDialog" />
        <OperatorIPDialog ref="operatorIPDialog"/>
    </div>
</template>
