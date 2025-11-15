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
            <h1 class="text-h3 font-weight-bold text-primary mb-2">ИФТУТ</h1>
            <p class="text-body-1 text-medium-emphasis">Система управления образованием</p>
          </div>

          <v-card-text class="pa-8 pt-0">
            <form @submit.prevent="submit" class="login-form">
              <!-- Email или телефон поле -->
              <v-text-field
                v-model="form.login"
                label="Email или номер телефона"
                prepend-inner-icon="mdi-account"
                variant="outlined"
                rounded="lg"
                :error-messages="form.errors.login || form.errors.email"
                :disabled="form.processing"
                class="mb-4"
                autocomplete="username"
                required
                hint="Введите email или номер телефона"
                persistent-hint
              ></v-text-field>

              <!-- Пароль поле -->
              <v-text-field
                v-model="form.password"
                label="Пароль"
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

              <!-- Запомнить меня -->
              <div class="d-flex justify-space-between align-center mb-6">
                <v-checkbox
                  v-model="form.remember"
                  label="Запомнить меня"
                  color="primary"
                  hide-details
                ></v-checkbox>
                
                <v-btn
                  variant="text"
                  color="primary"
                  size="small"
                  class="text-none"
                >
                  Забыли пароль?
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
                :disabled="form.processing"
                class="login-btn mb-6"
                elevation="4"
              >
                <v-icon start>mdi-login</v-icon>
                {{ form.processing ? 'Вход...' : 'Войти в систему' }}
              </v-btn>

              <!-- Тестовые данные -->
              <v-alert
                type="info"
                variant="tonal"
                class="mb-4"
                rounded="lg"
              >
                <template v-slot:prepend>
                  <v-icon>mdi-information</v-icon>
                </template>
                <div class="text-body-2">
                  <strong>Тестовые данные:</strong><br>
                  Email: <code>admin@fosila.com</code> или номер телефона<br>
                  Пароль: <code>password</code>
                </div>
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
                © {{ currentYear }} ИФТУТ. Все права защищены.
              </p>
              <div class="d-flex justify-center gap-4">
                <v-btn
                  variant="text"
                  size="small"
                  color="primary"
                  class="text-none"
                >
                  Политика конфиденциальности
                </v-btn>
                <v-btn
                  variant="text"
                  size="small"
                  color="primary"
                  class="text-none"
                >
                  Условия использования
                </v-btn>
              </div>
            </div>
          </v-card-actions>
        </v-card>

        <!-- Дополнительная информация -->
        <div class="login-info">
          <div class="info-card">
            <v-icon size="48" color="white" class="mb-4">mdi-lightbulb</v-icon>
            <h3 class="text-h5 font-weight-bold text-white mb-2">Добро пожаловать!</h3>
            <p class="text-body-1 text-white opacity-75">
              Войдите в систему управления образованием ИФТУТ для доступа к панели администратора.
            </p>
          </div>
        </div>
      </div>
    </v-main>
  </v-app>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useForm, router } from '@inertiajs/vue3'

const showPassword = ref(false)

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
</style>
