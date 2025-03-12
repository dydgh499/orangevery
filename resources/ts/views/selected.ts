
export function selectFunctionCollect(store:any, depth_mode=false) {
    const selected = ref<number[]>([])
    const all_selected = ref()
    const alert = <any>(inject('alert'))
    const snackbar = <any>(inject('snackbar'))

    watchEffect(() => {
        if(depth_mode === false)
            selected.value = all_selected.value ? store.getItems.map(item => item['id']) : []
    })

    const dialog = async (messge: string) => {
        const count = selected.value.length
        if(count) {
            const str_selected = selected.value.join(',')
            if (await alert.value.show(messge+'<br><br>NO. ['+str_selected+']'))
                return true
        }
        else
            snackbar.value.show('1개이상 필수로 선택 되어야합니다.', 'warning')
        return false
    }
    return { selected, all_selected, dialog }
}
