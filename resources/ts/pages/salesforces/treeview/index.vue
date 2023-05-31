<script setup lang="ts">
import VueTree from "@ssthouse/vue3-tree-chart";
import "@ssthouse/vue3-tree-chart/dist/vue3-tree-chart.css";
import { useSalesFilterStore } from '@/views/salesforces/useStore'
import { useTheme } from 'vuetify'

const { hierarchical, flattened } = useSalesFilterStore()
const salesforce = ref({ trx_fee: 0, user_name: '영업자 선택' })
const search = ref('')
const vehicules = {
    user_name: '본사',
    children: hierarchical,
    identifier: 'id'
}
const tree = ref();
const treeConfig = { nodeWidth: 80, nodeHeight: 80, levelHeight: 120 }

const handleScroll = (event:any) => {
  // 마우스 스크롤 이벤트 핸들러
  const deltaY = event.deltaY;
  if (deltaY > 0) {
    // 아래로 스크롤
    tree.value.zoomOut()
  } else if (deltaY < 0) {
    // 위로 스크롤
    tree.value.zoomIn()
  }
};

onMounted(() => {
  // 컴포넌트 마운트 후에 이벤트 리스너 등록
  tree.value.$el.addEventListener('wheel', handleScroll);
});
onUnmounted(() => {
  // 컴포넌트 언마운트 시 이벤트 리스너 해제
    if(tree.value != null)
      tree.value.$el.removeEventListener('wheel', handleScroll);
});
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
                        <VCol cols="12" sm="10">
                                <div>
                                    <VBtn type="button" @click="tree.zoomIn()">
                                        확대
                                    <VIcon end icon="tabler-plus" />
                                    </VBtn>
                                    <VBtn type="button" style="margin-left: 1em;" variant="tonal" @click="tree.zoomOut()">
                                        축소
                                        <VIcon end icon="tabler-minus" />
                                    </VBtn>
                                    <VBtn type="button" style="margin-left: 1em;" variant="tonal" @click="tree.restoreScale()">
                                        원래대로
                                        <VIcon end icon="tabler-line-height" />
                                    </VBtn>
                                </div>
                            </VCol>
                        <vue-tree style="width: 100%; height: 500px;" :dataset="vehicules" :config="treeConfig"
                            linkStyle="curve" ref="tree">
                            <template v-slot:node="{ node, collapsed }">
                                <div class="rich-media-node" :style="{ border: collapsed ? '1px solid grey' : '' }">
                                    <span style="padding: 4px 0; font-weight: bold;">{{ node.user_name }}</span>
                                    <span style="padding: 4px 0; font-weight: bold;">{{ (node.trx_fee * 100).toFixed(3)
                                    }}%</span>
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
  position: relative;
  z-index: 0;
  display: inline-flex;
  overflow: hidden;
  flex-direction: column;
  padding: 16px;
  border-radius: 12px;
  background-color: rgb(255, 255, 255);
  background-image: none;
  box-shadow: rgba(145, 158, 171, 20%) 0 0 2px 0, rgba(145, 158, 171, 12%) 0 12px 24px -4px;
  color: rgb(33, 43, 54);
  font-size: 12px;
  min-inline-size: 100px;
  text-align: start;
  text-transform: capitalize;
  transition: box-shadow 300ms cubic-bezier(0.4, 0, 0.2, 1) 0ms;
}

</style>
