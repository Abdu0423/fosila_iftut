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
            @click="$inertia.visit(`/teacher/coursework/${schedule.id}`)"
            class="mb-2"
          >
            {{ t('common.back', {}, { default: 'Назад' }) }}
          </v-btn>
          <div class="d-flex justify-space-between align-center">
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">{{ t('coursework.student_work', {}, { default: 'Работа студента' }) }}</h1>
              <p class="text-body-1 text-medium-emphasis">
                {{ schedule.group }} - {{ schedule.subject }}
              </p>
            </div>
            <v-chip
              :color="getStatusColor(submission.status)"
              size="large"
              variant="tonal"
            >
              {{ getStatusText(submission.status) }}
            </v-chip>
          </div>
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
              {{ t('coursework.student_info', {}, { default: 'Информация о студенте' }) }}
            </v-card-title>
            <v-card-text>
              <div class="d-flex align-center mb-4">
                <v-avatar size="56" color="primary" class="mr-3">
                  <span class="text-h5 text-white">{{ getInitials(student) }}</span>
                </v-avatar>
                <div>
                  <div class="font-weight-medium text-h6">{{ student.full_name }}</div>
                </div>
              </div>

              <v-divider class="mb-4"></v-divider>

              <div class="mb-3">
                <div class="text-caption text-grey">{{ t('coursework.topic', {}, { default: 'Тема' }) }}</div>
                <div class="font-weight-medium">{{ topic.topic_text }}</div>
              </div>

              <div v-if="submission.submitted_at" class="mb-3">
                <div class="text-caption text-grey">{{ t('coursework.submitted_at', {}, { default: 'Отправлено' }) }}</div>
                <div>{{ submission.submitted_at }}</div>
              </div>

              <div v-if="submission.checked_at" class="mb-3">
                <div class="text-caption text-grey">{{ t('coursework.checked_at', {}, { default: 'Проверено' }) }}</div>
                <div>{{ submission.checked_at }}</div>
              </div>
            </v-card-text>
          </v-card>

          <!-- Текущая оценка -->
          <v-card v-if="submission.grade_100 !== null && submission.grade_100 !== undefined">
            <v-card-title>
              <v-icon start>mdi-star</v-icon>
              {{ t('coursework.current_grade', {}, { default: 'Текущая оценка' }) }}
            </v-card-title>
            <v-card-text class="text-center">
              <div class="text-h2 font-weight-bold" :class="`text-${getGradeColor(submission.grade_100)}`">
                {{ submission.grade_10 }}
              </div>
              <div class="text-h5 text-grey mb-2">{{ submission.grade_letter }}</div>
              <div class="text-body-2 text-grey">({{ submission.grade_100 }} / 100)</div>
            </v-card-text>
          </v-card>
        </v-col>

        <!-- Работа и оценка -->
        <v-col cols="12" md="8">
          <!-- Текст работы -->
          <v-card class="mb-4">
            <v-card-title>
              <v-icon start>mdi-file-document</v-icon>
              {{ t('coursework.work_text', {}, { default: 'Текст работы' }) }}
            </v-card-title>
            <v-card-text>
              <div v-if="submission.text" style="white-space: pre-wrap; max-height: 400px; overflow-y: auto;" class="pa-3 bg-grey-lighten-5 rounded">
                {{ submission.text }}
              </div>
              <v-alert v-else type="info" variant="tonal">
                {{ t('coursework.no_text', {}, { default: 'Текст работы не был добавлен' }) }}
              </v-alert>
            </v-card-text>
          </v-card>

          <!-- Файл -->
          <v-card v-if="submission.file_name" class="mb-4">
            <v-card-title>
              <v-icon start>mdi-paperclip</v-icon>
              {{ t('coursework.attached_file', {}, { default: 'Прикрепленный файл' }) }}
            </v-card-title>
            <v-card-text>
              <v-list-item
                :href="`/storage/${submission.file_path}`"
                target="_blank"
                class="bg-grey-lighten-5 rounded"
              >
                <template v-slot:prepend>
                  <v-icon color="primary">mdi-file-document-outline</v-icon>
                </template>
                <v-list-item-title>{{ submission.file_name }}</v-list-item-title>
                <v-list-item-subtitle v-if="submission.file_size">{{ submission.file_size }}</v-list-item-subtitle>
                <template v-slot:append>
                  <v-icon>mdi-download</v-icon>
                </template>
              </v-list-item>
            </v-card-text>
          </v-card>

          <!-- Форма оценки -->
          <v-card>
            <v-card-title>
              <v-icon start>mdi-checkbox-marked-circle</v-icon>
              {{ t('coursework.grading', {}, { default: 'Оценивание работы' }) }}
            </v-card-title>
            <v-card-text>
              <v-form @submit.prevent="submitGrade">
                <v-row>
                  <v-col cols="12" sm="6">
                    <v-text-field
                      v-model.number="gradeForm.grade_100"
                      :label="t('coursework.grade_100', {}, { default: 'Оценка (0-100)' })"
                      type="number"
                      min="0"
                      max="100"
                      variant="outlined"
                      :error-messages="gradeForm.errors.grade_100"
                    >
                      <template v-slot:append-inner>
                        <div v-if="calculated10Grade !== null" class="text-right">
                          <div class="text-h6 font-weight-bold" :class="`text-${getGradeColor(gradeForm.grade_100)}`">
                            {{ calculated10Grade }}
                          </div>
                          <div class="text-caption text-grey">{{ calculatedLetter }}</div>
                        </div>
                      </template>
                    </v-text-field>
                  </v-col>
                </v-row>

                <v-textarea
                  v-model="gradeForm.teacher_comment"
                  :label="t('coursework.teacher_comment', {}, { default: 'Комментарий учителя' })"
                  variant="outlined"
                  rows="4"
                  :placeholder="t('coursework.comment_placeholder', {}, { default: 'Напишите комментарий к работе студента...' })"
                ></v-textarea>

                <div class="d-flex gap-3 mt-4">
                  <v-btn
                    color="success"
                    :loading="gradeForm.processing && gradeForm.action === 'check'"
                    prepend-icon="mdi-check"
                    @click="submitGrade('check')"
                    :disabled="!gradeForm.grade_100"
                  >
                    {{ t('coursework.accept_work', {}, { default: 'Принять работу' }) }}
                  </v-btn>
                  <v-btn
                    color="warning"
                    variant="outlined"
                    :loading="gradeForm.processing && gradeForm.action === 'return'"
                    prepend-icon="mdi-undo"
                    @click="submitGrade('return')"
                  >
                    {{ t('coursework.return_for_revision', {}, { default: 'Вернуть на доработку' }) }}
                  </v-btn>
                </div>
              </v-form>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </Layout>
