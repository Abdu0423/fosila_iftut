<template>
  <v-menu offset-y>
    <template v-slot:activator="{ props: menuProps }">
      <v-btn
        v-bind="menuProps"
        variant="text"
        :prepend-icon="compact ? undefined : 'mdi-translate'"
        class="language-switcher"
        :class="{ 'compact': compact }"
      >
        <span v-if="!compact" class="mr-2">{{ getLocaleFlag(currentLocale) }}</span>
        <span v-if="compact" class="flag-only">{{ getLocaleFlag(currentLocale) }}</span>
        <span v-if="!compact">{{ getLocaleName(currentLocale) }}</span>
      </v-btn>
    </template>
    
    <v-list density="compact">
      <v-list-item
        v-for="loc in availableLocales"
        :key="loc"
        @click="changeLanguage(loc)"
        :active="loc === currentLocale"
      >
        <template v-slot:prepend>
          <v-icon v-if="loc === currentLocale" color="primary" class="mr-2">mdi-check</v-icon>
          <span v-else class="mr-2" style="width: 24px; display: inline-block;"></span>
          <span class="mr-3 flag-emoji">{{ getLocaleFlag(loc) }}</span>
        </template>
        <v-list-item-title>{{ getLocaleName(loc) }}</v-list-item-title>
      </v-list-item>
    </v-list>
  </v-menu>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { usePage, router } from '@/compat/inertia'

defineProps({
  compact: {
    type: Boolean,
    default: false
  }
})

const page = usePage()
const isChanging = ref(false)

const availableLocales = ['ru', 'tg']

const currentLocale = computed(() => {
  return page.props.locale || window.__LOCALE__ || 'ru'
})

const getLocaleName = (loc) => {
  const names = {
    ru: 'Русский',
    tg: 'Тоҷикӣ'
  }
  return names[loc] || loc
}

const getLocaleFlag = (loc) => {
  const flags = {
    ru: '🇷🇺',
    tg: '🇹🇯'
  }
  return flags[loc] || '🌐'
}

const changeLanguage = (newLocale) => {
  if (newLocale === currentLocale.value || isChanging.value) {
    return
  }
  
  isChanging.value = true
  
  router.post('/set-locale', { locale: newLocale }, {
    preserveScroll: true,
    preserveState: false, // Нужно обновить состояние для переводов
    onSuccess: () => {
      // Перезагружаем страницу для обновления переводов
      setTimeout(() => {
        window.location.reload()
      }, 500) // Небольшая задержка для показа уведомления
    },
    onError: () => {
      isChanging.value = false
    },
    onFinish: () => {
      // Сбрасываем флаг после завершения запроса
      setTimeout(() => {
        isChanging.value = false
      }, 1000)
    }
  })
}
</script>

<style scoped>
.language-switcher {
  text-transform: none;
  font-weight: 500;
}

.language-switcher.compact {
  min-width: auto;
  padding: 8px;
}

.flag-emoji {
  font-size: 1.3em;
  line-height: 1;
}

.flag-only {
  font-size: 1.5em;
}

.v-list-item {
  cursor: pointer;
}
</style>
