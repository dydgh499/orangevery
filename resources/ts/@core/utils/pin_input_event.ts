export const pinInputEvent = (total_input: number) => {
    const digits = ref<string[]>([])
    const ref_opt_comp = ref<HTMLInputElement | null>(null)
    const defaultStyle = {
        style: 'max-width: 48px; text-align: center;',
    }

    const handleKeyDown = (index: number) => {
        if (ref_opt_comp.value !== null && index > 0) {
            const children = ref_opt_comp.value.children
            const cur_ele = <HTMLInputElement>(children[index - 1].querySelector('input'))
            const value = cur_ele.value
    
            // backspace
            if (value === '') {
                if (index > 1) {
                    const inputEl = children[index - 2].querySelector('input')
                    if (inputEl)
                        inputEl.focus()
                }
            }
            const numberRegExp = /^([0-9])$/
            if (numberRegExp.test(value)) {
                if (ref_opt_comp.value !== null && index !== 0 && index < children.length) {
                    const inputEl = children[index].querySelector('input')
                    if (inputEl)
                        inputEl.focus()
                }
            }
    
            digits.value[index - 1] = value
        }
    }

    return {
        digits,
        ref_opt_comp,
        handleKeyDown,
        defaultStyle,
    }
    
}