</template>

<script setup>
import { computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import Layout from '../../Layout.vue'

const { t } = useI18n()

const props = defineProps({
  submission: Object,
  topic: Object,
  student: Object,
  schedule: Object
})

const gradeForm = useForm({
  grade_100: props.submission.grade_100 || null,
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
  if (!grade) return 'grey'
  if (grade >= 90) return 'success'
  if (grade >= 75) return 'info'
  if (grade >= 60) return 'warning'
  return 'error'
}

const calculated10Grade = computed(() => {
  const g = gradeForm.grade_100
  if (g === null || g === undefined || g === '') return null
  if (g < 50) return 0
  if (g < 55) return 1
  if (g < 60) return 2
  if (g < 65) return 3
  if (g < 70) return 4
  if (g < 75) return 5
  if (g < 80) return 6
  if (g < 85) return 7
  if (g < 90) return 8
  if (g < 95) return 9
  return 10
})

const calculatedLetter = computed(() => {
  const g = gradeForm.grade_100
  if (g === null || g === undefined || g === '') return ''
  if (g < 50) return 'F'
  if (g < 55) return 'D'
  if (g < 60) return 'D+'
  if (g < 65) return 'C-'
  if (g < 70) return 'C'
  if (g < 75) return 'C+'
  if (g < 80) return 'B-'
  if (g < 85) return 'B'
  if (g < 90) return 'B+'
  if (g < 95) return 'A-'
  return 'A'
})

const submitGrade = (action) => {
  gradeForm.action = action
  gradeForm.post(`/teacher/coursework/submission/${props.submission.id}/check`)
}
</script>
