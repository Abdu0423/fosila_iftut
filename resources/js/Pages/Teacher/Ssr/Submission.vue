<template>
  <Layout role="teacher">
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <v-btn
            variant="text"
            color="secondary"
            prepend-icon="mdi-arrow-left"
            @click="$inertia.visit(`/teacher/ssr/${task.id}`)"
            class="mb-2"
          >
            {{ t('common.back', {}, { default: 'Назад к ССР' }) }}
          </v-btn>
          <h1 class="text-h4 font-weight-bold mb-2">{{ t('ssr.check_work', {}, { default: 'Проверка работы' }) }}</h1>
          <p class="text-body-1 text-medium-emphasis">{{ task.title }}</p>
        </v-col>
      </v-row>

      <!-- Уведомления -->
      <v-row v-if="$page.props.flash?.success">
        <v-col cols="12">
          <v-alert type="success" variant="tonal" closable>
            {{ $page.props.flash.success }}
          </v-alert>
        </v-col>
      </v-row>

      <v-row>
        <!-- Информация о студенте и теме -->
        <v-col cols="12" md="4">
          <v-card class="mb-4">
            <v-card-title>
              <v-icon start>mdi-account</v-icon>
              {{ t('ssr.student', {}, { default: 'Студент' }) }}
            </v-card-title>
            <v-card-text>
              <div class="d-flex align-center mb-4">
                <v-avatar size="48" color="primary" class="mr-3">
                  <span class="text-h6 text-white">{{ getInitials(student) }}</span>
                </v-avatar>
                <div>
                  <div class="font-weight-medium text-h6">{{ student.full_name }}</div>
                </div>
              </div>
            </v-card-text>
          </v-card>

          <v-card class="mb-4">
            <v-card-title>
              <v-icon start>mdi-bookmark</v-icon>
              {{ t('ssr.selected_topic', {}, { default: 'Выбранная тема' }) }}
            </v-card-title>
            <v-card-text>
              <p class="text-body-1 font-weight-medium">{{ topic.topic_text }}</p>
            </v-card-text>
          </v-card>

          <v-card>
            <v-card-title>
              <v-icon start>mdi-information</v-icon>
              {{ t('ssr.submission_info', {}, { default: 'Информация' }) }}
            </v-card-title>
            <v-card-text>
              <v-list density="compact">
                <v-list-item>
                  <template v-slot:prepend>
                    <v-icon>mdi-clock</v-icon>
                  </template>
                  <v-list-item-title>{{ t('ssr.submitted_at', {}, { default: 'Отправлено' }) }}</v-list-item-title>
                  <v-list-item-subtitle>{{ submission.submitted_at || '-' }}</v-list-item-subtitle>
                </v-list-item>
                <v-list-item>
                  <template v-slot:prepend>
                    <v-icon>mdi-flag</v-icon>
                  </template>
                  <v-list-item-title>{{ t('ssr.status', {}, { default: 'Статус' }) }}</v-list-item-title>
                  <v-list-item-subtitle>
                    <v-chip :color="getStatusColor(submission.status)" size="small" variant="tonal">
                      {{ getStatusText(submission.status) }}
                    </v-chip>
                  </v-list-item-subtitle>
                </v-list-item>
                <v-list-item v-if="submission.checked_at">
                  <template v-slot:prepend>
                    <v-icon>mdi-check-circle</v-icon>
                  </template>
                  <v-list-item-title>{{ t('ssr.checked_at', {}, { default: 'Проверено' }) }}</v-list-item-title>
                  <v-list-item-subtitle>{{ submission.checked_at }}</v-list-item-subtitle>
                </v-list-item>
              </v-list>
            </v-card-text>
          </v-card>
        </v-col>

        <!-- Работа студента -->
        <v-col cols="12" md="8">
          <v-card class="mb-4">
            <v-card-title>
              <v-icon start>mdi-file-document</v-icon>
              {{ t('ssr.student_work', {}, { default: 'Работа студента' }) }}
            </v-card-title>
            <v-card-text>
              <!-- Текст работы -->
              <div v-if="submission.text" class="work-text mb-4">
                <div v-html="formatText(submission.text)"></div>
              </div>
              <v-alert v-else type="info" variant="tonal" density="compact" class="mb-4">
                {{ t('ssr.no_text', {}, { default: 'Текст работы не добавлен' }) }}
              </v-alert>

              <!-- Файл -->
              <v-card v-if="submission.file_name" variant="outlined" class="pa-3">
                <div class="d-flex align-center">
                  <v-icon size="32" color="primary" class="mr-3">mdi-file-document</v-icon>
                  <div class="flex-grow-1">
                    <div class="font-weight-medium">{{ submission.file_name }}</div>
                    <div class="text-caption text-grey">{{ submission.file_size }}</div>
                  </div>
                  <v-btn
                    variant="tonal"
                    color="primary"
                    :href="`/storage/${submission.file_path}`"
                    target="_blank"
                    prepend-icon="mdi-download"
                  >
                    {{ t('common.download', {}, { default: 'Скачать' }) }}
                  </v-btn>
                </div>
              </v-card>
            </v-card-text>
          </v-card>

          <!-- Форма оценки -->
          <v-card>
            <v-card-title>
              <v-icon start>mdi-check-decagram</v-icon>
              {{ t('ssr.evaluation', {}, { default: 'Оценка работы' }) }}
            </v-card-title>
            <v-card-text>
              <v-form @submit.prevent="submitCheck">
                <v-text-field
                  v-model.number="checkForm.grade"
                  :label="t('ssr.grade', {}, { default: 'Оценка' }) + ' (0-100) *'"
                  type="number"
                  min="0"
                  max="100"
                  variant="outlined"
                  density="comfortable"
                  class="mb-4"
                  :error-messages="checkForm.errors.grade"
                >
                  <template v-slot:append>
                    <v-chip v-if="checkForm.grade" :color="getGradeColor(checkForm.grade)" variant="flat">
                      {{ getGradeLabel(checkForm.grade) }}
                    </v-chip>
                  </template>
                </v-text-field>

                <v-textarea
                  v-model="checkForm.teacher_comment"
                  :label="t('ssr.teacher_comment', {}, { default: 'Комментарий учителя' })"
                  variant="outlined"
                  density="comfortable"
                  rows="4"
                  :hint="t('ssr.comment_hint', {}, { default: 'Напишите отзыв о работе, замечания или рекомендации' })"
                  persistent-hint
                  class="mb-4"
                ></v-textarea>

                <div class="d-flex gap-3">
                  <v-btn
                    type="submit"
                    color="success"
                    :loading="checkForm.processing"
                    prepend-icon="mdi-check"
                    @click="checkForm.action = 'check'"
                  >
                    {{ t('ssr.accept_work', {}, { default: 'Принять работу' }) }}
                  </v-btn>
                  <v-btn
                    color="warning"
                    variant="outlined"
                    :loading="checkForm.processing"
                    prepend-icon="mdi-undo"
                    @click="returnWork"
                  >
                    {{ t('ssr.return_work', {}, { default: 'Вернуть на доработку' }) }}
                  </v-btn>
                </div>
              </v-form>
            </v-card-text>
          </v-card>

          <!-- Предыдущий комментарий -->
          <v-card v-if="submission.teacher_comment && submission.status !== 'checked'" class="mt-4">
            <v-card-title>
              <v-icon start>mdi-comment-text</v-icon>
              {{ t('ssr.previous_comment', {}, { default: 'Предыдущий комментарий' }) }}
            </v-card-title>
            <v-card-text>
              <p class="text-body-1" style="white-space: pre-wrap;">{{ submission.teacher_comment }}</p>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </Layout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import Layout from '../../Layout.vue'

