<template>
  <v-app>
    <v-main class="d-flex align-center justify-center" style="min-height: 100vh; background: linear-gradient(135deg, #434343 0%, #000000 100%);">
      <v-container>
        <v-row justify="center">
          <v-col cols="12" sm="10" md="8" lg="6">
            <v-card class="pa-8 text-center" elevation="12" rounded="xl">
              <!-- Иконка -->
              <div class="mb-4">
                <v-icon size="120" color="warning">mdi-alert-circle-outline</v-icon>
              </div>
              
              <h1 class="text-h3 font-weight-bold mb-2" style="color: #ff9800;">
                500
              </h1>
              
              <h2 class="text-h5 font-weight-medium mb-3 text-grey-darken-3">
                Внутренняя ошибка сервера
              </h2>
              
              <p class="text-body-1 text-grey mb-6">
                Произошла непредвиденная ошибка.
                <br>
                Мы уже работаем над её устранением. Попробуйте позже.
              </p>
              
              <v-divider class="mb-6"></v-divider>
              
              <div class="d-flex flex-column flex-sm-row gap-3 justify-center">
                <v-btn
                  color="primary"
                  size="large"
                  rounded="lg"
                  prepend-icon="mdi-refresh"
                  @click="refresh"
                >
                  Обновить страницу
                </v-btn>
                
                <v-btn
                  variant="outlined"
                  color="secondary"
                  size="large"
                  rounded="lg"
                  prepend-icon="mdi-home"
                  @click="goHome"
                >
                  На главную
                </v-btn>
              </div>
            </v-card>
          </v-col>
        </v-row>
      </v-container>
    </v-main>
  </v-app>
</template>

<script setup lang="ts">
const goHome = () => {
  window.location.href = '/'
}

const refresh = () => {
  window.location.reload()
}
</script>
