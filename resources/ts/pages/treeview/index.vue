<script setup lang="ts">
import type { MerchandiseProperties } from '@/@fake-db/types';
import { useSalesforceListStore } from '@/views/salesforces/salesforceMoudleListStore';
import VueTree from "@ssthouse/vue3-tree-chart";
import "@ssthouse/vue3-tree-chart/dist/vue3-tree-chart.css";
import { useSalesHierarchicalStore } from '@/views/salesforces/useStore'

const { hierarchical, flattened } = useSalesHierarchicalStore()
const salesforce = ref({ trx_fee: 0, user_name: '영업자 선택' })
const search = ref('')
const vehicules = {
    user_name: '본사',
    children: hierarchical,
    identifier: 'id'
}

const treeConfig = { nodeWidth: 120, nodeHeight: 80, levelHeight: 200 }
</script>

<template>
    <section>
        <VRow>
            <VCol cols="12">
                <VCard title="검색 옵션">
                    <!-- 👉 Filters -->
                    <VCardText>
                        <VRow>
                            <!-- 👉 Select Plan -->
                            <VCol cols="12" sm="2">
                                <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="salesforce" :items="flattened"
                                    prepend-inner-icon="tabler-man" label="영업자 선택"
                                    :hint="`수수료율: ${(salesforce.trx_fee * 100).toFixed(3)}%`" item-title="user_name"
                                    item-value="id" persistent-hint return-object single-line />
                            </VCol>
                        </VRow>
                    </VCardText>
                    <VDivider />


                    <VDivider />
                    <VCardText>
                        <vue-tree style="width: 100%; height: 800px;" :dataset="vehicules" :config="treeConfig"
                            linkStyle="straight">
                            <template v-slot:node="{ node, collapsed }">
                                <div class="rich-media-node" :style="{ border: collapsed ? '2px solid grey' : '' }">
                                    <span style="padding: 4px 0; font-weight: bold;">{{ node.user_name }}</span>
                                    <span style="padding: 4px 0; font-weight: bold;">{{ node.trx_fee }}</span>
                                </div>
                            </template>
                        </vue-tree>

                    </VCardText>
                </VCard>
            </VCol>
        </VRow>
    </section>
</template>
<style scoped lang="scss">
.container {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.rich-media-node {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  justify-content: center;
  padding: 8px;
  border-radius: 4px;
  background-color: #f7c616;
  color: white;
  inline-size: 80px;
}
</style>
