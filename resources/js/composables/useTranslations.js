import { computed } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import { getCurrentLocale, setLocale, isValidLocale, getLocaleName, getLocaleFlag } from '../utils/i18n'

export function useTranslations() {
    const page = usePage()
    
    // Используем localStorage как основной источник, но синхронизируем с сервером
    const locale = computed(() => {
        // Приоритет: props (с сервера) > localStorage > default
        const serverLocale = page.props.locale
        const localLocale = getCurrentLocale()
        
        // Если серверный язык отличается от локального, синхронизируем
        if (serverLocale && serverLocale !== localLocale && isValidLocale(serverLocale)) {
            setLocale(serverLocale)
            return serverLocale
        }
        
        // Используем локальный язык
        if (localLocale && isValidLocale(localLocale)) {
            return localLocale
        }
        
        // Fallback на серверный или default
        return serverLocale || 'ru'
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
        
        // Сразу сохраняем в localStorage для быстрого отклика
        setLocale(newLocale)
        
        // Обновляем заголовок axios для последующих запросов
        if (typeof window !== 'undefined' && window.axios) {
            window.axios.defaults.headers.common['X-Locale'] = newLocale
        }
        
        try {
            // Получаем CSRF токен
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            if (!csrfToken) {
                console.error('❌ CSRF token not found')
                // Используем полную перезагрузку страницы
                window.location.reload()
                return
            }
            
            // Отправляем POST запрос для синхронизации с сервером
            const response = await fetch('/locale/change', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Locale': newLocale, // Отправляем язык в заголовке
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: JSON.stringify({ locale: newLocale })
            })
            
            if (response.ok) {
                const data = await response.json()
                console.log('✅ Locale synchronized with server:', data)
                
                // Используем полную перезагрузку страницы для гарантии обновления переводов
                // Это гарантирует, что сервер получит заголовок X-Locale и вернет правильные переводы
                window.location.reload()
            } else {
                console.warn('⚠️ Failed to sync locale with server, but locale saved locally')
                // Используем полную перезагрузку страницы
                window.location.reload()
            }
        } catch (error) {
            console.error('❌ Exception changing locale:', error)
            // Используем полную перезагрузку страницы
            window.location.reload()
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
