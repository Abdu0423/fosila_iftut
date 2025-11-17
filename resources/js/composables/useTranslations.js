import { ref, computed } from 'vue'
import { usePage, router } from '@inertiajs/vue3'

// Composable для использования переводов
export function useTranslations() {
    const page = usePage()
    
    // Получаем текущую локаль из данных страницы
    const locale = computed(() => {
        return page.props.locale || 'tg'
    })
    
    // Получаем переводы из props
    const translations = computed(() => {
        return page.props.translations || {}
    })
    
    // Функция для смены языка
    const changeLocale = async (newLocale) => {
        if (!['ru', 'tg'].includes(newLocale)) {
            console.error('Invalid locale:', newLocale)
            return
        }
        
        try {
            await router.post('/locale/change', {
                locale: newLocale
            }, {
                preserveState: false,
                preserveScroll: true,
                onSuccess: () => {
                    localStorage.setItem('locale', newLocale)
                    // Страница перезагрузится автоматически с preserveState: false
                }
            })
        } catch (error) {
            console.error('Error changing locale:', error)
        }
    }
    
    // Функция для получения перевода
    const __ = (key, replacements = {}) => {
        // В Laravel используется формат 'file.key' или просто 'key'
        const parts = key.split('.')
        let translation = key
        
        if (parts.length === 2) {
            const [file, translationKey] = parts
            
            // Получаем перевод из translations
            if (translations.value[file] && translations.value[file][translationKey]) {
                translation = translations.value[file][translationKey]
            }
        } else if (parts.length === 1) {
            // Пробуем найти ключ во всех файлах
            for (const file in translations.value) {
                if (translations.value[file][key]) {
                    translation = translations.value[file][key]
                    break
                }
            }
        }
        
        // Заменяем плейсхолдеры
        Object.keys(replacements).forEach(placeholder => {
            translation = String(translation).replace(`:${placeholder}`, replacements[placeholder])
        })
        
        return translation
    }
    
    // Получаем название языка
    const getLocaleName = (loc) => {
        const names = {
            ru: 'Русский',
            tg: 'Тоҷикӣ'
        }
        return names[loc] || loc
    }
    
    // Получаем флаг языка (эмодзи)
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

// Экспортируем также простую функцию для использования вне composable
export function trans(key, replacements = {}) {
    // Это упрощенная версия, которая возвращает ключ как есть
    // В реальном приложении здесь был бы доступ к хранилищу переводов
    let translation = key
    
    Object.keys(replacements).forEach(placeholder => {
        translation = translation.replace(`:${placeholder}`, replacements[placeholder])
    })
    
    return translation
}

