import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { getCurrentLocale, setLocale, isValidLocale, getLocaleName, getLocaleFlag } from '../utils/i18n'

export function useTranslations() {
    const page = usePage()
    
    // Используем серверный locale как основной источник
    const locale = computed(() => {
        const serverLocale = page.props.locale
        
        // Если серверный язык есть и валидный - используем его
        if (serverLocale && isValidLocale(serverLocale)) {
            // Синхронизируем cookie с сервером
            const localLocale = getCurrentLocale()
            if (localLocale !== serverLocale) {
                setLocale(serverLocale)
            }
            return serverLocale
        }
        
        // Если серверного нет, используем локальный
        const localLocale = getCurrentLocale()
        if (localLocale && isValidLocale(localLocale)) {
            return localLocale
        }
        
        // Fallback на default
        return 'ru'
    })
    
    const translations = computed(() => {
        return page.props.translations || {}
    })
    
    const changeLocale = async (newLocale) => {
        if (!isValidLocale(newLocale)) {
            console.error('❌ Invalid locale:', newLocale)
            return
        }
        
        const currentLocale = locale.value
        if (newLocale === currentLocale) {
            console.log('ℹ️ Locale already set to:', newLocale)
            return
        }
        
        console.log('🌍 Changing locale from', currentLocale, 'to', newLocale)
        
        // Сохраняем в cookie
        setLocale(newLocale)
        
        try {
            // Получаем CSRF токен
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            if (!csrfToken) {
                console.error('❌ CSRF token not found')
                // Используем простой редирект с параметром языка
                const currentUrl = window.location.href
                const url = new URL(currentUrl)
                url.searchParams.set('lang', newLocale)
                window.location.href = url.toString()
                return
            }
            
            // Отправляем POST запрос для синхронизации с сервером
            const response = await fetch('/locale/change', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: JSON.stringify({ locale: newLocale })
            })
            
            if (response.redirected || response.ok) {
                // Сервер вернет редирект, просто следуем ему
                if (response.redirected) {
                    window.location.href = response.url
                } else {
                    // Если нет редиректа, используем простой редирект с параметром
                    const currentUrl = window.location.href
                    const url = new URL(currentUrl)
                    url.searchParams.set('lang', newLocale)
                    window.location.href = url.toString()
                }
            } else {
                console.warn('⚠️ Failed to sync locale with server')
                // Используем простой редирект с параметром языка
                const currentUrl = window.location.href
                const url = new URL(currentUrl)
                url.searchParams.set('lang', newLocale)
                window.location.href = url.toString()
            }
        } catch (error) {
            console.error('❌ Exception changing locale:', error)
            // Используем простой редирект с параметром языка
            const currentUrl = window.location.href
            const url = new URL(currentUrl)
            url.searchParams.set('lang', newLocale)
            window.location.href = url.toString()
        }
    }
    
    const __ = (key, replacements = {}) => {
        const parts = key.split('.')
        let translation = key
        
        if (parts.length === 2) {
            const [file, translationKey] = parts
            if (translations.value[file] && translations.value[file][translationKey]) {
                translation = translations.value[file][translationKey]
            }
        } else if (parts.length === 1) {
            for (const file in translations.value) {
                if (translations.value[file][key]) {
                    translation = translations.value[file][key]
                    break
                }
            }
        }
        
        Object.keys(replacements).forEach(placeholder => {
            translation = String(translation).replace(`:${placeholder}`, replacements[placeholder])
        })
        
        return translation
    }
    
    return {
        locale,
        changeLocale,
        __,
        getLocaleName,
        getLocaleFlag,
        translations
    }
}
