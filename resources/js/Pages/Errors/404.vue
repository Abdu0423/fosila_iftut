<template>
  <v-app>
    <v-main class="d-flex align-center justify-center" style="min-height: 100vh; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
      <v-container>
        <v-row justify="center">
          <v-col cols="12" sm="10" md="8" lg="6">
            <v-card class="pa-8 text-center" elevation="12" rounded="xl">
              <!-- Анимированный 404 -->
              <div class="error-code mb-4">
                <span class="digit">4</span>
                <span class="digit zero">
                  <v-icon size="120" color="primary">mdi-emoticon-sad-outline</v-icon>
                </span>
                <span class="digit">4</span>
              </div>
              
              <h1 class="text-h4 font-weight-bold mb-3 text-grey-darken-3">
                Страница не найдена
              </h1>
              
              <p class="text-body-1 text-grey mb-6">
                К сожалению, запрашиваемая страница не существует или была перемещена.
                <br>
                Возможно, вы перешли по устаревшей ссылке или ввели неверный адрес.
              </p>
              
              <v-divider class="mb-6"></v-divider>
              
              <div class="d-flex flex-column flex-sm-row gap-3 justify-center">
                <v-btn
                  color="primary"
                  size="large"
                  rounded="lg"
                  prepend-icon="mdi-home"
                  @click="goHome"
                >
                  На главную
                </v-btn>
                
                <v-btn
                  variant="outlined"
                  color="secondary"
                  size="large"
                  rounded="lg"
                  prepend-icon="mdi-arrow-left"
                  @click="goBack"
                >
                  Назад
                </v-btn>
                
                <v-btn
                  variant="outlined"
                  color="primary"
                  size="large"
                  rounded="lg"
                  prepend-icon="mdi-login"
                  @click="goLogin"
                >
                  Войти
                </v-btn>
              </div>
              
              <!-- Подсказки -->
              <v-alert
                type="info"
                variant="tonal"
                class="mt-6 text-left"
                density="compact"
              >
                <template v-slot:title>Что можно сделать?</template>
                <ul class="mt-2 pl-4">
                  <li>Проверьте правильность введённого адреса</li>
                  <li>Вернитесь на главную страницу</li>
                  <li>Войдите в систему заново</li>
                </ul>
              </v-alert>
            </v-card>
          </v-col>
        </v-row>
      </v-container>
    </v-main>
  </v-app>
</template>

<script setup>
import { router } from '@inertiajs/vue3'

const goHome = () => {
  window.location.href = '/'
}

const goBack = () => {
  if (window.history.length > 1) {
    window.history.back()
  } else {
    window.location.href = '/'
  }
}

const goLogin = () => {
  window.location.href = '/login'
}
</script>

<style scoped>
.error-code {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.digit {
  font-size: 120px;
  font-weight: 900;
  color: #667eea;
  line-height: 1;
}

.digit.zero {
  display: flex;
  align-items: center;
  justify-content: center;
}

@keyframes bounce {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-10px);
  }
}

.digit.zero :deep(.v-icon) {
  animation: bounce 2s ease-in-out infinite;
}
</style>
