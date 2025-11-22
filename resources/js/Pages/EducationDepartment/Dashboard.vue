<template>
  <Layout role="education_department">
    <v-container fluid class="pa-6">
      <!-- Заголовок -->
      <div class="mb-6">
        <h1 class="text-h4 font-weight-bold mb-2">
          {{ translations.education_department?.dashboard_title || 'Панель отдела образования' }}
        </h1>
        <p class="text-body-1 text-medium-emphasis">
          {{ translations.education_department?.dashboard_subtitle || 'Управление образовательным процессом' }}
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
                    {{ translations.education_department?.total_users || 'Всего пользователей' }}
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
                    {{ translations.education_department?.total_teachers || 'Преподавателей' }}
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
                    {{ translations.education_department?.total_students || 'Студентов' }}
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
                    {{ translations.education_department?.total_subjects || 'Предметов' }}
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
          {{ translations.education_department?.quick_access || 'Быстрый доступ' }}
        </v-card-title>
        <v-divider></v-divider>
        <v-card-text>
          <v-row>
            <v-col cols="12" md="4">
              <v-card 
                variant="tonal" 
                color="primary" 
                class="cursor-pointer"
                @click="$inertia.visit(route('education.users.index'))"
              >
                <v-card-text class="text-center pa-6">
                  <v-icon size="48" class="mb-3">mdi-account-group</v-icon>
                  <h3 class="text-h6">{{ translations.education_department?.manage_users || 'Управление пользователями' }}</h3>
                </v-card-text>
              </v-card>
            </v-col>

            <v-col cols="12" md="4">
              <v-card 
                variant="tonal" 
                color="success" 
                class="cursor-pointer"
                @click="$inertia.visit(route('education.schedules.index'))"
              >
                <v-card-text class="text-center pa-6">
                  <v-icon size="48" class="mb-3">mdi-calendar-clock</v-icon>
                  <h3 class="text-h6">{{ translations.education_department?.view_schedules || 'Расписания' }}</h3>
                </v-card-text>
              </v-card>
            </v-col>

            <v-col cols="12" md="4">
              <v-card 
                variant="tonal" 
                color="warning" 
                class="cursor-pointer"
                @click="$inertia.visit(route('education.subjects.index'))"
              >
                <v-card-text class="text-center pa-6">
                  <v-icon size="48" class="mb-3">mdi-book-open-page-variant</v-icon>
                  <h3 class="text-h6">{{ translations.education_department?.manage_subjects || 'Управление предметами' }}</h3>
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

