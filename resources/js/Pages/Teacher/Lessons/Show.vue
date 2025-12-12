<template>
  <Layout role="teacher">
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center mb-6">
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">{{ lesson.title }}</h1>
              <p class="text-body-1 text-medium-emphasis">
                {{ lesson.subject?.name }} - {{ schedule.group?.name }}
              </p>
            </div>
            <div class="d-flex gap-2">
              <v-btn
                color="primary"
                @click="navigateTo(`/teacher/lessons/${lesson.id}/edit`)"
                prepend-icon="mdi-pencil"
              >
                Редактировать
              </v-btn>
              <v-btn
                color="secondary"
                variant="outlined"
                @click="navigateTo('/teacher/lessons')"
                prepend-icon="mdi-arrow-left"
              >
                Назад к урокам
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

      <!-- Основная информация об уроке -->
      <v-row class="mb-6">
        <v-col cols="12" md="8">
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-information</v-icon>
              Информация об уроке
            </v-card-title>
            <v-card-text>
              <v-row>
                <v-col cols="12" md="6">
                  <div class="mb-4">
                    <div class="text-subtitle-2 text-medium-emphasis mb-1">Название</div>
                    <div class="text-body-1">{{ lesson.title }}</div>
                  </div>
                </v-col>
                <v-col cols="12" md="6">
                  <div class="mb-4">
                    <div class="text-subtitle-2 text-medium-emphasis mb-1">Предмет</div>
                    <div class="text-body-1">{{ lesson.subject?.name }}</div>
                  </div>
                </v-col>
                <v-col cols="12">
                  <div class="mb-4">
                    <div class="text-subtitle-2 text-medium-emphasis mb-1">Описание</div>
                    <div class="text-body-1">{{ lesson.description }}</div>
                  </div>
                </v-col>
                <v-col cols="12" md="6">
                  <div class="mb-4">
                    <div class="text-subtitle-2 text-medium-emphasis mb-1">Создан</div>
                    <div class="text-body-1">{{ formatDate(lesson.created_at) }}</div>
                  </div>
                </v-col>
                <v-col cols="12" md="6">
                  <div class="mb-4">
                    <div class="text-subtitle-2 text-medium-emphasis mb-1">Обновлен</div>
                    <div class="text-body-1">{{ formatDate(lesson.updated_at) }}</div>
                  </div>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>
        </v-col>
        
        <v-col cols="12" md="4">
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-calendar-clock</v-icon>
              Расписание
            </v-card-title>
            <v-card-text>
              <div class="mb-4">
                <div class="text-subtitle-2 text-medium-emphasis mb-1">Предмет</div>
                <div class="text-body-1">{{ schedule.subject?.name }}</div>
              </div>
              <div class="mb-4">
                <div class="text-subtitle-2 text-medium-emphasis mb-1">Группа</div>
                <div class="text-body-1">{{ schedule.group?.name }}</div>
              </div>
              <div class="mb-4">
                <div class="text-subtitle-2 text-medium-emphasis mb-1">Период</div>
                <div class="text-body-1">
                  {{ formatDateRange(schedule.start_date, schedule.end_date) }}
                </div>
              </div>
              <div class="mb-4">
                <div class="text-subtitle-2 text-medium-emphasis mb-1">Статус</div>
                <v-chip
                  :color="schedule.is_active ? 'success' : 'warning'"
                  size="small"
                >
                  {{ schedule.is_active ? 'Активно' : 'Неактивно' }}
                </v-chip>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Файл урока -->
      <v-row v-if="lesson.file_name">
        <v-col cols="12">
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-file-document</v-icon>
              Файл урока
            </v-card-title>
            <v-card-text>
              <div class="d-flex align-center justify-space-between">
                <div>
                  <div class="text-subtitle-1 font-weight-medium">{{ lesson.file_name }}</div>
                  <div class="text-body-2 text-medium-emphasis">
                    Размер: {{ formatFileSize(lesson.file_size) }}
                  </div>
                </div>
                <v-btn
                  color="primary"
                  @click="downloadFile"
                  prepend-icon="mdi-download"
                >
                  Скачать файл
                </v-btn>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Студенты группы -->
      <v-row>
        <v-col cols="12">
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-account-group</v-icon>
              Студенты группы
            </v-card-title>
            <v-card-text>
              <div v-if="students.length === 0" class="text-center py-4">
                <v-icon size="48" color="grey-lighten-1" class="mb-2">mdi-account-group-outline</v-icon>
                <p class="text-body-2 text-grey">Нет студентов в группе</p>
              </div>
              <v-list v-else>
                <v-list-item
                  v-for="student in students"
                  :key="student.id"
                >
                  <template v-slot:prepend>
                    <v-avatar color="primary" size="32">
                      <v-icon color="white" size="16">mdi-account</v-icon>
                    </v-avatar>
                  </template>
                  <v-list-item-title>{{ student.full_name }}</v-list-item-title>
                  <v-list-item-subtitle>{{ student.email }}</v-list-item-subtitle>
                  <template v-slot:append>
                    <v-btn
                      icon="mdi-email"
                      variant="text"
                      size="small"
                      @click="sendEmail(student.email)"
                    ></v-btn>
                  </template>
                </v-list-item>
              </v-list>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </Layout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import Layout from '../../Layout.vue'

const page = usePage()

// Props
const props = defineProps({
  lesson: {
    type: Object,
    required: true
  },
  schedule: {
    type: Object,
    required: true
  },
  students: {
    type: Array,
    default: () => []
  }
})

// Методы
const navigateTo = (route) => {
  router.visit(route)
}

const downloadFile = () => {
  if (props.lesson.file_path) {
    window.open(`/storage/${props.lesson.file_path}`, '_blank')
  }
}

const sendEmail = (email) => {
  window.location.href = `mailto:${email}`
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('ru-RU', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const formatDateRange = (startDate, endDate) => {
  const start = new Date(startDate).toLocaleDateString('ru-RU')
  const end = new Date(endDate).toLocaleDateString('ru-RU')
  return `${start} - ${end}`
}

const formatFileSize = (bytes) => {
  if (!bytes) return '0 B'
  const k = 1024
  const sizes = ['B', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

// Жизненный цикл
onMounted(() => {
  console.log('Страница урока загружена:', props.lesson.id)
})
</script>

<style scoped>
.v-card {
  border-radius: 12px;
}
</style>