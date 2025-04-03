export const inputFormater = () => {
    const business_num_format = ref('')
    const phone_num_format = ref('')
    const card_num_format = ref('')
    const yymm_format = ref('')
    const amount_format = ref('')

    const business_num = ref('')
    const phone_num = ref('')
    const card_num = ref('')
    const yymm = ref('')
    const amount = ref(0)

    const formatPhoneNum = computed(() => {
        let raw_value = phone_num_format.value.replace(/\D/g, '');
        phone_num.value = raw_value
        if (raw_value.length <= 3)
            phone_num_format.value = raw_value;
        else if (raw_value.length <= 7) 
            phone_num_format.value = raw_value.slice(0, 3) + '-' + raw_value.slice(3);
        else
            phone_num_format.value = raw_value.slice(0, 3) + '-' + raw_value.slice(3, 7) + '-' + raw_value.slice(7, 11);
    })

    const formatBusinessNum = computed(() => {
        let raw_value = business_num_format.value.replace(/\D/g, '');
        business_num.value = raw_value
        business_num_format.value = changeBusinessFormat(raw_value)
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
        const parse_amount = parseFloat(amount_format.value.replace(/,/g, "")) || 0;
        amount.value = parse_amount
        amount_format.value = parse_amount.toLocaleString()
    })

    const changeBusinessFormat = (value: string) => {
        if (value.length <= 3)
            return value;
        else if (value.length <= 5)
            return value.slice(0, 3) + '-' + value.slice(3);
        else
            return value.slice(0, 3) + '-' + value.slice(3, 5) + '-' + value.slice(5, 10);

    }

    const formatTime = (raw_value: string) => {
        raw_value = raw_value.replace(/\D/g, '');
        if (raw_value.length >= 1 && raw_value.length <= 2) {
            return raw_value; // 입력 중 (시작 시)
        } else if (raw_value.length <= 4) {
            // HHMM → HH:MM
            const fromHour = raw_value.slice(0, 2);
            const fromMin = raw_value.slice(2);
            return `${fromHour}:${fromMin}`;
        } else if (raw_value.length <= 6) {
            // HHMMHH → HH:MM~HH
            const fromHour = raw_value.slice(0, 2);
            const fromMin = raw_value.slice(2, 4);
            const toHour = raw_value.slice(4);
            return `${fromHour}:${fromMin}:${toHour}`;
        } else {
            raw_value = raw_value.slice(0, 6);
            const fromHour = raw_value.slice(0, 2);
            const fromMin = raw_value.slice(2, 4);
            const toHour = raw_value.slice(4);
            return `${fromHour}:${fromMin}:${toHour}`;
        }
    }

    return {
        business_num_format,
        phone_num_format,
        card_num_format,
        yymm_format,
        amount_format,

        business_num,
        phone_num,
        card_num,
        yymm,
        amount,

        formatBusinessNum,
        formatPhoneNum,
        formatCardNum,
        formatYYmm,
        formatAmount,
        formatTime,

        changeBusinessFormat
    }
}
