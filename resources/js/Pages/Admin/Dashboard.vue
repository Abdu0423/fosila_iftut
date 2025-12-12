<template>
  <Layout role="admin">
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center mb-6">
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">Панель администратора</h1>
              <p class="text-body-1 text-medium-emphasis">Управление системой дистанционного обучения</p>
            </div>
            <v-btn
              color="primary"
              size="large"
              prepend-icon="mdi-plus"
              @click="navigateTo('/admin/users/create')"
            >
              Добавить пользователя
            </v-btn>
          </div>
        </v-col>
      </v-row>

      <!-- Статистика -->
      <v-row class="mb-6">
        <v-col cols="12" sm="6" md="3">
          <v-card hover class="stat-card">
            <v-card-text class="text-center pa-6">
              <v-icon size="48" color="primary" class="mb-4">mdi-account-group</v-icon>
              <div class="text-h4 font-weight-bold">{{ stats?.users || 0 }}</div>
              <div class="text-body-2 text-medium-emphasis">Всего пользователей</div>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" sm="6" md="3">
          <v-card hover class="stat-card">
            <v-card-text class="text-center pa-6">
              <v-icon size="48" color="success" class="mb-4">mdi-book-education</v-icon>
              <div class="text-h4 font-weight-bold">{{ stats?.subjects || 0 }}</div>
              <div class="text-body-2 text-medium-emphasis">Предметов</div>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" sm="6" md="3">
          <v-card hover class="stat-card">
            <v-card-text class="text-center pa-6">
              <v-icon size="48" color="info" class="mb-4">mdi-teach</v-icon>
              <div class="text-h4 font-weight-bold">{{ stats?.lessons || 0 }}</div>
              <div class="text-body-2 text-medium-emphasis">Уроков</div>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" sm="6" md="3">
          <v-card hover class="stat-card">
            <v-card-text class="text-center pa-6">
              <v-icon size="48" color="warning" class="mb-4">mdi-chart-line</v-icon>
              <div class="text-h4 font-weight-bold">{{ stats?.activeUsers || 0 }}</div>
              <div class="text-body-2 text-medium-emphasis">Активных за 24ч</div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Дополнительная статистика -->
      <v-row class="mb-6">
        <v-col cols="12" sm="6" md="3">
          <v-card hover class="stat-card-secondary">
            <v-card-text class="text-center pa-4">
              <v-icon size="36" color="purple" class="mb-2">mdi-calendar-clock</v-icon>
              <div class="text-h5 font-weight-bold">{{ stats?.schedules || 0 }}</div>
              <div class="text-caption text-medium-emphasis">Расписаний</div>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" sm="6" md="3">
          <v-card hover class="stat-card-secondary">
            <v-card-text class="text-center pa-4">
              <v-icon size="36" color="orange" class="mb-2">mdi-clipboard-text</v-icon>
              <div class="text-h5 font-weight-bold">{{ stats?.assignments || 0 }}</div>
              <div class="text-caption text-medium-emphasis">Заданий</div>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" sm="6" md="3">
          <v-card hover class="stat-card-secondary">
            <v-card-text class="text-center pa-4">
              <v-icon size="36" color="pink" class="mb-2">mdi-help-circle</v-icon>
              <div class="text-h5 font-weight-bold">{{ stats?.tests || 0 }}</div>
              <div class="text-caption text-medium-emphasis">Тестов</div>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" sm="6" md="3">
          <v-card hover class="stat-card-secondary">
            <v-card-text class="text-center pa-4">
              <v-icon size="36" color="teal" class="mb-2">mdi-star</v-icon>
              <div class="text-h5 font-weight-bold">{{ stats?.grades || 0 }}</div>
              <div class="text-caption text-medium-emphasis">Оценок</div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Основной контент -->
      <v-row>
        <!-- Последние действия -->
        <v-col cols="12" lg="8">
          <v-card class="mb-6">
            <v-card-title class="text-h6">
              <v-icon start>mdi-clock</v-icon>
              Последние действия
            </v-card-title>
            <v-card-text>
              <v-list>
                <v-list-item
                  v-for="action in recentActions"
                  :key="action.id"
                  :prepend-avatar="action.user.avatar"
                >
                  <v-list-item-title>{{ action.description }}</v-list-item-title>
                  <v-list-item-subtitle>
                    {{ action.user.name }} • {{ formatDate(action.created_at) }}
                  </v-list-item-subtitle>
                  <template v-slot:append>
                    <v-chip
                      :color="getActionColor(action.type)"
                      size="small"
                      variant="tonal"
                    >
                      {{ getActionText(action.type) }}
                    </v-chip>
                  </template>
                </v-list-item>
              </v-list>
            </v-card-text>
          </v-card>

          <!-- Системные уведомления -->
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-alert</v-icon>
              Системные уведомления
            </v-card-title>
            <v-card-text>
              <v-list>
                <v-list-item
                  v-for="notification in systemNotifications"
                  :key="notification.id"
                >
                  <v-list-item-title>{{ notification.title }}</v-list-item-title>
                  <v-list-item-subtitle>
                    {{ notification.message }} • {{ formatDate(notification.created_at) }}
                  </v-list-item-subtitle>
                  <template v-slot:append>
                    <v-chip
                      :color="getNotificationColor(notification.level)"
                      size="small"
                      variant="tonal"
                    >
                      {{ getNotificationText(notification.level) }}
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
              Быстрые действия
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

          <!-- Статистика по ролям -->
          <v-card class="mb-6">
            <v-card-title class="text-h6">
              <v-icon start>mdi-chart-pie</v-icon>
              Пользователи по ролям
            </v-card-title>
            <v-card-text>
              <div v-for="role in roleStats" :key="role.name" class="mb-4">
                <div class="d-flex justify-space-between align-center mb-2">
                  <span class="text-body-2">{{ role.name }}</span>
                  <span class="text-body-2 font-weight-medium">{{ role.count }}</span>
                </div>
                <v-progress-linear
                  :model-value="(role.count / stats.users) * 100"
                  :color="role.color"
                  height="8"
                  rounded
                ></v-progress-linear>
              </div>
            </v-card-text>
          </v-card>

          <!-- Системная информация -->
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-information</v-icon>
              Системная информация
            </v-card-title>
            <v-card-text>
              <div class="mb-3">
                <div class="text-body-2 text-medium-emphasis">Версия системы</div>
                <div class="text-body-1 font-weight-medium">{{ systemInfo?.version }}</div>
              </div>
              <div class="mb-3">
                <div class="text-body-2 text-medium-emphasis">Laravel</div>
                <div class="text-body-1 font-weight-medium">{{ systemInfo?.laravelVersion }}</div>
              </div>
              <div class="mb-3">
                <div class="text-body-2 text-medium-emphasis">PHP</div>
                <div class="text-body-1 font-weight-medium">{{ systemInfo?.phpVersion }}</div>
              </div>
              <div class="mb-3">
                <div class="text-body-2 text-medium-emphasis">База данных</div>
                <div class="text-body-1 font-weight-medium">{{ systemInfo?.dbConnection }}</div>
              </div>
              <div class="mb-3">
                <div class="text-body-2 text-medium-emphasis">Последнее обновление</div>
                <div class="text-body-1 font-weight-medium">{{ formatDate(systemInfo?.lastUpdate) }}</div>
              </div>
              <div class="mb-3">
                <div class="text-body-2 text-medium-emphasis">Статус системы</div>
                <v-chip color="success" size="small" variant="tonal">
                  <v-icon start size="small">mdi-check-circle</v-icon>
                  Работает
                </v-chip>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </Layout>
