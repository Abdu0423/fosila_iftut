import { createI18n } from 'vue-i18n'

export function setupI18n() {
  const translations = window.__TRANSLATIONS__ || {}
  const locale = window.__LOCALE__ || 'ru'

  return createI18n({
    legacy: false,
    locale,
    fallbackLocale: 'ru',
    messages: {
      [locale]: translations
    },
    silentTranslationWarn: true,
    silentFallbackWarn: true
  })
}

export function updateI18n(i18n, locale, translations) {
  i18n.global.locale.value = locale
  i18n.global.setLocaleMessage(locale, translations)
}
