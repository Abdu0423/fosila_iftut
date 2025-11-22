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
import { useTranslations } from '../composables/useTranslations'

const props = defineProps({
  compact: {
    type: Boolean,
    default: false
  }
})

const { locale, changeLocale, getLocaleName, getLocaleFlag } = useTranslations()

const availableLocales = ref(['ru', 'tg'])

const currentLocale = computed(() => {
  return locale.value || 'tg'
})

const changeLanguage = async (newLocale) => {
  if (newLocale !== currentLocale.value) {
    await changeLocale(newLocale)
  }
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
  transition: background-color 0.2s;
}

.v-list-item:hover {
  background-color: rgba(0, 0, 0, 0.04);
}
</style>