const { t } = useI18n()

const props = defineProps({
  submission: {
    type: Object,
    required: true
  },
  topic: {
    type: Object,
    required: true
  },
  student: {
    type: Object,
    required: true
  },
  task: {
    type: Object,
    required: true
  }
})

const checkForm = useForm({
  grade: props.submission.grade || null,
  teacher_comment: props.submission.teacher_comment || '',
  action: 'check'
})

const getInitials = (student) => {
  if (!student) return '?'
  const last = student.last_name ? student.last_name[0] : ''
  const first = student.name ? student.name[0] : ''
  return (last + first).toUpperCase()
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
    submitted: t('ssr.status_submitted', {}, { default: 'Отправлено' }),
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

const getGradeLabel = (grade) => {
  if (grade >= 90) return 'A'
  if (grade >= 85) return 'A-'
  if (grade >= 80) return 'B+'
  if (grade >= 75) return 'B'
  if (grade >= 70) return 'B-'
  if (grade >= 65) return 'C+'
  if (grade >= 60) return 'C'
  if (grade >= 55) return 'D'
  return 'F'
}

const formatText = (text) => {
  if (!text) return ''
  return text.replace(/\n/g, '<br>')
}

const submitCheck = () => {
  checkForm.action = 'check'
  checkForm.post(`/teacher/ssr/submission/${props.submission.id}/check`)
}

const returnWork = () => {
  if (!checkForm.teacher_comment.trim()) {
    checkForm.errors.teacher_comment = t('ssr.comment_required_for_return', {}, { default: 'Напишите комментарий для возврата работы' })
    return
  }
  checkForm.action = 'return'
  checkForm.post(`/teacher/ssr/submission/${props.submission.id}/check`)
}
</script>

<style scoped>
.work-text {
  background-color: rgba(var(--v-theme-surface-variant), 0.3);
  padding: 16px;
  border-radius: 8px;
  line-height: 1.7;
  max-height: 500px;
  overflow-y: auto;
}
</style>
