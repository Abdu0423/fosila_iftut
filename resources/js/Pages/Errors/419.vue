<template>
  <v-app>
    <v-main class="d-flex align-center justify-center" style="min-height: 100vh; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
      <v-container>
        <v-row justify="center">
          <v-col cols="12" sm="10" md="8" lg="6">
            <v-card class="pa-8 text-center" elevation="12" rounded="xl">
              <!-- Иконка -->
              <div class="mb-4">
                <v-icon size="120" color="info">mdi-clock-alert-outline</v-icon>
              </div>
              
              <h1 class="text-h3 font-weight-bold mb-2" style="color: #4facfe;">
                419
              </h1>
              
              <h2 class="text-h5 font-weight-medium mb-3 text-grey-darken-3">
                Сессия истекла
              </h2>
              
              <p class="text-body-1 text-grey mb-6">
                Ваша сессия истекла из-за длительного бездействия.
                <br>
                Пожалуйста, войдите в систему заново.
              </p>
              
              <v-divider class="mb-6"></v-divider>
              
              <div class="d-flex flex-column flex-sm-row gap-3 justify-center">
                <v-btn
                  color="primary"
                  size="large"
                  rounded="lg"
                  prepend-icon="mdi-login"
                  @click="goLogin"
                >
                  Войти заново
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
              
              <v-alert
                type="info"
                variant="tonal"
                class="mt-6 text-left"
                density="compact"
              >
                <template v-slot:title>Почему это произошло?</template>
                <p class="mt-2 mb-0">
                  Для вашей безопасности сессия автоматически завершается 
                  после определённого периода неактивности.
                </p>
              </v-alert>
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

const goLogin = () => {
  window.location.href = '/login'
}
</script>
