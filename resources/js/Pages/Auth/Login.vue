<template>
  <v-app class="login-app">
    <v-main class="login-main">
      <div class="login-container">
        <!-- Фоновые элементы -->
        <div class="background-shapes">
          <div class="shape shape-1"></div>
          <div class="shape shape-2"></div>
          <div class="shape shape-3"></div>
          <div class="shape shape-4"></div>
        </div>

        <!-- Основная форма входа -->
        <v-card class="login-card" elevation="24" rounded="xl">
          <div class="login-header">
            <v-icon size="64" color="primary" class="mb-4">mdi-school</v-icon>
            <h1 class="text-h3 font-weight-bold text-primary mb-2">{{ translations.auth?.login_title || 'ИФТУТ' }}</h1>
            <p class="text-body-1 text-medium-emphasis">{{ translations.auth?.login_subtitle || 'Системаи идоракунии таълим' }}</p>
          </div>

          <v-card-text class="pa-8 pt-0">
            <form @submit.prevent="submit" class="login-form">
              <!-- Email или телефон поле -->
              <v-text-field
                v-model="form.login"
                :label="translations.auth?.email_or_phone || 'Email ё рақами телефон'"
                prepend-inner-icon="mdi-account"
                variant="outlined"
                rounded="lg"
                :error-messages="form.errors.login || form.errors.email"
                :disabled="form.processing"
                class="mb-4"
                autocomplete="username"
                required
                :hint="translations.auth?.email_or_phone_hint || 'Email ё рақами телефонро ворид кунед'"
                persistent-hint
              ></v-text-field>

              <!-- Пароль поле -->
              <v-text-field
                v-model="form.password"
                :label="translations.auth?.password_label || 'Парол'"
                :type="showPassword ? 'text' : 'password'"
                prepend-inner-icon="mdi-lock"
                :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
                @click:append-inner="showPassword = !showPassword"
                variant="outlined"
                rounded="lg"
                :error-messages="form.errors.password"
                :disabled="form.processing"
                class="mb-6"
                autocomplete="current-password"
                required
              ></v-text-field>

              <!-- Согласие с политикой конфиденциальности -->
              <v-checkbox
                v-model="agreeToTerms"
                color="primary"
                class="mb-4"
                density="compact"
              >
                <template v-slot:label>
                  <span class="text-body-2">
                    {{ translations.auth?.agree_to_terms || 'Ман розӣ ҳастам' }}
                    <a 
                      href="#" 
                      @click.prevent="showPrivacyDialog = true" 
                      class="text-primary text-decoration-none"
                    >
                      {{ translations.auth?.privacy_policy || 'Сиёсати махфият' }}
                    </a>
                    {{ translations.auth?.and || 'ва' }}
                    <a 
                      href="#" 
                      @click.prevent="showTermsDialog = true" 
                      class="text-primary text-decoration-none"
                    >
                      {{ translations.auth?.terms_of_service || 'Шартҳои истифода' }}
                    </a>
                  </span>
                </template>
              </v-checkbox>

              <!-- Запомнить меня -->
              <div class="d-flex justify-space-between align-center mb-6">
                <v-checkbox
                  v-model="form.remember"
                  :label="translations.auth?.remember_me || 'Маро ба хотир гиред'"
                  color="primary"
                  hide-details
                ></v-checkbox>
                
                <v-btn
                  variant="text"
                  color="primary"
                  size="small"
                  class="text-none"
                >
                  {{ translations.auth?.forgot_password || 'Паролро фаромӯш кардед?' }}
                </v-btn>
              </div>

              <!-- Кнопка входа -->
              <v-btn
                type="submit"
                color="primary"
                size="large"
                block
                rounded="lg"
                :loading="form.processing"
                :disabled="form.processing || !agreeToTerms"
                class="login-btn mb-6"
                elevation="4"
              >
                <v-icon start>mdi-login</v-icon>
                {{ form.processing ? (translations.auth?.logging_in || 'Вуруд...') : (translations.auth?.login_button || 'Ворид шудан ба система') }}
              </v-btn>
              
              <!-- Предупреждение если не согласен -->
              <v-alert
                v-if="!agreeToTerms"
                type="info"
                variant="tonal"
                density="compact"
                class="mb-4"
              >
                <span class="text-caption">
                  {{ translations.auth?.must_agree || 'Барои вуруд зарур аст, ки бо сиёсати махфият ва шартҳои истифода розӣ шавед' }}
                </span>
              </v-alert>

              <!-- Ошибки -->
              <v-alert
                v-if="Object.keys(form.errors).length > 0"
                type="error"
                variant="tonal"
                class="mb-4"
                rounded="lg"
              >
                <template v-slot:prepend>
                  <v-icon>mdi-alert-circle</v-icon>
                </template>
                <div class="text-body-2">
                  <div v-for="error in Object.values(form.errors)" :key="error">
                    {{ error }}
                  </div>
                </div>
              </v-alert>
            </form>
          </v-card-text>

          <!-- Футер -->
          <v-card-actions class="pa-8 pt-0">
            <div class="text-center w-100">
              <p class="text-body-2 text-medium-emphasis mb-2">
                © {{ currentYear }} ИФТУТ. {{ translations.auth?.all_rights_reserved || 'Ҳамаи ҳуқуқҳо ҳифз шудаанд.' }}
              </p>
              <div class="d-flex justify-center gap-4">
                <v-btn
                  @click="showPrivacyDialog = true"
                  variant="text"
                  size="small"
                  color="primary"
                  class="text-none"
                >
                  {{ translations.auth?.privacy_policy || 'Сиёсати махфият' }}
                </v-btn>
                <v-btn
                  @click="showTermsDialog = true"
                  variant="text"
                  size="small"
                  color="primary"
                  class="text-none"
                >
                  {{ translations.auth?.terms_of_service || 'Шартҳои истифода' }}
                </v-btn>
              </div>
            </div>
          </v-card-actions>
        </v-card>

        <!-- Дополнительная информация -->
        <div class="login-info">
          <div class="info-card">
            <v-icon size="48" color="white" class="mb-4">mdi-lightbulb</v-icon>
            <h3 class="text-h5 font-weight-bold text-white mb-2">{{ translations.auth?.welcome || 'Хуш омадед!' }}</h3>
            <p class="text-body-1 text-white opacity-75">
              {{ translations.auth?.welcome_message || 'Барои дастрасӣ ба панели идоракунӣ ба системаи идоракунии таълими ИФТУТ ворид шавед.' }}
            </p>
          </div>
        </div>
      </div>
      
      <!-- Переключатель языка в правом верхнем углу -->
      <div class="language-switcher-wrapper">
        <LanguageSwitcher />
      </div>
    </v-main>

    <!-- Модальное окно - Политика конфиденциальности -->
    <v-dialog v-model="showPrivacyDialog" max-width="900" scrollable>
      <v-card>
        <v-card-title class="bg-primary pa-4 d-flex align-center">
          <v-icon color="white" class="mr-3">mdi-shield-lock</v-icon>
          <span class="text-white">Политика конфиденциальности</span>
          <v-spacer></v-spacer>
          <v-btn icon variant="text" @click="showPrivacyDialog = false">
            <v-icon color="white">mdi-close</v-icon>
          </v-btn>
        </v-card-title>
        
        <v-card-text class="pa-6" style="height: 600px;">
          <div class="legal-content">
            <p class="text-body-1 mb-4">
              Институт Финансов и Технологий Университета Таджикистана (ИФТУТ) уважает вашу конфиденциальность 
              и обязуется защищать ваши персональные данные.
            </p>

            <h3 class="text-h6 font-weight-bold mb-3">1. Собираемая информация</h3>
            <ul class="mb-4">
              <li>Персональные данные (ФИО, email, телефон, адрес)</li>
              <li>Академическая информация (оценки, посещаемость, задания)</li>
              <li>Техническая информация (IP-адрес, тип браузера, логи активности)</li>
            </ul>

            <h3 class="text-h6 font-weight-bold mb-3">2. Использование информации</h3>
            <ul class="mb-4">
              <li>Предоставление образовательных услуг</li>
              <li>Коммуникация (email, SMS уведомления)</li>
              <li>Обеспечение безопасности системы</li>
              <li>Улучшение качества сервиса</li>
            </ul>

            <h3 class="text-h6 font-weight-bold mb-3">3. Защита данных</h3>
            <ul class="mb-4">
              <li>SSL/TLS шифрование для передачи данных</li>
              <li>Bcrypt хеширование паролей</li>
              <li>Контроль доступа по ролям</li>
              <li>Регулярные резервные копии</li>
              <li>Мониторинг безопасности</li>
            </ul>

            <h3 class="text-h6 font-weight-bold mb-3">4. Передача данных третьим лицам</h3>
            <p class="mb-4">
              Мы не передаем ваши персональные данные третьим лицам, за исключением:
            </p>
            <ul class="mb-4">
              <li>SMS-провайдер (OsonSMS) для отправки уведомлений</li>
              <li>Государственные органы РТ по официальным запросам</li>
            </ul>

            <h3 class="text-h6 font-weight-bold mb-3">5. Ваши права</h3>
            <ul class="mb-4">
              <li>Доступ к своим данным</li>
              <li>Исправление неточной информации</li>
              <li>Удаление данных (с ограничениями)</li>
              <li>Ограничение обработки</li>
            </ul>

            <h3 class="text-h6 font-weight-bold mb-3">6. Cookies</h3>
            <p class="mb-4">
              Мы используем cookies для аутентификации, сохранения предпочтений и анализа использования системы.
            </p>

            <h3 class="text-h6 font-weight-bold mb-3">7. Контакты</h3>
            <p>
              По вопросам конфиденциальности: <a href="mailto:privacy@iftut.tj" class="text-primary">privacy@iftut.tj</a>
            </p>
          </div>
        </v-card-text>

        <v-divider></v-divider>

        <v-card-actions class="pa-4">
          <v-spacer></v-spacer>
          <v-btn
            color="primary"
            variant="elevated"
            @click="showPrivacyDialog = false"
          >
            Закрыть
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Модальное окно - Условия использования -->
    <v-dialog v-model="showTermsDialog" max-width="900" scrollable>
      <v-card>
        <v-card-title class="bg-primary pa-4 d-flex align-center">
          <v-icon color="white" class="mr-3">mdi-file-document</v-icon>
          <span class="text-white">Условия использования</span>
          <v-spacer></v-spacer>
          <v-btn icon variant="text" @click="showTermsDialog = false">
            <v-icon color="white">mdi-close</v-icon>
          </v-btn>
        </v-card-title>
        
        <v-card-text class="pa-6" style="height: 600px;">
          <div class="legal-content">
            <p class="text-body-1 mb-4">
              Используя систему ИФТУТ, вы соглашаетесь соблюдать следующие условия использования.
            </p>

            <h3 class="text-h6 font-weight-bold mb-3">1. Принятие условий</h3>
            <p class="mb-4">
              Используя систему, вы подтверждаете, что прочитали и согласны со всеми положениями настоящих Условий.
            </p>

            <h3 class="text-h6 font-weight-bold mb-3">2. Учетные записи</h3>
            <ul class="mb-4">
              <li>Учетные записи создаются администраторами ИФТУТ</li>
              <li>Вы обязаны сохранять конфиденциальность пароля</li>
              <li>Запрещено передавать доступ третьим лицам</li>
              <li>Необходимо сменить временный пароль при первом входе</li>
            </ul>

            <h3 class="text-h6 font-weight-bold mb-3">3. Правила использования</h3>
            <p class="mb-2"><strong>Разрешено:</strong></p>
            <ul class="mb-3">
              <li>Использование для образовательных целей</li>
              <li>Доступ к учебным материалам</li>
              <li>Взаимодействие с преподавателями</li>
            </ul>

            <p class="mb-2"><strong>Запрещено:</strong></p>
            <ul class="mb-4">
              <li>Несанкционированный доступ к системе</li>
              <li>Распространение вредоносного ПО</li>
              <li>Плагиат и списывание</li>
              <li>Размещение неприемлемого контента</li>
              <li>Продажа или передача учетной записи</li>
            </ul>

            <h3 class="text-h6 font-weight-bold mb-3">4. Академическая честность</h3>
            <ul class="mb-4">
              <li>Самостоятельное выполнение заданий</li>
              <li>Корректное цитирование источников</li>
              <li>Запрет на списывание и плагиат</li>
            </ul>

            <h3 class="text-h6 font-weight-bold mb-3">5. Ответственность</h3>
            <p class="mb-4">
              Вы несете полную ответственность за все действия, совершенные с использованием вашей учетной записи.
            </p>

            <h3 class="text-h6 font-weight-bold mb-3">6. Приостановка доступа</h3>
            <p class="mb-4">
              ИФТУТ оставляет за собой право приостановить доступ при нарушении настоящих Условий.
            </p>

            <h3 class="text-h6 font-weight-bold mb-3">7. Применимое право</h3>
            <p class="mb-4">
              Настоящие Условия регулируются законодательством Республики Таджикистан.
            </p>

            <h3 class="text-h6 font-weight-bold mb-3">8. Контакты</h3>
            <p>
              По вопросам условий использования: <a href="mailto:legal@iftut.tj" class="text-primary">legal@iftut.tj</a>
            </p>
          </div>
        </v-card-text>

        <v-divider></v-divider>

        <v-card-actions class="pa-4">
          <v-spacer></v-spacer>
          <v-btn
            color="primary"
            variant="elevated"
            @click="showTermsDialog = false"
          >
            Закрыть
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-app>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useForm, router, usePage } from '@inertiajs/vue3'
import LanguageSwitcher from '../../Components/LanguageSwitcher.vue'

