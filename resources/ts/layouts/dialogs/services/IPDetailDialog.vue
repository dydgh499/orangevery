<script setup lang="ts">
interface IPDetail {
    ip: string,
    city?: string,
    region?: string,
    country?: string,
    loc?: string,
    postal?: string,
    timezone?: string,
    asn?: string,
}

const visible = ref(false)
const ip_detail = ref(<IPDetail>({}))
const show = (_ip_detail: any) => {
    console.log(_ip_detail)
    ip_detail.value = _ip_detail
    visible.value = true
}

defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="visible" max-width="900">
        <!-- Dialog close btn -->
        <DialogCloseBtn @click="visible = false" />
        <!-- Dialog Content -->
        <VCard title="IP 상세정보">
            <VCardText>
                <VRow style="margin-left: 0.5em;">
                    <span class="text-sm">
                        IP 정보는 참고목적으로 사용되며, 정확한 주소를 제공하지 않습니다.
                    </span>
                </VRow>
                <br>
                <VTable class="text-no-wrap" style="width: 100%;">
                    <thead>
                        <tr>
                            <th class='list-square'>구분</th>
                            <th class='list-square'>값</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class='list-square'>IP</td>
                            <td class='list-square'>{{ ip_detail.ip }}</td>
                        </tr>
                        <tr>
                            <td class='list-square'>도시</td>
                            <td class='list-square'>{{ ip_detail.city }}</td>
                        </tr>
                        <tr>
                            <td class='list-square'>지역</td>
                            <td class='list-square'>{{ ip_detail.region }}</td>
                        </tr>
                        <tr>
                            <td class='list-square'>국가</td>
                            <td class='list-square'>{{ ip_detail.country }}</td>
                        </tr>
                        <tr>
                            <td class='list-square'>위/경도</td>
                            <td class='list-square'>{{ ip_detail.loc }}</td>
                        </tr>
                        <tr>
                            <td class='list-square'>우편번호</td>
                            <td class='list-square'>{{ ip_detail.postal }}</td>
                        </tr>
                        <tr>
                            <td class='list-square'>timezone</td>
                            <td class='list-square'>{{ ip_detail.timezone }}</td>
                        </tr>
                        <tr>
                            <td class='list-square'>ASN</td>
                            <td class='list-square'>{{ ip_detail.asn }}</td>
                        </tr>
                    </tbody>
                </VTable>
            </VCardText>
        </VCard>
    </VDialog>
</template>
<style scoped>
:deep(.v-table__wrapper) {
  block-size: auto !important;
}
</style>