</template>

<script setup>
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import Layout from '../Layout.vue'

// Props из контроллера
const props = defineProps({
  stats: Object,
  roleStats: Array,
  recentUsers: Array,
  recentLessons: Array,
  recentGrades: Array,
  activityStats: Array,
  subjectsStats: Array,
  systemNotifications: Array,
  quickActions: Array,
  systemInfo: Object
})

// Формируем последние действия из разных источников
const recentActions = computed(() => {
  const actions = []
  
  // Добавляем последних пользователей
  if (props.recentUsers && props.recentUsers.length > 0) {
    props.recentUsers.slice(0, 3).forEach(user => {
      actions.push({
        id: `user-${user.id}`,
        description: `Зарегистрирован новый пользователь: ${user.name}`,
        user: { name: 'Система', avatar: 'https://ui-avatars.com/api/?name=System' },
        type: 'user',
        created_at: user.created_at
      })
    })
  }
  
  // Добавляем последние уроки
  if (props.recentLessons && props.recentLessons.length > 0) {
    props.recentLessons.slice(0, 3).forEach(lesson => {
      actions.push({
        id: `lesson-${lesson.id}`,
        description: `Создан урок: ${lesson.name} (${lesson.subject})`,
        user: { name: lesson.teacher, avatar: `https://ui-avatars.com/api/?name=${encodeURIComponent(lesson.teacher)}` },
        type: 'create',
        created_at: lesson.created_at
      })
    })
  }
  
  // Добавляем последние оценки
  if (props.recentGrades && props.recentGrades.length > 0) {
    props.recentGrades.slice(0, 3).forEach(grade => {
      actions.push({
        id: `grade-${grade.id}`,
        description: `Выставлена оценка ${grade.grade} студенту ${grade.student_name} за ${grade.subject_name}`,
        user: { name: 'Преподаватель', avatar: 'https://ui-avatars.com/api/?name=Teacher' },
        type: 'update',
        created_at: grade.created_at
      })
    })
  }
  
  // Сортируем по дате и берем последние 10
  return actions.sort((a, b) => new Date(b.created_at) - new Date(a.created_at)).slice(0, 10)
})

