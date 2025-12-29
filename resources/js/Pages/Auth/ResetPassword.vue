<template>
  <v-app class="login-app">
    <v-main class="login-main">
      <div class="login-container">
        <v-card class="login-card" elevation="24" rounded="xl">
          <div class="login-header">
            <v-icon size="64" color="primary" class="mb-4">mdi-lock-reset</v-icon>
            <h1 class="text-h3 font-weight-bold text-primary mb-2">Сброс пароля</h1>
            <p class="text-body-1 text-medium-emphasis">Введите код подтверждения и новый пароль</p>
          </div>

          <v-card-text class="pa-8 pt-0">
            <form @submit.prevent="resetPassword" class="login-form">
              <!-- Телефон (скрытое поле) -->
              <input type="hidden" v-model="form.phone" />
              
              <!-- Код подтверждения -->
              <v-text-field
                v-model="form.code"
                label="Код подтверждения *"
                prepend-inner-icon="mdi-key"
                variant="outlined"
                rounded="lg"
                :error-messages="form.errors.code"
                :disabled="form.processing"
                class="mb-2"
                maxlength="6"
                hint="6-значный код, отправленный на ваш телефон"
                persistent-hint
                required
              ></v-text-field>

              <!-- Повторная отправка кода -->
              <div class="mb-4 text-center">
                <v-btn
                  variant="text"
                  color="primary"
                  size="small"
                  :disabled="!canResend || form.processing"
                  @click="resendCode"
                >
                  <v-icon start>mdi-refresh</v-icon>
                  {{ canResend ? 'Отправить код повторно' : `Повторная отправка через ${resendCountdown} сек` }}
                </v-btn>
              </div>

              <!-- Новый пароль -->
              <v-text-field
                v-model="form.password"
                :label="'Новый пароль *'"
                :type="showPassword ? 'text' : 'password'"
                prepend-inner-icon="mdi-lock"
                :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
                @click:append-inner="showPassword = !showPassword"
                variant="outlined"
                rounded="lg"
                :error-messages="form.errors.password"
                :disabled="form.processing"
                class="mb-4"
                hint="Минимум 4 символа"
                persistent-hint
                required
              ></v-text-field>

              <!-- Подтверждение пароля -->
              <v-text-field
                v-model="form.password_confirmation"
                :label="'Подтверждение пароля *'"
                :type="showPasswordConfirmation ? 'text' : 'password'"
                prepend-inner-icon="mdi-lock-check"
                :append-inner-icon="showPasswordConfirmation ? 'mdi-eye-off' : 'mdi-eye'"
                @click:append-inner="showPasswordConfirmation = !showPasswordConfirmation"
                variant="outlined"
                rounded="lg"
                :error-messages="form.errors.password_confirmation"
                :disabled="form.processing"
                class="mb-6"
                required
              ></v-text-field>

              <!-- Ошибки -->
              <v-alert
                v-if="Object.keys(form.errors).length > 0"
                type="error"
                variant="tonal"
                class="mb-4"
                rounded="lg"
              >
                <div v-for="error in Object.values(form.errors)" :key="error">
                  {{ error }}
                </div>
              </v-alert>

              <!-- Кнопка сброса -->
              <v-btn
                type="submit"
                color="primary"
                size="large"
                block
                rounded="lg"
                :loading="form.processing"
                :disabled="form.processing || !form.code || form.code.length !== 6 || !form.password"
                class="login-btn mb-4"
                elevation="4"
              >
                <v-icon start>mdi-check</v-icon>
                {{ form.processing ? 'Сброс...' : 'Сбросить пароль' }}
              </v-btn>

              <!-- Кнопка назад -->
              <v-btn
                variant="text"
                color="primary"
                size="large"
                block
                rounded="lg"
                @click="goToForgotPassword"
                :disabled="form.processing"
              >
                <v-icon start>mdi-arrow-left</v-icon>
                Вернуться назад
              </v-btn>
            </form>
          </v-card-text>
        </v-card>
      </div>
      
      <!-- Переключатель языка -->
      <div class="language-switcher-wrapper">
        <LanguageSwitcher />
      </div>
    </v-main>
  </v-app>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useForm, router, usePage } from '@inertiajs/vue3'
import LanguageSwitcher from '../../Components/LanguageSwitcher.vue'

const page = usePage()
const props = defineProps({
  phone: {
    type: String,
    required: true
  }
})

const showPassword = ref(false)
const showPasswordConfirmation = ref(false)
const canResend = ref(false)
const resendCountdown = ref(0)

const form = useForm({
  phone: props.phone,
  code: '',
  password: '',
  password_confirmation: '',
})

// Таймер для повторной отправки (1 минута)
const startResendTimer = () => {
  canResend.value = false
  resendCountdown.value = 60
  const timer = setInterval(() => {
    resendCountdown.value--
    if (resendCountdown.value <= 0) {
      canResend.value = true
      clearInterval(timer)
    }
  }, 1000)
}

// Запускаем таймер при загрузке
onMounted(() => {
  startResendTimer()
})

const resendCode = () => {
  router.post('/password/forgot/send-code', {
    phone: props.phone,
  }, {
    onSuccess: () => {
      startResendTimer()
    },
  })
}

const resetPassword = () => {
  form.post('/password/reset', {
    onSuccess: () => {
      router.visit('/login')
    },
  })
}

const goToForgotPassword = () => {
  router.visit('/password/forgot')
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
  max-width: 500px;
  margin: 0 auto;
}

.login-card {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.2);
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
}

.language-switcher-wrapper {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 1000;
}
</style>

