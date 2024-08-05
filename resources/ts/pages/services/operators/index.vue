<script setup lang="ts">
import PasswordChangeDialog from '@/layouts/dialogs/users/PasswordChangeDialog.vue'
import PhoneNum2FAVertifyDialog from '@/layouts/dialogs/users/PhoneNum2FAVertifyDialog.vue'
import ImageDialog from '@/layouts/dialogs/utils/ImageDialog.vue'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import { operator_levels, useSearchStore } from '@/views/services/operators/useStore'
import UserExtraMenu from '@/views/users/UserExtraMenu.vue'
import { getUserLevel } from '@axios'
import { DateFilters } from '@core/enums'

const { store, head, exporter } = useSearchStore()
const password = ref()
const imageDialog = ref()
const phoneNum2FAVertifyDialog = ref()

provide('phoneNum2FAVertifyDialog', phoneNum2FAVertifyDialog)
provide('password', password)
provide('store', store)
provide('head', head)
provide('exporter', exporter)

const showAvatar = (preview: string) => {
    imageDialog.value.show(preview)
}
</script>
<template>
    <div>
        <BaseIndexView placeholder="ID 및 성명 검색" :metas="[]" :add="getUserLevel() > 35 ? true : false" add_name="운영자" :date_filter_type="DateFilters.NOT_USE">
            <template #filter>
            </template>
            <template #index_extra_field>
                <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.page_size" density="compact" variant="outlined"
                    :items="[10, 20, 30, 50, 100, 200]" label="표시 개수" id="page-size-filter" eager  @update:modelValue="store.updateQueryString({page_size: store.params.page_size})" />
                <div>
                    <VSwitch hide-details :false-value=0 :true-value=1 v-model="store.params.is_lock" label="잠금계정 조회"
                        color="warning" @update:modelValue="store.updateQueryString({ is_lock: store.params.is_lock })" v-if="getUserLevel() >= 35"/>
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
                                <span v-if="_key == `id`" class="edit-link" @click="store.edit(item['id'])">
                                    #{{ item[_key] }}
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
        <PhoneNum2FAVertifyDialog ref="phoneNum2FAVertifyDialog"/>
        <ImageDialog ref="imageDialog" :style="`inline-size:20em !important;`"/>
    </div>
</template>
