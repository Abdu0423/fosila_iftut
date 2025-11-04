<template>
  <Layout role="teacher">
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center mb-6">
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">Мои уроки</h1>
              <p class="text-body-1 text-medium-emphasis">Выберите расписание для управления уроками</p>
            </div>
          </div>
        </v-col>
      </v-row>

      <!-- Уведомления -->
      <v-row v-if="page.props.flash?.success">
        <v-col cols="12">
          <v-alert
            type="success"
            variant="tonal"
            closable
          >
            {{ page.props.flash.success }}
          </v-alert>
        </v-col>
      </v-row>

      <!-- Список расписаний -->
      <v-row v-if="schedules.length > 0">
        <v-col
          v-for="schedule in schedules"
          :key="schedule.id"
          cols="12"
          md="6"
          lg="4"
        >
          <div
            class="schedule-card-wrapper"
            @click="viewSchedule(schedule)"
          >
            <v-card
              class="schedule-card"
              hover
            >
              <v-card-title class="d-flex justify-space-between align-center">
                <div>
                  <h3 class="text-h6">{{ schedule.subject?.name || 'Без предмета' }}</h3>
                  <p class="text-body-2 text-medium-emphasis mb-0">
                    {{ schedule.group?.name || 'Группа не указана' }}
                  </p>
                </div>
                <v-chip
                  :color="schedule.is_active ? 'success' : 'grey'"
                  size="small"
                >
                  {{ schedule.is_active ? 'Активно' : 'Неактивно' }}
                </v-chip>
              </v-card-title>

              <v-card-text>
                <div class="d-flex align-center justify-space-between">
                  <div class="d-flex align-center">
                    <v-icon class="mr-2" color="info" size="24">mdi-teach</v-icon>
                    <div>
                      <div class="text-h5 font-weight-bold">{{ schedule.lessons_count || 0 }}</div>
                      <div class="text-caption text-medium-emphasis">Уроков</div>
                    </div>
                  </div>
                  <v-icon color="primary">mdi-chevron-right</v-icon>
                </div>
                
                <v-divider class="my-3"></v-divider>
                
                <div class="text-caption text-medium-emphasis">
                  <div v-if="schedule.scheduled_at" class="mb-1">
                    <v-icon size="14">mdi-calendar-clock</v-icon>
                    {{ schedule.scheduled_at }}
                  </div>
                  <div v-if="schedule.semester" class="mb-1">
                    <v-icon size="14">mdi-school</v-icon>
                    Семестр {{ schedule.semester }}
                  </div>
                  <div v-if="schedule.credits">
                    <v-icon size="14">mdi-credit-card</v-icon>
                    {{ schedule.credits }} кредитов
                  </div>
                </div>
              </v-card-text>
            </v-card>
          </div>
        </v-col>
      </v-row>

      <!-- Пустое состояние -->
      <v-row v-else>
        <v-col cols="12">
          <v-card>
            <v-card-text class="text-center py-8">
              <v-icon size="64" color="grey-lighten-1" class="mb-4">mdi-calendar-outline</v-icon>
              <h3 class="text-h6 mb-2">У вас пока нет расписаний</h3>
              <p class="text-body-2 text-grey">Расписания будут отображаться здесь после их создания</p>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </Layout>
</template>

<script setup>
import { usePage, router } from '@inertiajs/vue3'
import Layout from '../../Layout.vue'

const page = usePage()

// Props
const props = defineProps({
  schedules: {
    type: Array,
    default: () => []
  }
})

// Методы
const viewSchedule = (schedule) => {
  console.log('Переход к расписанию:', schedule.id)
  if (!schedule || !schedule.id) {
    console.error('Расписание не найдено или нет ID')
    return
  }
  
  router.get(`/teacher/lessons/schedule/${schedule.id}`, {}, {
    preserveState: false,
    preserveScroll: false,
    onError: (errors) => {
      console.error('Ошибка при переходе к расписанию:', errors)
    },
    onSuccess: () => {
      console.log('Успешно перешли к расписанию')
    }
  })
}
</script>

<style scoped>
.v-card {
  border-radius: 12px;
}

.schedule-card-wrapper {
  cursor: pointer;
  height: 100%;
}

.schedule-card {
  transition: transform 0.2s, box-shadow 0.2s;
  height: 100%;
}

.schedule-card-wrapper:hover .schedule-card {
  transform: translateY(-4px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12) !important;
}
</style>
