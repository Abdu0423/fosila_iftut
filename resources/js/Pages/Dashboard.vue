<template>
  <Layout role="student">
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center mb-6">
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">{{ t('panels.student_title') }}</h1>
              <p class="text-body-1 text-medium-emphasis">{{ t('panels.student_subtitle') }}</p>
            </div>
            <v-btn
              color="primary"
              size="large"
              prepend-icon="mdi-play"
              @click="navigateTo('/student/courses')"
            >
              {{ t('navigation.courses') }}
            </v-btn>
          </div>
        </v-col>
      </v-row>

      <!-- Статистика -->
      <v-row class="mb-6">
        <v-col cols="12" md="3">
          <v-card>
            <v-card-text class="text-center">
              <v-icon size="48" color="primary" class="mb-4">mdi-book-open-variant</v-icon>
              <div class="text-h4 font-weight-bold">{{ stats.courses }}</div>
              <div class="text-body-2 text-medium-emphasis">{{ t('navigation.courses') }}</div>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" md="3">
          <v-card>
            <v-card-text class="text-center">
              <v-icon size="48" color="success" class="mb-4">mdi-check-circle</v-icon>
              <div class="text-h4 font-weight-bold">{{ stats.completedLessons }}</div>
              <div class="text-body-2 text-medium-emphasis">{{ t('navigation.lessons') }}</div>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" md="3">
          <v-card>
            <v-card-text class="text-center">
              <v-icon size="48" color="info" class="mb-4">mdi-clock</v-icon>
              <div class="text-h4 font-weight-bold">{{ stats.studyHours }}</div>
              <div class="text-body-2 text-medium-emphasis">{{ t('navigation.schedule') }}</div>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" md="3">
          <v-card>
            <v-card-text class="text-center">
              <v-icon size="48" color="warning" class="mb-4">mdi-star</v-icon>
              <div class="text-h4 font-weight-bold">{{ stats.averageGrade }}</div>
              <div class="text-body-2 text-medium-emphasis">{{ t('navigation.grades') }}</div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Основной контент -->
      <v-row>
        <!-- Текущие курсы -->
        <v-col cols="12" lg="8">
          <v-card class="mb-6">
            <v-card-title class="text-h6">
              <v-icon start>mdi-book-open</v-icon>
              {{ t('navigation.courses') }}
            </v-card-title>
            <v-card-text>
              <v-list>
                <v-list-item
                  v-for="course in currentCourses"
                  :key="course.id"
                  :prepend-avatar="course.avatar"
                >
                  <v-list-item-title>{{ course.name }}</v-list-item-title>
                  <v-list-item-subtitle>
                    {{ course.description }} • {{ t('messages.progress') || 'Прогресс' }}: {{ course.progress }}%
                  </v-list-item-subtitle>
                  <template v-slot:append>
                    <v-btn
                      color="primary"
                      size="small"
                      @click="navigateTo(`/student/courses/${course.id}`)"
                    >
                      {{ t('messages.view') }}
                    </v-btn>
                  </template>
                </v-list-item>
              </v-list>
            </v-card-text>
          </v-card>

          <!-- Последние оценки -->
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-star</v-icon>
              {{ t('navigation.grades') }}
            </v-card-title>
            <v-card-text>
              <v-list>
                <v-list-item
                  v-for="grade in recentGrades"
                  :key="grade.id"
                >
                  <v-list-item-title>{{ grade.lesson.title }}</v-list-item-title>
                  <v-list-item-subtitle>
                    {{ grade.course.name }} • {{ formatDate(grade.graded_at) }}
                  </v-list-item-subtitle>
                  <template v-slot:append>
                    <v-chip
                      :color="getGradeColor(grade.grade)"
                      size="small"
                      variant="tonal"
                    >
                      {{ grade.grade }}/{{ grade.max_grade }}
                    </v-chip>
                  </template>
                </v-list-item>
              </v-list>
            </v-card-text>
          </v-card>
        </v-col>

        <!-- Боковая панель -->
        <v-col cols="12" lg="4">
          <!-- Быстрые действия -->
          <v-card class="mb-6">
            <v-card-title class="text-h6">
              <v-icon start>mdi-lightning-bolt</v-icon>
              {{ t('messages.actions') }}
            </v-card-title>
            <v-card-text>
              <v-list>
                <v-list-item
                  v-for="action in quickActions"
                  :key="action.title"
                  @click="navigateTo(action.route)"
                  :prepend-icon="action.icon"
                >
                  <v-list-item-title>{{ action.title }}</v-list-item-title>
                </v-list-item>
              </v-list>
            </v-card-text>
          </v-card>

          <!-- Уведомления -->
          <v-card class="mb-6">
            <v-card-title class="text-h6">
              <v-icon start>mdi-bell</v-icon>
              {{ t('messages.notification') }}
            </v-card-title>
            <v-card-text>
              <v-list>
                <v-list-item
                  v-for="notification in notifications"
                  :key="notification.id"
                >
                  <v-list-item-title class="text-body-2">{{ notification.message }}</v-list-item-title>
                  <v-list-item-subtitle class="text-caption">{{ notification.time }}</v-list-item-subtitle>
                </v-list-item>
              </v-list>
            </v-card-text>
          </v-card>

          <!-- Достижения -->
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-trophy</v-icon>
              {{ t('messages.info') }}
            </v-card-title>
            <v-card-text>
              <div v-for="achievement in achievements" :key="achievement.id" class="mb-4">
                <div class="d-flex align-center">
                  <v-icon 
                    :color="achievement.unlocked ? 'warning' : 'grey'"
                    class="mr-3"
                  >
                    {{ achievement.icon }}
                  </v-icon>
                  <div>
                    <div class="text-body-2 font-weight-medium">{{ achievement.title }}</div>
                    <div class="text-caption text-medium-emphasis">{{ achievement.description }}</div>
                  </div>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </Layout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import Layout from './Layout.vue'

