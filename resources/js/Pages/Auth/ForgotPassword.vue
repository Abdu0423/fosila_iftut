<template>
  <v-app class="login-app">
    <v-main class="login-main">
      <div class="login-container">
        <v-card class="login-card" elevation="24" rounded="xl">
          <div class="login-header">
            <v-icon size="64" color="primary" class="mb-4">mdi-lock-reset</v-icon>
            <h1 class="text-h3 font-weight-bold text-primary mb-2">Восстановление пароля</h1>
            <p class="text-body-1 text-medium-emphasis">Введите номер телефона для получения кода подтверждения</p>
          </div>

          <v-card-text class="pa-8 pt-0">
            <form @submit.prevent="sendCode" class="login-form">
              <!-- Телефон -->
              <v-text-field
                v-model="form.phone"
                label="Номер телефона *"
                prepend-inner-icon="mdi-phone"
                variant="outlined"
                rounded="lg"
                :error-messages="form.errors.phone"
                :disabled="form.processing"
                class="mb-4"
                v-mask="'+992#########'"
                placeholder="+992XXXXXXXXX"
                required
                hint="Введите номер телефона, на который будет отправлен код подтверждения"
                persistent-hint
              ></v-text-field>

              <!-- Успешное сообщение -->
              <v-alert
                v-if="successMessage"
                type="success"
                variant="tonal"
                class="mb-4"
                rounded="lg"
              >
                {{ successMessage }}
              </v-alert>

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

              <!-- Кнопка отправки -->
              <v-btn
                type="submit"
                color="primary"
                size="large"
                block
                rounded="lg"
                :loading="form.processing"
                :disabled="form.processing || !form.phone || form.phone.length < 13"
                class="login-btn mb-4"
                elevation="4"
              >
                <v-icon start>mdi-send</v-icon>
                {{ form.processing ? 'Отправка...' : 'Отправить код' }}
              </v-btn>

              <!-- Кнопка назад -->
              <v-btn
                variant="text"
                color="primary"
                size="large"
                block
                rounded="lg"
                @click="goToLogin"
                :disabled="form.processing"
              >
                <v-icon start>mdi-arrow-left</v-icon>
                Вернуться к входу
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
import { ref, computed } from 'vue'
import { useForm, router, usePage } from '@inertiajs/vue3'
import LanguageSwitcher from '../../Components/LanguageSwitcher.vue'

const page = usePage()
const successMessage = ref(page.props.flash?.success || null)

const form = useForm({
  phone: '+992',
})

const sendCode = () => {
  form.post('/password/forgot/send-code', {
    onSuccess: (page) => {
      successMessage.value = page.props.flash?.success || 'Код подтверждения отправлен на ваш номер телефона'
      // Через 1.5 секунды перенаправляем на страницу ввода кода
      setTimeout(() => {
        router.visit(`/password/reset?phone=${encodeURIComponent(form.phone)}`)
      }, 1500)
    },
  })
}

const goToLogin = () => {
  router.visit('/login')
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

