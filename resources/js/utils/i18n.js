/**
 * i18n утилита для управления языками
 * Использует localStorage как основной источник хранения языка
 */

const AVAILABLE_LOCALES = ['ru', 'tg']
const DEFAULT_LOCALE = 'ru'

/**
 * Получить текущий язык из localStorage, cookie или вернуть язык по умолчанию
 */
export function getCurrentLocale() {
  if (typeof window === 'undefined') {
    return DEFAULT_LOCALE
  }
  
  // Сначала проверяем localStorage
  const stored = localStorage.getItem('locale')
  if (stored && AVAILABLE_LOCALES.includes(stored)) {
    return stored
  }
  
  // Затем проверяем cookie (для первой загрузки)
  const cookies = document.cookie.split(';')
  for (let cookie of cookies) {
    const [name, value] = cookie.trim().split('=')
    if (name === 'locale' && value && AVAILABLE_LOCALES.includes(value)) {
      // Синхронизируем с localStorage
      localStorage.setItem('locale', value)
      return value
    }
  }
  
  return DEFAULT_LOCALE
}

/**
 * Установить язык в localStorage и cookie
 */
export function setLocale(locale) {
  if (!AVAILABLE_LOCALES.includes(locale)) {
    console.warn(`Invalid locale: ${locale}, falling back to ${DEFAULT_LOCALE}`)
    locale = DEFAULT_LOCALE
  }
  
  if (typeof window !== 'undefined') {
    // Сохраняем в localStorage
    localStorage.setItem('locale', locale)
    console.log(`✅ Locale saved to localStorage: ${locale}`)
    
    // Сохраняем в cookie для передачи на сервер при первой загрузке
    document.cookie = `locale=${locale}; path=/; max-age=31536000; SameSite=Lax`
    console.log(`✅ Locale saved to cookie: ${locale}`)
    
    // Обновляем заголовок axios для всех последующих запросов
    if (window.axios) {
      window.axios.defaults.headers.common['X-Locale'] = locale
      console.log(`✅ Axios header X-Locale updated to: ${locale}`)
    }
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
  
  // Устанавливаем заголовок для всех последующих запросов (axios и fetch)
  if (typeof window !== 'undefined') {
    // Убеждаемся, что язык сохранен в localStorage и cookie
    if (!localStorage.getItem('locale')) {
      localStorage.setItem('locale', currentLocale)
    }
    
    // Устанавливаем cookie
    document.cookie = `locale=${currentLocale}; path=/; max-age=31536000; SameSite=Lax`
    
    // Устанавливаем заголовок axios
    if (window.axios) {
      window.axios.defaults.headers.common['X-Locale'] = currentLocale
    }
    
    console.log(`🌍 i18n initialized with locale: ${currentLocale}`)
  }
  
  return currentLocale
}

