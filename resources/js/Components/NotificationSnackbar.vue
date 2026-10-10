<template>
  <v-snackbar
    v-if="notification"
    v-model="show"
    :color="notification.color"
    :timeout="notification.timeout"
    location="top right"
    variant="elevated"
  >
    <div class="d-flex align-center">
      <v-icon class="mr-3" :color="notification.iconColor">
        {{ notification.icon }}
      </v-icon>
      <div>
        <div class="font-weight-medium">{{ notification.title }}</div>
        <div class="text-caption">{{ notification.message }}</div>
      </div>
    </div>
    
    <template v-slot:actions>
      <v-btn
        :color="notification.iconColor"
        variant="text"
        @click="close"
        size="small"
      >
        Закрыть
      </v-btn>
    </template>
  </v-snackbar>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { usePage } from '@/compat/inertia'

const page = usePage()
const show = ref(false)

const notification = computed(() => {
  const flash = page.props.flash
  
  if (flash?.error) {
    return {
      title: 'Ошибка',
      message: flash.error,
      color: 'error',
      iconColor: 'white',
      icon: 'mdi-alert-circle',
      timeout: 5000
    }
  }
  
  if (flash?.success) {
    return {
      title: 'Успешно',
      message: flash.success,
      color: 'success',
      iconColor: 'white',
      icon: 'mdi-check-circle',
      timeout: 3000
    }
  }
  
  if (flash?.warning) {
    return {
      title: 'Предупреждение',
      message: flash.warning,
      color: 'warning',
      iconColor: 'white',
      icon: 'mdi-alert',
      timeout: 4000
    }
  }
  
  if (flash?.info) {
    return {
      title: 'Информация',
      message: flash.info,
      color: 'info',
      iconColor: 'white',
      icon: 'mdi-information',
      timeout: 3000
    }
  }
  
  return null
})

watch(notification, (newNotification) => {
  if (newNotification) {
    show.value = true
  } else {
    show.value = false
  }
}, { immediate: true })

watch(show, (newShow) => {
  if (!newShow && notification.value) {
    // Очищаем flash сообщения после закрытия уведомления
    const page = usePage()
    if (page.props.flash) {
      page.props.flash = {}
    }
  }
})

const close = () => {
  show.value = false
}
</script>
