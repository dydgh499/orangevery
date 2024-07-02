export const timer = (time: number) => {
    const countdown_time = ref(time)
    let countdown_timer = <any>(null)

    const startTimer = () => {
        countdown_time.value = time
        countdown_timer = setInterval(timer, 1000)
    }

    const timer = () => {
        if (countdown_time.value === 0)
            clearInterval(countdown_timer)
        else
            countdown_time.value--
    }
    
    const countdownTimer = computed(() => {
        if (countdown_time.value > 0) {
            const min = parseInt((countdown_time.value / 60).toString())
            const sec = countdown_time.value % 60
            return `${min}:${sec < 10 ? '0' + sec : sec}`
        }
        else {
            return `0:00`
        }
    })
    return {
        countdown_time,
        countdown_timer,
        countdownTimer,
        startTimer,
    }
}
