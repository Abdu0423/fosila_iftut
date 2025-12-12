/**
 * Плагин для автоматического обновления Vue i18n при навигации Inertia
 * Использует глобальные свойства для отслеживания изменений
 */
export function createI18nUpdater(i18n) {
  return {
    install(app) {
      // Добавляем глобальное свойство для обновления i18n
      app.config.globalProperties.$updateI18n = (page) => {
        const locale = page?.props?.locale || 'ru';
        const translations = page?.props?.translations || {};
        
        if (locale !== i18n.global.locale.value) {
          i18n.global.locale.value = locale;
        }
        
        i18n.global.setLocaleMessage(locale, translations);
      };
      
      // Используем mixin для автоматического обновления в Options API компонентах
      app.mixin({
        mounted() {
          // Обновляем при монтировании компонента
          if (this.$page) {
            this.$updateI18n(this.$page);
          }
        },
        watch: {
          '$page.props.locale'(newLocale) {
            if (newLocale && this.$page) {
              this.$updateI18n(this.$page);
            }
          },
          '$page.props.translations'() {
            if (this.$page) {
              this.$updateI18n(this.$page);
            }
          }
        }
      });
    }
  };
}

