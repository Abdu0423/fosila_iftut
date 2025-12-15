<template>
  <Layout role="teacher">
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center mb-6">
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">{{ group.name }}</h1>
              <p class="text-body-1 text-medium-emphasis">{{ group.full_name }}</p>
            </div>
            <div class="d-flex gap-2">
              <v-btn
                color="secondary"
                variant="outlined"
                @click="navigateTo('/teacher/students')"
                prepend-icon="mdi-arrow-left"
              >
                Назад к студентам
              </v-btn>
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

      <!-- Статистика -->
      <v-row>
        <v-col cols="12" md="3">
          <v-card variant="outlined">
            <v-card-text class="text-center">
              <div class="text-h4 font-weight-bold text-primary">{{ stats.total_students }}</div>
              <div class="text-body-2 text-medium-emphasis">Студентов</div>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" md="3">
          <v-card variant="outlined">
            <v-card-text class="text-center">
              <div class="text-h4 font-weight-bold text-success">{{ stats.total_lessons }}</div>
              <div class="text-body-2 text-medium-emphasis">Уроков</div>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" md="3">
          <v-card variant="outlined">
            <v-card-text class="text-center">
              <div class="text-h4 font-weight-bold text-info">{{ stats.active_lessons }}</div>
              <div class="text-body-2 text-medium-emphasis">Активных уроков</div>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" md="3">
          <v-card variant="outlined">
            <v-card-text class="text-center">
              <div class="text-h4 font-weight-bold text-warning">{{ group.course }}</div>
              <div class="text-body-2 text-medium-emphasis">Курс</div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <v-row>
        <!-- Информация о группе -->
        <v-col cols="12" md="4">
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-information</v-icon>
              Информация о группе
            </v-card-title>
            <v-card-text>
              <v-list>
                <v-list-item>
                  <template v-slot:prepend>
                    <v-icon color="primary">mdi-account-group</v-icon>
                  </template>
                  <v-list-item-title>Название</v-list-item-title>
                  <v-list-item-subtitle>{{ group.name }}</v-list-item-subtitle>
                </v-list-item>
                
                <v-list-item>
                  <template v-slot:prepend>
                    <v-icon color="secondary">mdi-calendar</v-icon>
                  </template>
                  <v-list-item-title>Годы обучения</v-list-item-title>
                  <v-list-item-subtitle>{{ group.enrollment_year }} - {{ group.graduation_year }}</v-list-item-subtitle>
                </v-list-item>
                
                <v-list-item>
                  <template v-slot:prepend>
                    <v-icon color="info">mdi-school</v-icon>
                  </template>
                  <v-list-item-title>Курс</v-list-item-title>
                  <v-list-item-subtitle>{{ group.course }}</v-list-item-subtitle>
                </v-list-item>
                
                <v-list-item>
                  <template v-slot:prepend>
                    <v-icon :color="group.status === 'active' ? 'success' : 'warning'">mdi-toggle-switch</v-icon>
                  </template>
                  <v-list-item-title>Статус</v-list-item-title>
                  <v-list-item-subtitle>
                    <v-chip
                      :color="group.status === 'active' ? 'success' : 'warning'"
                      size="small"
                      variant="tonal"
                    >
                      {{ group.status_text }}
                    </v-chip>
                  </v-list-item-subtitle>
                </v-list-item>
              </v-list>
            </v-card-text>
          </v-card>
        </v-col>
        
        <!-- Студенты группы -->
        <v-col cols="12" md="8">
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-account-group</v-icon>
              Студенты группы ({{ students.length }})
            </v-card-title>
            <v-card-text>
              <div v-if="students.length === 0" class="text-center py-4">
                <v-icon size="48" color="grey-lighten-1" class="mb-2">mdi-account-group-outline</v-icon>
                <p class="text-body-2 text-grey">В группе нет студентов</p>
              </div>
              
              <div v-else>
                <v-list>
                  <v-list-item
                    v-for="student in students"
                    :key="student.id"
                    class="mb-2"
                  >
                    <template v-slot:prepend>
                      <v-avatar size="40" color="primary">
                        <v-icon color="white" size="20">mdi-account</v-icon>
                      </v-avatar>
                    </template>
                    
                    <v-list-item-title class="font-weight-medium">
                      {{ student.full_name }}
                    </v-list-item-title>
                    
                    <v-list-item-subtitle>
                      <div class="mb-1">{{ student.email }}</div>
                      <div v-if="student.phone" class="mb-1">
                        <v-icon size="16" class="mr-1">mdi-phone</v-icon>
                        {{ student.phone }}
                      </div>
                      <div v-if="student.address" class="text-caption">
                        <v-icon size="16" class="mr-1">mdi-map-marker</v-icon>
                        {{ student.address }}
                      </div>
                    </v-list-item-subtitle>
                    
                    <template v-slot:append>
                      <v-btn
                        color="primary"
                        variant="text"
                        size="small"
                        @click="viewStudent(student)"
                        prepend-icon="mdi-eye"
                      >
                        Просмотр
                      </v-btn>
                    </template>
                  </v-list-item>
                </v-list>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Расписание уроков -->
      <v-row>
        <v-col cols="12">
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-calendar-clock</v-icon>
              Расписание уроков ({{ schedules.length }})
            </v-card-title>
            <v-card-text>
              <div v-if="schedules.length === 0" class="text-center py-4">
                <v-icon size="48" color="grey-lighten-1" class="mb-2">mdi-calendar-outline</v-icon>
                <p class="text-body-2 text-grey">Нет запланированных уроков</p>
              </div>
              
              <div v-else>
                <v-list>
                  <v-list-item
                    v-for="schedule in schedules"
                    :key="schedule.id"
                    class="mb-2"
                  >
                    <template v-slot:prepend>
                      <v-avatar size="40" color="secondary">
                        <v-icon color="white" size="20">mdi-teach</v-icon>
                      </v-avatar>
                    </template>
                    
                    <v-list-item-title class="font-weight-medium">
                      {{ schedule.lesson_title }}
                    </v-list-item-title>
                    
                    <v-list-item-subtitle>
                      <div class="d-flex align-center gap-4 mb-2">
                        <v-chip
                          color="primary"
                          size="small"
                          variant="tonal"
                        >
                          {{ schedule.subject_name }}
                        </v-chip>
                        
                        <v-chip
                          color="info"
                          size="small"
                          variant="tonal"
                        >
                          {{ getDayOfWeekText(schedule.day_of_week) }}
                        </v-chip>
                        
                        <v-chip
                          :color="schedule.is_active ? 'success' : 'warning'"
                          size="small"
                          variant="tonal"
                        >
                          {{ schedule.is_active ? 'Активен' : 'Неактивен' }}
                        </v-chip>
                      </div>
                      
                      <div class="d-flex align-center gap-4 text-caption text-medium-emphasis">
                        <span v-if="schedule.start_time && schedule.end_time">
                          <v-icon size="16" class="mr-1">mdi-clock</v-icon>
                          {{ schedule.start_time }} - {{ schedule.end_time }}
                        </span>
                        <span v-if="schedule.room">
                          <v-icon size="16" class="mr-1">mdi-map-marker</v-icon>
                          {{ schedule.room }}
                        </span>
                        <span v-if="schedule.semester">
                          <v-icon size="16" class="mr-1">mdi-school</v-icon>
                          Семестр {{ schedule.semester }}
                        </span>
                      </div>
                    </v-list-item-subtitle>
                    
                    <template v-slot:append>
                      <v-btn
                        color="primary"
                        variant="text"
                        size="small"
                        @click="viewLesson(schedule)"
                        prepend-icon="mdi-eye"
                      >
                        Просмотр урока
                      </v-btn>
                    </template>
                  </v-list-item>
                </v-list>
              </div>
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

// Props из Inertia
const props = defineProps({
  group: {
    type: Object,
    required: true
  },
  students: {
    type: Array,
    default: () => []
  },
  schedules: {
    type: Array,
    default: () => []
  },
  stats: {
    type: Object,
    default: () => ({})
  }
})

// Методы
const navigateTo = (path) => {
  router.visit(path)
}

const getDayOfWeekText = (day) => {
  const days = {
    'monday': 'Понедельник',
    'tuesday': 'Вторник',
    'wednesday': 'Среда',
    'thursday': 'Четверг',
    'friday': 'Пятница',
    'saturday': 'Суббота',
    'sunday': 'Воскресенье'
  }
  return days[day] || day
}

const viewStudent = (student) => {
  navigateTo(`/teacher/students/student/${student.id}`)
}

const viewLesson = (schedule) => {
  navigateTo(`/teacher/lessons/${schedule.id}`)
}
</script>

<style scoped>
.v-card {
  border-radius: 12px;
}

.v-list-item {
  border-radius: 8px;
}
</style>