const page = usePage()
const { t } = useI18n()

// Статистика
const stats = ref({
  courses: 3,
  completedLessons: 15,
  studyHours: 24,
  averageGrade: 4.3
})

// Текущие курсы
const currentCourses = ref([
  {
    id: 1,
    name: 'Программирование на Python',
    description: 'Базовый курс по программированию',
    avatar: 'https://ui-avatars.com/api/?name=Python',
    progress: 65
  },
  {
    id: 2,
    name: 'Веб-разработка',
    description: 'Создание веб-сайтов',
    avatar: 'https://ui-avatars.com/api/?name=Web',
    progress: 45
  },
  {
    id: 3,
    name: 'Базы данных',
    description: 'Работа с SQL',
    avatar: 'https://ui-avatars.com/api/?name=SQL',
    progress: 80
  }
])

// Последние оценки
const recentGrades = ref([
  {
    id: 1,
    lesson: { title: 'Введение в Python' },
    course: { name: 'Программирование на Python' },
    grade: 5,
    max_grade: 5,
    graded_at: '2024-01-19'
  },
  {
    id: 2,
    lesson: { title: 'Переменные и типы данных' },
    course: { name: 'Программирование на Python' },
    grade: 4,
    max_grade: 5,
    graded_at: '2024-01-18'
  },
  {
    id: 3,
    lesson: { title: 'Основы HTML' },
    course: { name: 'Веб-разработка' },
    grade: 5,
    max_grade: 5,
    graded_at: '2024-01-17'
  }
])

// Быстрые действия
const quickActions = computed(() => {
  return [
    {
      title: t('navigation.courses'),
      icon: 'mdi-book-open-variant',
      route: '/student/courses'
    },
    {
      title: t('navigation.schedule'),
      icon: 'mdi-calendar',
      route: '/student/schedule'
    },
    {
      title: t('navigation.assignments'),
      icon: 'mdi-clipboard-text',
      route: '/student/assignments'
    },
    {
      title: t('navigation.grades'),
      icon: 'mdi-star',
      route: '/student/grades'
    }
  ]
})

// Уведомления
const notifications = computed(() => {
  return [
    { id: 1, message: t('messages.notification'), time: '2 часа назад' },
    { id: 2, message: t('messages.notification'), time: '1 день назад' },
    { id: 3, message: t('messages.notification'), time: '1 день назад' }
  ]
})

// Достижения
const achievements = computed(() => {
  return [
    { 
      id: 1, 
      title: t('messages.info'), 
      description: t('messages.details'),
      icon: 'mdi-trophy',
      unlocked: true
    },
    { 
      id: 2, 
      title: t('messages.info'), 
      description: t('messages.details'),
      icon: 'mdi-star',
      unlocked: true
    },
    { 
      id: 3, 
      title: t('messages.info'), 
      description: t('messages.details'),
      icon: 'mdi-clock',
      unlocked: false
    }
  ]
})

// Методы
const navigateTo = (route) => {
  router.visit(route)
}

const formatDate = (date) => {
  const locale = page.props.locale
  return new Date(date).toLocaleDateString(locale === 'tg' ? 'tg-TJ' : 'ru-RU')
}

const getGradeColor = (grade) => {
  if (grade >= 4.5) return 'success'
  if (grade >= 3.5) return 'info'
  if (grade >= 2.5) return 'warning'
  return 'error'
}

onMounted(() => {
  console.log('Dashboard loaded')
})
</script>

<style scoped>
.v-card {
  border-radius: 12px;
}
</style>
