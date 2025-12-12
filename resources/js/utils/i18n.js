/**
 * i18n утилита для управления языками
 * Использует localStorage как основной источник хранения языка
 */

const AVAILABLE_LOCALES = ['ru', 'tg']
const DEFAULT_LOCALE = 'ru'

/**
 * Получить текущий язык из localStorage или вернуть язык по умолчанию
 */
export function getCurrentLocale() {
  if (typeof window === 'undefined') {
    return DEFAULT_LOCALE
  }
  
  const stored = localStorage.getItem('locale')
  if (stored && AVAILABLE_LOCALES.includes(stored)) {
    return stored
  }
  
  return DEFAULT_LOCALE
}

/**
 * Установить язык в localStorage
 */
export function setLocale(locale) {
  if (!AVAILABLE_LOCALES.includes(locale)) {
    console.warn(`Invalid locale: ${locale}, falling back to ${DEFAULT_LOCALE}`)
    locale = DEFAULT_LOCALE
  }
  
  if (typeof window !== 'undefined') {
    localStorage.setItem('locale', locale)
    console.log(`✅ Locale saved to localStorage: ${locale}`)
  }
  
  return locale
}

/**
 * Проверить, является ли язык валидным
 */
export function isValidLocale(locale) {
  return AVAILABLE_LOCALES.includes(locale)
}

/**
 * Получить название языка
 */
export function getLocaleName(locale) {
  const names = {
    ru: 'Русский',
    tg: 'Тоҷикӣ'
  }
  return names[locale] || locale
}

/**
 * Получить флаг языка
 */
export function getLocaleFlag(locale) {
  const flags = {
    ru: '🇷🇺',
    tg: '🇹🇯'
  }
  return flags[locale] || '🌐'
}

/**
 * Получить все доступные языки
 */
export function getAvailableLocales() {
  return AVAILABLE_LOCALES
}

/**
 * Инициализировать язык при загрузке страницы
 * Синхронизирует localStorage с сервером
 */
export function initLocale() {
  const currentLocale = getCurrentLocale()
  
  // Отправляем текущий язык на сервер для синхронизации
  if (typeof window !== 'undefined' && document.querySelector('meta[name="csrf-token"]')) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    
    // Отправляем заголовок с языком для следующего запроса
    // Это будет использоваться middleware для установки языка
    if (csrfToken) {
      // Устанавливаем заголовок для всех последующих запросов
      if (window.axios) {
        window.axios.defaults.headers.common['X-Locale'] = currentLocale
      }
    }
  }
  
  return currentLocale
}

