export const timerV1 = (init_time: number) => {
    const countdown_time = ref(init_time)
    let countdown_timer = <any>(null)

    const clearTimer = () => {
        if(countdown_timer)
            clearInterval(countdown_timer)
    }
    const startTimer = () => {
        countdown_time.value = init_time
        countdown_timer = setInterval(updateRemainingTime, 1000)
    }

    const restartTimer = () => {
        clearTimer()
        startTimer()
    }

    const updateRemainingTime = () => {
        if (countdown_time.value === 0)
            clearInterval(countdown_timer)
        else
            countdown_time.value--
    }
    
    const countdownTimer = computed(() => {
        if (countdown_time.value > 0) {
            const min = parseInt((countdown_time.value / 60).toString())
            const sec = countdown_time.value % 60
            return `${min}:${sec < 10 ? `0${sec}` : sec}`
        }
        else {
            return `0:00`
        }
    })
    
    startTimer()
    return {
        countdown_time,
        countdownTimer,
        restartTimer,
    }
}

export const timerV2 = (init_time: string) => {
    const remaining_time = ref(<string>("00:00:00"))
    const expire_time = ref(<string>(init_time))
    let countdown_timer = <any>(null)

    const clearTimer = () => {
        if(countdown_timer)
            clearInterval(countdown_timer)
    }
    const startTimer = () => {
        expire_time.value = init_time
        countdown_timer = setInterval(updateRemainingTime, 1000)
    }

    const restartTimer = () => {
        clearTimer()
        startTimer()
    }
    const updateRemainingTime = () => {
        const expire = new Date(expire_time.value)
        const now = new Date()
        const diff = expire.getTime() - now.getTime()
        if (!Number.isNaN(diff))
        {
            if (diff < 0)
                remaining_time.value = "00:00:00"
            else {
                const hours = Math.floor(diff / (1000 * 60 * 60))
                const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60))
                const seconds = Math.floor((diff % (1000 * 60)) / 1000)
    
                const formatted_hours = hours.toString().padStart(2, '0')
                const formatted_mins = minutes.toString().padStart(2, '0')
                const formatted_secs = seconds.toString().padStart(2, '0')
    
                remaining_time.value = `${formatted_hours}:${formatted_mins}:${formatted_secs}`
            }    
        }
    }

    const getRemainTimeColor = computed(() => {
        const expire = new Date(expire_time.value)
        const now = new Date()
        const diff = expire.getTime() - now.getTime()
        if (diff < 0)
            return 'text-error'
        else {
            const minutes = Math.floor(diff / (1000 * 60))
            if (minutes < 5)
                return 'text-error'
            else
                return 'text-primary'
        }
    })

    startTimer()
    return {
        getRemainTimeColor, 
        remaining_time,
        expire_time,
        clearTimer,
    }
}
