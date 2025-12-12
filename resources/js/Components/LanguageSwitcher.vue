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

<script setup>
import { ref, computed } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  compact: {
    type: Boolean,
    default: false
  }
})

const { locale } = useI18n()
const page = usePage()

const availableLocales = ref(['ru', 'tg'])

const currentLocale = computed(() => {
  return page.props.locale || locale.value || 'ru'
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

const changeLanguage = async (newLocale) => {
  if (newLocale === currentLocale.value) {
    return
  }
  
  // Отправляем POST запрос для смены языка
  router.post('/set-locale', { locale: newLocale }, {
    preserveState: false,
    preserveScroll: true,
    onSuccess: () => {
      // Страница перезагрузится автоматически с новым языком
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