const page = usePage()
const translations = computed(() => page.props.translations || {})

const showPassword = ref(false)
const agreeToTerms = ref(false)
const showPrivacyDialog = ref(false)
const showTermsDialog = ref(false)

const form = useForm({
  login: '',
  password: '',
  remember: false,
})

// Динамический год для футера
const currentYear = computed(() => new Date().getFullYear())

const submit = () => {
  console.log('Отправка формы входа:', form.data())
  
  form.post('/login', {
    onSuccess: (page) => {
      console.log('Успешный вход:', page)
      // Доверяем серверному перенаправлению - не делаем принудительный redirect
    },
    onError: (errors) => {
      console.log('Ошибки входа:', errors)
    },
    onFinish: () => {
      console.log('Запрос завершен')
    }
  })
}
</script>

<style scoped>
.login-app {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  min-height: 100vh;
}

.login-main {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  padding: 20px;
}

.login-container {
  position: relative;
  width: 100%;
  max-width: 1200px;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 40px;
  align-items: center;
}

.login-card {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  max-width: 500px;
  margin: 0 auto;
}

.login-header {
  text-align: center;
  padding: 40px 40px 20px;
}

.login-form {
  width: 100%;
}

.login-btn {
  height: 56px;
  font-size: 16px;
  font-weight: 600;
  text-transform: none;
  transition: all 0.3s ease;
}

