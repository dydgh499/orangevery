export const inputFormater = () => {
    const phone_num_format = ref('')
    const card_num_format = ref('')
    const yymm_format = ref('')
    const format_amount = ref('')

    const buyer_phone = ref('')
    const card_num = ref('')
    const yymm = ref('')
    const amount = ref(0)

    const formatPhoneNum = computed(() => {
        let raw_value = phone_num_format.value.replace(/\D/g, '');
        buyer_phone.value = raw_value
        // 휴대폰 번호 마스킹
        if (raw_value.length <= 3)
            phone_num_format.value = raw_value;
        else if (raw_value.length <= 7) 
            phone_num_format.value = raw_value.slice(0, 3) + '-' + raw_value.slice(3);
        else
            phone_num_format.value = raw_value.slice(0, 3) + '-' + raw_value.slice(3, 7) + '-' + raw_value.slice(7, 11);
    })
    
    const formatCardNum = computed(() => {
        let raw_value = card_num_format.value.replace(/\D/g, '')
        card_num.value = raw_value
        card_num_format.value = raw_value.match(/.{1,4}/g)?.join(' ') || ''  
    })
    
    const formatYYmm = computed(() => {
        let raw_value = yymm_format.value.replace(/\D/g, '')
        yymm.value = raw_value
        yymm_format.value = raw_value.match(/.{1,2}/g)?.join('/') || ''  
    })

    const formatAmount = computed(() => {
        const parse_amount = parseFloat(format_amount.value.replace(/,/g, "")) || 0;
        amount.value = parse_amount
        format_amount.value = parse_amount.toLocaleString()
    })

    return {
        phone_num_format,
        card_num_format,
        yymm_format,
        format_amount,

        buyer_phone,
        card_num,
        yymm,
        amount,
        
        formatPhoneNum,
        formatCardNum,
        formatYYmm,
        formatAmount,
    }
}
