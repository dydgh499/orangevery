
export function selectFunctionCollect(store:any) {
    const selected = ref<number[]>([])
    const all_selected = ref()

    watchEffect(() => {
        selected.value = all_selected.value ? store.getItems.map(item => item['id']) : []
    })
    return {selected, all_selected}
}
