import { reactive, computed, readonly } from 'vue'
import { usePage } from '@inertiajs/vue3'

// Глобальное хранилище переводов
const state = reactive({
  translations: {},
  locale: 'ru'
})

// Инициализация переводов из Inertia props
export function initTranslations() {
  const page = usePage()
  
  // Получаем переводы из props
  const translations = page.props.translations || {}
  const locale = page.props.locale || 'ru'
  
  state.translations = translations
  state.locale = locale
  
  return {
    translations: readonly(state.translations),
    locale: readonly(state.locale)
  }
}

// Функция перевода t('navigation.dashboard')
export function useTranslate() {
  const page = usePage()
  
  const t = (key, fallback = '') => {
    const translations = page.props.translations || {}
    const parts = key.split('.')
    
    let value = translations
    for (const part of parts) {
      if (value && typeof value === 'object' && part in value) {
        value = value[part]
      } else {
        return fallback || key
      }
    }
    
    return value || fallback || key
  }
  
  const locale = computed(() => page.props.locale || 'ru')
  const translations = computed(() => page.props.translations || {})
  
  return {
    t,
    locale,
    translations
  }
}

// Plugin для Vue
export const i18nPlugin = {
  install(app) {
    // Глобальная функция $t
    app.config.globalProperties.$t = function(key, fallback = '') {
      const page = usePage()
      const translations = page.props.translations || {}
      const parts = key.split('.')
      
      let value = translations
      for (const part of parts) {
        if (value && typeof value === 'object' && part in value) {
          value = value[part]
        } else {
          return fallback || key
        }
      }
      
      return value || fallback || key
    }
  }
}

export default { useTranslate, initTranslations, i18nPlugin }

