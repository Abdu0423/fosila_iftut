<template>
  <Layout>
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="mb-6">
            <h1 class="text-h4 font-weight-bold mb-2">{{ t('ssr.title', {}, { default: 'Самостоятельная работа (ССР)' }) }}</h1>
            <p class="text-body-1 text-medium-emphasis">{{ t('ssr.student_subtitle', {}, { default: 'Выберите тему и напишите работу' }) }}</p>
          </div>
        </v-col>
      </v-row>

      <!-- Список ССР -->
      <v-row v-if="tasks.length > 0">
        <v-col v-for="task in tasks" :key="task.id" cols="12" md="6" lg="4">
          <v-card class="h-100" :class="getTaskCardClass(task)">
            <v-card-title class="d-flex align-center">
              <v-icon start :color="getTaskIconColor(task)">{{ getTaskIcon(task) }}</v-icon>
              <span class="text-truncate">{{ task.title }}</span>
            </v-card-title>
            
            <v-card-subtitle v-if="task.schedule">
              {{ task.schedule.subject }}
            </v-card-subtitle>

            <v-card-text>
              <p v-if="task.description" class="text-body-2 mb-3 description-text">
                {{ task.description.length > 100 ? task.description.substring(0, 100) + '...' : task.description }}
              </p>

              <!-- Статус студента -->
              <v-alert
                v-if="task.has_selected_topic"
                :type="getAlertType(task)"
                variant="tonal"
                density="compact"
                class="mb-3"
              >
                <div class="font-weight-medium mb-1">{{ t('ssr.your_topic', {}, { default: 'Ваша тема' }) }}:</div>
                <div>{{ task.selected_topic.topic_text }}</div>
                <div v-if="task.submission" class="mt-2">
                  <v-chip :color="getStatusColor(task.submission.status)" size="small" variant="flat">
                    {{ getStatusText(task.submission.status) }}
                  </v-chip>
                  <v-chip v-if="task.submission.grade !== null" class="ml-2" :color="getGradeColor(task.submission.grade)" size="small" variant="flat">
                    {{ t('ssr.grade', {}, { default: 'Оценка' }) }}: {{ task.submission.grade }}
                  </v-chip>
                </div>
              </v-alert>
              <v-alert
                v-else
                type="info"
                variant="tonal"
                density="compact"
                class="mb-3"
              >
                {{ t('ssr.available_topics', {}, { default: 'Доступно тем' }) }}: {{ task.available_topics_count }}
              </v-alert>

              <!-- Срок -->
              <div v-if="task.deadline" class="d-flex align-center text-body-2" :class="{ 'text-error': task.is_overdue }">
                <v-icon size="small" class="mr-1">mdi-calendar</v-icon>
                {{ t('ssr.deadline', {}, { default: 'Срок сдачи' }) }}: {{ task.deadline }}
                <v-chip v-if="task.is_overdue" color="error" size="x-small" class="ml-2">
                  {{ t('ssr.overdue', {}, { default: 'Просрочено' }) }}
                </v-chip>
              </div>
            </v-card-text>

            <v-card-actions>
              <v-btn
                color="primary"
                :variant="task.has_selected_topic ? 'outlined' : 'flat'"
                @click="$inertia.visit(`/student/ssr/${task.id}`)"
              >
                {{ task.has_selected_topic 
                  ? (task.submission?.status === 'checked' 
                    ? t('common.view', {}, { default: 'Просмотр' })
                    : t('ssr.continue_work', {}, { default: 'Продолжить работу' }))
                  : t('ssr.select_topic', {}, { default: 'Выбрать тему' }) 
                }}
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-col>
      </v-row>

      <!-- Пустое состояние -->
      <v-row v-else>
        <v-col cols="12">
          <v-card>
            <v-card-text class="text-center py-12">
              <v-icon size="64" color="grey-lighten-1" class="mb-4">mdi-file-document-outline</v-icon>
              <h3 class="text-h6 mb-2">{{ t('ssr.no_tasks', {}, { default: 'Нет доступных ССР' }) }}</h3>
              <p class="text-body-2 text-grey">{{ t('ssr.no_tasks_description', {}, { default: 'Преподаватель пока не создал задания для самостоятельной работы' }) }}</p>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </Layout>
</template>

<script setup>
import { useI18n } from 'vue-i18n'
import Layout from '../../Layout.vue'

const { t } = useI18n()

defineProps({
  tasks: {
    type: Array,
    default: () => []
  }
})

const getTaskCardClass = (task) => {
  if (task.submission?.status === 'checked') return 'border-success'
  if (task.submission?.status === 'returned') return 'border-warning'
  if (task.is_overdue && !task.submission?.status) return 'border-error'
  return ''
}

const getTaskIconColor = (task) => {
  if (task.submission?.status === 'checked') return 'success'
  if (task.submission?.status === 'returned') return 'warning'
  if (task.has_selected_topic) return 'primary'
  return 'grey'
}

const getTaskIcon = (task) => {
  if (task.submission?.status === 'checked') return 'mdi-check-circle'
  if (task.submission?.status === 'returned') return 'mdi-alert-circle'
  if (task.submission?.status === 'submitted') return 'mdi-clock-outline'
  if (task.has_selected_topic) return 'mdi-pencil'
  return 'mdi-file-document-outline'
}

const getAlertType = (task) => {
  if (task.submission?.status === 'checked') return 'success'
  if (task.submission?.status === 'returned') return 'warning'
  return 'info'
}

const getStatusColor = (status) => {
  const colors = {
    draft: 'grey',
    submitted: 'warning',
    checked: 'success',
    returned: 'error'
  }
  return colors[status] || 'grey'
}

const getStatusText = (status) => {
  const texts = {
    draft: t('ssr.status_draft', {}, { default: 'Черновик' }),
    submitted: t('ssr.status_submitted', {}, { default: 'На проверке' }),
    checked: t('ssr.status_checked', {}, { default: 'Проверено' }),
    returned: t('ssr.status_returned', {}, { default: 'На доработку' })
  }
  return texts[status] || status
}

const getGradeColor = (grade) => {
  if (grade >= 90) return 'success'
  if (grade >= 75) return 'info'
  if (grade >= 60) return 'warning'
  return 'error'
}
</script>

<style scoped>
.description-text {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.border-success {
  border-left: 4px solid rgb(var(--v-theme-success)) !important;
}

.border-warning {
  border-left: 4px solid rgb(var(--v-theme-warning)) !important;
}

.border-error {
  border-left: 4px solid rgb(var(--v-theme-error)) !important;
}
</style>
