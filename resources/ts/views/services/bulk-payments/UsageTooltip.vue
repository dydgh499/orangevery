<script setup lang="ts">
</script>
<template>
    <div class="d-inline-flex align-center gap-2 justify-content-evenly">
        <h4 style="font-weight: bold;">사용방법</h4>
        <VTooltip open-on-click :open-on-hover="false" :location="'top'" transition="scale-transition">
            <template #activator="{ props }">
                <VIcon v-bind="props" size="20" icon="ic:outline-help" color="primary" style="margin-bottom: 0.2em;" />
            </template>
            <span>
                <div style="line-height: 40px;">
                    주의사항을 확인한 후 오른쪽 하단
                    <VBtn color="secondary" aria-readonly="" size="small">
                        양식 다운로드
                        <VIcon end icon="uiw-file-excel" />
                    </VBtn>
                    버튼을 클릭하여 양식을 다운받은 후
                    <VBtn type="button" aria-readonly="" size="small">
                        양식 업로드
                        <VIcon end icon="uiw-file-excel" />
                    </VBtn>
                    버튼을 통해
                    <br>
                    액셀 등록을 진행합니다.
                    양식 업로드를 통해 정상적인 값으로 1차 검증이되면
                    <VBtn type="button" aria-readonly="" size="small">
                        등록
                        <VIcon end icon="tabler-pencil" />
                    </VBtn>
                    버튼이 활성화 됩니다.
                </div>
            </span>
        </VTooltip>
    </div>
</template>
