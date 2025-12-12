import { createI18n } from 'vue-i18n'
import { usePage } from '@inertiajs/vue3'

/**
 * Инициализация Vue i18n
 * Использует переводы, переданные через Inertia
 */
export function setupI18n() {
  const page = usePage()
  
  // Получаем переводы и локаль из Inertia props
  const translations = page.props.translations || {}
  const locale = page.props.locale || 'ru'
  
  const i18n = createI18n({
    legacy: false, // Composition API mode
    locale: locale,
    fallbackLocale: 'ru',
    messages: {
      [locale]: translations
    },
    // Отключаем предупреждения в production
    silentTranslationWarn: true,
    silentFallbackWarn: true
  })
  
  return i18n
}

/**
 * Обновить переводы при изменении локали
 */
export function updateI18n(i18n, locale, translations) {
  i18n.global.locale.value = locale
  i18n.global.setLocaleMessage(locale, translations)
}

