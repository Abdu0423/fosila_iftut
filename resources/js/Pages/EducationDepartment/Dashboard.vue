<template>
  <Layout>
    <v-container fluid class="pa-6">
      <!-- Заголовок -->
      <div class="mb-6">
        <h1 class="text-h4 font-weight-bold mb-2">
          {{ translations.education_department?.dashboard_title }}
        </h1>
        <p class="text-body-1 text-medium-emphasis">
          {{ translations.education_department?.dashboard_subtitle }}
        </p>
      </div>

      <!-- Статистика -->
      <v-row>
        <v-col cols="12" md="3">
          <v-card class="h-100">
            <v-card-text>
              <div class="d-flex align-center justify-space-between">
                <div>
                  <p class="text-caption text-medium-emphasis mb-1">
                    {{ translations.education_department?.total_users }}
                  </p>
                  <h2 class="text-h4 font-weight-bold">{{ stats.total_users }}</h2>
                </div>
                <v-avatar color="primary" size="56">
                  <v-icon size="28">mdi-account-group</v-icon>
                </v-avatar>
              </div>
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12" md="3">
          <v-card class="h-100">
            <v-card-text>
              <div class="d-flex align-center justify-space-between">
                <div>
                  <p class="text-caption text-medium-emphasis mb-1">
                    {{ translations.education_department?.total_teachers }}
                  </p>
                  <h2 class="text-h4 font-weight-bold">{{ stats.total_teachers }}</h2>
                </div>
                <v-avatar color="success" size="56">
                  <v-icon size="28">mdi-account-tie</v-icon>
                </v-avatar>
              </div>
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12" md="3">
          <v-card class="h-100">
            <v-card-text>
              <div class="d-flex align-center justify-space-between">
                <div>
                  <p class="text-caption text-medium-emphasis mb-1">
                    {{ translations.education_department?.total_students }}
                  </p>
                  <h2 class="text-h4 font-weight-bold">{{ stats.total_students }}</h2>
                </div>
                <v-avatar color="info" size="56">
                  <v-icon size="28">mdi-school</v-icon>
                </v-avatar>
              </div>
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12" md="3">
          <v-card class="h-100">
            <v-card-text>
              <div class="d-flex align-center justify-space-between">
                <div>
                  <p class="text-caption text-medium-emphasis mb-1">
                    {{ translations.education_department?.total_subjects }}
                  </p>
                  <h2 class="text-h4 font-weight-bold">{{ stats.total_subjects }}</h2>
                </div>
                <v-avatar color="warning" size="56">
                  <v-icon size="28">mdi-book-open-page-variant</v-icon>
                </v-avatar>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Быстрый доступ -->
      <v-card class="mt-6">
        <v-card-title>
          <v-icon class="mr-2">mdi-lightning-bolt</v-icon>
          {{ translations.education_department?.quick_access }}
        </v-card-title>
        <v-divider></v-divider>
        <v-card-text>
          <v-row>
            <v-col cols="12" md="4">
              <v-card 
                variant="tonal" 
                color="primary" 
                class="cursor-pointer"
                @click="$inertia.visit(getRoute('users.index'))"
              >
                <v-card-text class="text-center pa-6">
                  <v-icon size="48" class="mb-3">mdi-account-group</v-icon>
                  <h3 class="text-h6">{{ translations.education_department?.manage_users }}</h3>
                </v-card-text>
              </v-card>
            </v-col>

            <v-col cols="12" md="4">
              <v-card 
                variant="tonal" 
                color="success" 
                class="cursor-pointer"
                @click="$inertia.visit(getRoute('schedules.index'))"
              >
                <v-card-text class="text-center pa-6">
                  <v-icon size="48" class="mb-3">mdi-calendar-clock</v-icon>
                  <h3 class="text-h6">{{ translations.education_department?.view_schedules }}</h3>
                </v-card-text>
              </v-card>
            </v-col>

            <v-col cols="12" md="4">
              <v-card 
                variant="tonal" 
                color="warning" 
                class="cursor-pointer"
                @click="$inertia.visit(getRoute('subjects.index'))"
              >
                <v-card-text class="text-center pa-6">
                  <v-icon size="48" class="mb-3">mdi-book-open-page-variant</v-icon>
                  <h3 class="text-h6">{{ translations.education_department?.manage_subjects }}</h3>
                </v-card-text>
              </v-card>
            </v-col>
          </v-row>
        </v-card-text>
      </v-card>
    </v-container>
  </Layout>
</template>

<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import Layout from '../Layout.vue'

const page = usePage()
const translations = computed(() => page.props.translations || {})

defineProps({
  stats: {
    type: Object,
    required: true
  }
})

// Определяем префикс маршрута в зависимости от текущего URL
const getRoutePrefix = () => {
  const path = window.location.pathname
  return path.startsWith('/registration') ? 'registration' : 'education'
}

// Функция для получения правильного маршрута
const getRoute = (routeName) => {
  const prefix = getRoutePrefix()
  return route(`${prefix}.${routeName}`)
}
</script>

<style scoped>
.cursor-pointer {
  cursor: pointer;
  transition: transform 0.2s;
}

.cursor-pointer:hover {
  transform: translateY(-4px);
}
</style>

