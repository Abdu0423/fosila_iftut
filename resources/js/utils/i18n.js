/**
 * Простая утилита для управления языками
 * Использует cookie как основной источник
 */

const AVAILABLE_LOCALES = ['ru', 'tg']
const DEFAULT_LOCALE = 'ru'

/**
 * Получить текущий язык из cookie или вернуть язык по умолчанию
 */
export function getCurrentLocale() {
  if (typeof window === 'undefined') {
    return DEFAULT_LOCALE
  }
  
  // Проверяем cookie
  const cookies = document.cookie.split(';')
  for (let cookie of cookies) {
    const [name, value] = cookie.trim().split('=')
    if (name === 'locale' && value && AVAILABLE_LOCALES.includes(value)) {
      return value
    }
  }
  
  return DEFAULT_LOCALE
}

/**
 * Установить язык в cookie
 */
export function setLocale(locale) {
  if (!AVAILABLE_LOCALES.includes(locale)) {
    console.warn(`Invalid locale: ${locale}, falling back to ${DEFAULT_LOCALE}`)
    locale = DEFAULT_LOCALE
  }
  
  if (typeof window !== 'undefined') {
    // Сохраняем в cookie
    document.cookie = `locale=${locale}; path=/; max-age=31536000; SameSite=Lax`
    console.log(`✅ Locale saved to cookie: ${locale}`)
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