.login-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.login-info {
  display: flex;
  align-items: center;
  justify-content: center;
}

.info-card {
  text-align: center;
  padding: 40px;
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 24px;
  max-width: 400px;
}

/* Фоновые элементы */
.background-shapes {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  overflow: hidden;
  z-index: -1;
}

.shape {
  position: absolute;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.1);
  animation: float 6s ease-in-out infinite;
}

.shape-1 {
  width: 100px;
  height: 100px;
  top: 20%;
  left: 10%;
  animation-delay: 0s;
}

.shape-2 {
  width: 150px;
  height: 150px;
  top: 60%;
  right: 10%;
  animation-delay: 2s;
}

.shape-3 {
  width: 80px;
  height: 80px;
  bottom: 20%;
  left: 20%;
  animation-delay: 4s;
}

.shape-4 {
  width: 120px;
  height: 120px;
  top: 10%;
  right: 30%;
  animation-delay: 1s;
}

@keyframes float {
  0%, 100% {
    transform: translateY(0px) rotate(0deg);
  }
  50% {
    transform: translateY(-20px) rotate(180deg);
  }
}

/* Анимации появления */
.login-card {
  animation: slideInUp 0.8s ease-out;
}

.info-card {
  animation: slideInRight 0.8s ease-out 0.2s both;
}

