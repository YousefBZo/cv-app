import {defineStore} from 'pinia'
import {ref} from 'vue'

export const useToastStore = defineStore('toast', () => {
    const toasts = ref([])
    let nextId = 0

    function addToast(message, type = 'error', duration = 5000) {
        const id = nextId++
        toasts.value.push({id, message, type})
        setTimeout(() => removeToast(id), duration)
    }

    function removeToast(id) {
        toasts.value = toasts.value.filter(t => t.id !== id)
    }

    function showError(message) {
        addToast(message, 'error')
    }

    function showSuccess(message) {
        addToast(message, 'success', 3000)
    }

    function showWarning(message) {
        addToast(message, 'warning', 4000)
    }

    return {toasts, addToast, removeToast, showError, showSuccess, showWarning}
})

