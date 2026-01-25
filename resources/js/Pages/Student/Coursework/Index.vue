<template>
  <Layout role="student">
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center mb-6">
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">{{ t('coursework.title', {}, { default: 'Курсовые работы' }) }}</h1>
              <p class="text-body-1 text-medium-emphasis">{{ t('coursework.student_subtitle', {}, { default: 'Выбор темы и сдача курсовых работ' }) }}</p>
            </div>
          </div>
        </v-col>
      </v-row>

      <!-- Список курсовых -->
      <v-row v-if="courseworks.length > 0">
        <v-col v-for="item in courseworks" :key="item.id" cols="12" md="6" lg="4">
          <v-card class="h-100">
            <v-card-title class="d-flex align-center">
              <v-icon start :color="getCardColor(item)">mdi-file-document-edit</v-icon>
              <span class="text-truncate">{{ item.subject }}</span>
            </v-card-title>
            
            <v-card-subtitle>
              {{ t('coursework.semester', {}, { default: 'Семестр' }) }} {{ item.semester }}
            </v-card-subtitle>

            <v-card-text>
              <div v-if="item.task.title" class="font-weight-medium mb-2">
                {{ item.task.title }}
              </div>

              <!-- Статус выбора темы -->
              <template v-if="item.has_selected_topic">
                <v-alert type="success" variant="tonal" density="compact" class="mb-3">
                  <div class="text-body-2 font-weight-medium mb-1">{{ t('coursework.your_topic', {}, { default: 'Ваша тема' }) }}:</div>
                  <div>{{ item.selected_topic.topic_text }}</div>
                </v-alert>
              </template>
              <template v-else>
                <v-chip color="info" size="small" class="mb-3">
                  {{ t('coursework.available_topics', {}, { default: 'Доступно тем' }) }}: {{ item.task.available_topics_count }}
                </v-chip>
              </template>

              <!-- Статус работы -->
              <template v-if="item.submission">
                <v-chip :color="getStatusColor(item.submission.status)" size="small" class="mb-2">
                  {{ getStatusText(item.submission.status) }}
                </v-chip>
                <div v-if="item.submission.grade_100 !== null && item.submission.grade_100 !== undefined" class="d-flex align-center mt-2">
                  <span class="text-body-2 text-grey mr-2">{{ t('coursework.grade', {}, { default: 'Оценка' }) }}:</span>
                  <v-chip :color="getGradeColor(item.submission.grade_100)" size="small" variant="flat" class="font-weight-bold">
                    {{ item.submission.grade_10 }} ({{ item.submission.grade_letter }})
                  </v-chip>
                </div>
              </template>

              <!-- Срок сдачи -->
              <div v-if="item.task.deadline" class="d-flex align-center text-body-2 mt-2" :class="item.task.is_overdue ? 'text-error' : 'text-grey'">
                <v-icon size="small" class="mr-1">mdi-calendar-clock</v-icon>
                {{ t('coursework.deadline', {}, { default: 'Срок' }) }}: {{ item.task.deadline }}
                <v-chip v-if="item.task.is_overdue" color="error" size="x-small" class="ml-2">{{ t('coursework.overdue', {}, { default: 'Просрочено' }) }}</v-chip>
              </div>
            </v-card-text>

            <v-card-actions>
              <v-btn
                :color="getCardColor(item)"
                @click="$inertia.visit(`/student/coursework/${item.id}`)"
              >
                <template v-if="!item.has_selected_topic">
                  {{ t('coursework.select_topic', {}, { default: 'Выбрать тему' }) }}
                </template>
                <template v-else-if="!item.submission || item.submission.status === 'draft' || item.submission.status === 'returned'">
                  {{ t('coursework.continue_work', {}, { default: 'Продолжить работу' }) }}
                </template>
                <template v-else>
                  {{ t('common.view', {}, { default: 'Просмотр' }) }}
                </template>
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
              <h3 class="text-h6 mb-2">{{ t('coursework.no_courseworks', {}, { default: 'Нет доступных курсовых работ' }) }}</h3>
              <p class="text-body-2 text-grey">{{ t('coursework.no_courseworks_desc', {}, { default: 'Курсовые работы появятся здесь, когда преподаватель их назначит' }) }}</p>
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
  courseworks: {
    type: Array,
    default: () => []
  }
})

const getCardColor = (item) => {
  if (item.submission?.status === 'checked') return 'success'
  if (item.submission?.status === 'returned') return 'error'
  if (item.submission?.status === 'submitted') return 'warning'
  if (item.has_selected_topic) return 'primary'
  return 'info'
}

const getStatusColor = (status) => {
  const colors = { draft: 'grey', submitted: 'warning', checked: 'success', returned: 'error' }
  return colors[status] || 'grey'
}

const getStatusText = (status) => {
  const texts = {
    draft: t('coursework.status_draft', {}, { default: 'Черновик' }),
    submitted: t('coursework.status_submitted', {}, { default: 'На проверке' }),
    checked: t('coursework.status_checked', {}, { default: 'Проверено' }),
    returned: t('coursework.status_returned', {}, { default: 'На доработку' })
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