@keyframes slideInUp {
  from {
    opacity: 0;
    transform: translateY(50px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes slideInRight {
  from {
    opacity: 0;
    transform: translateX(50px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

/* Адаптивность */
@media (max-width: 1024px) {
  .login-container {
    grid-template-columns: 1fr;
    gap: 20px;
  }
  
  .login-info {
    order: -1;
  }
  
  .info-card {
    max-width: 100%;
  }
}

@media (max-width: 768px) {
  .login-main {
    padding: 10px;
  }
  
  .login-card {
    margin: 0;
  }
  
  .login-header {
    padding: 30px 20px 15px;
  }
  
  .v-card-text {
    padding: 20px;
  }
  
  .v-card-actions {
    padding: 20px;
  }
}

/* Дополнительные эффекты */
.v-text-field .v-field {
  transition: all 0.3s ease;
}

.v-text-field .v-field:focus-within {
  transform: scale(1.02);
}

/* Пульсация для кнопки */
.login-btn:not(:disabled):hover {
  animation: pulse 0.6s ease-in-out;
}

@keyframes pulse {
  0% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.05);
  }
  100% {
    transform: scale(1);
  }
}

/* Стили для модальных окон */
.legal-content {
  line-height: 1.8;
}

.legal-content h3 {
  margin-top: 20px;
  color: rgb(var(--v-theme-primary));
}

.legal-content ul {
  padding-left: 24px;
}

.legal-content li {
  margin-bottom: 8px;
}

.legal-content a {
  text-decoration: none;
}

.legal-content a:hover {
  text-decoration: underline;
}

.v-dialog .v-card-title {
  position: sticky;
  top: 0;
  z-index: 1;
}

/* Переключатель языка */
.language-switcher-wrapper {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 1000;
}
</style>
