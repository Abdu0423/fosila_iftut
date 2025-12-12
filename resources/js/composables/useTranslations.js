import { computed } from 'vue'
import { usePage, router } from '@inertiajs/vue3'

export function useTranslations() {
    const page = usePage()
    
    const locale = computed(() => {
        return page.props.locale || 'tg'
    })
    
    const translations = computed(() => {
        return page.props.translations || {}
    })
    
    const changeLocale = async (newLocale) => {
        if (!['ru', 'tg'].includes(newLocale)) {
            console.error('❌ Invalid locale:', newLocale)
            return
        }
        
        console.log('🌍 Changing locale to:', newLocale)
        
        try {
            // Получаем CSRF токен
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            if (!csrfToken) {
                console.error('❌ CSRF token not found')
                return
            }
            
            // Отправляем POST запрос
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
            
            if (response.ok) {
                const data = await response.json()
                console.log('✅ Locale changed:', data)
                
                // Сохраняем в localStorage
                localStorage.setItem('locale', newLocale)
                
                // Перезагружаем страницу через Inertia без добавления параметров к URL
                router.reload({
                    only: ['locale', 'translations'],
                    preserveState: false,
                    preserveScroll: false
                })
            } else {
                const error = await response.json().catch(() => ({ message: 'Unknown error' }))
                console.error('❌ Error changing locale:', error)
                alert('Ошибка при смене языка: ' + (error.message || 'Неизвестная ошибка'))
            }
        } catch (error) {
            console.error('❌ Exception changing locale:', error)
            alert('Ошибка при смене языка: ' + error.message)
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
    
    return {
        locale,
        changeLocale,
        __,
        getLocaleName,
        getLocaleFlag,
        translations
    }
}
