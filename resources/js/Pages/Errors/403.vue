<template>
  <v-app>
    <v-main class="d-flex align-center justify-center" style="min-height: 100vh; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
      <v-container>
        <v-row justify="center">
          <v-col cols="12" sm="10" md="8" lg="6">
            <v-card class="pa-8 text-center" elevation="12" rounded="xl">
              <!-- Иконка -->
              <div class="mb-4">
                <v-icon size="120" color="error">mdi-shield-lock-outline</v-icon>
              </div>
              
              <h1 class="text-h3 font-weight-bold mb-2" style="color: #f5576c;">
                403
              </h1>
              
              <h2 class="text-h5 font-weight-medium mb-3 text-grey-darken-3">
                Доступ запрещён
              </h2>
              
              <p class="text-body-1 text-grey mb-6">
                У вас нет прав для просмотра этой страницы.
                <br>
                Обратитесь к администратору, если считаете это ошибкой.
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
            </v-card>
          </v-col>
        </v-row>
      </v-container>
    </v-main>
  </v-app>
</template>

<script setup>
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