// Методы
const navigateTo = (route) => {
  router.visit(route)
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('ru-RU')
}

const getActionColor = (type) => {
  const colors = {
    create: 'success',
    update: 'info',
    delete: 'error',
    user: 'primary'
  }
  return colors[type]
}

const getActionText = (type) => {
  const texts = {
    create: 'Создание',
    update: 'Обновление',
    delete: 'Удаление',
    user: 'Пользователь'
  }
  return texts[type] || type
}

const getNotificationColor = (level) => {
  const colors = {
    info: 'info',
    warning: 'warning',
    error: 'error',
    success: 'success'
  }
  return colors[level]
}

const getNotificationText = (level) => {
  const texts = {
    info: 'Информация',
    warning: 'Предупреждение',
    error: 'Ошибка',
    success: 'Успех'
  }
  return texts[level] || level
}
</script>

<style scoped>
.v-card {
  border-radius: 12px;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.stat-card {
  border-radius: 16px;
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 1) 100%);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  border: 1px solid rgba(0, 0, 0, 0.06);
}

.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 24px rgba(0, 0, 0, 0.12);
}

.stat-card-secondary {
  border-radius: 12px;
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 1) 100%);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.stat-card-secondary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
}

.v-list-item {
  border-radius: 8px;
  margin-bottom: 8px;
  transition: all 0.2s ease;
}

.v-list-item:hover {
  background-color: rgba(0, 0, 0, 0.02);
}

.v-card-title {
  font-weight: 600;
}

/* Анимация появления карточек */
.v-col {
  animation: fadeInUp 0.4s ease-out;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Задержка анимации для каждой карточки */
.v-col:nth-child(1) { animation-delay: 0.05s; }
.v-col:nth-child(2) { animation-delay: 0.1s; }
.v-col:nth-child(3) { animation-delay: 0.15s; }
.v-col:nth-child(4) { animation-delay: 0.2s; }
</style>
