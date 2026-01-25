<template>
  <Layout role="student">
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <v-btn
            variant="text"
            color="secondary"
            prepend-icon="mdi-arrow-left"
            @click="$inertia.visit('/student/coursework')"
            class="mb-2"
          >
            {{ t('common.back', {}, { default: 'Назад' }) }}
          </v-btn>
          <div class="d-flex justify-space-between align-center">
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">{{ t('coursework.title', {}, { default: 'Курсовая работа' }) }}</h1>
              <p class="text-body-1 text-medium-emphasis">{{ schedule.subject }}</p>
            </div>
            <v-chip v-if="submission" :color="getStatusColor(submission.status)" size="large" variant="tonal">
              {{ getStatusText(submission.status) }}
            </v-chip>
          </div>
        </v-col>
      </v-row>

      <!-- Уведомления -->
      <v-row v-if="errorMessage">
        <v-col cols="12">
          <v-alert type="error" variant="tonal" closable @click:close="errorMessage = ''">
            {{ errorMessage }}
          </v-alert>
        </v-col>
      </v-row>

      <v-row v-if="successMessage">
        <v-col cols="12">
          <v-alert type="success" variant="tonal" closable @click:close="successMessage = ''">
            {{ successMessage }}
          </v-alert>
        </v-col>
      </v-row>

      <v-row>
        <!-- Информация о задании -->
        <v-col cols="12" md="4">
          <v-card class="mb-4">
            <v-card-title>
              <v-icon start>mdi-information</v-icon>
              {{ t('coursework.task_info', {}, { default: 'Информация о задании' }) }}
            </v-card-title>
            <v-card-text>
              <div v-if="task.title" class="mb-3">
                <div class="text-caption text-grey">{{ t('coursework.work_title', {}, { default: 'Название' }) }}</div>
                <div class="font-weight-medium">{{ task.title }}</div>
              </div>

              <div v-if="task.description" class="mb-3">
                <div class="text-caption text-grey">{{ t('coursework.description', {}, { default: 'Описание' }) }}</div>
                <div style="white-space: pre-wrap;">{{ task.description }}</div>
              </div>

              <div v-if="task.requirements" class="mb-3">
                <div class="text-caption text-grey">{{ t('coursework.requirements', {}, { default: 'Требования' }) }}</div>
                <div style="white-space: pre-wrap;">{{ task.requirements }}</div>
              </div>

              <div v-if="task.deadline" class="mb-3">
                <div class="text-caption text-grey">{{ t('coursework.deadline', {}, { default: 'Срок сдачи' }) }}</div>
                <div :class="task.is_overdue ? 'text-error font-weight-bold' : ''">
                  {{ task.deadline }}
                  <v-chip v-if="task.is_overdue" color="error" size="x-small" class="ml-2">
                    {{ t('coursework.overdue', {}, { default: 'Просрочено' }) }}
                  </v-chip>
                </div>
              </div>
            </v-card-text>
          </v-card>

          <!-- Оценка -->
          <v-card v-if="submission && submission.grade_100 !== null && submission.grade_100 !== undefined">
            <v-card-title>
              <v-icon start>mdi-star</v-icon>
              {{ t('coursework.your_grade', {}, { default: 'Ваша оценка' }) }}
            </v-card-title>
            <v-card-text class="text-center">
              <div class="text-h2 font-weight-bold" :class="`text-${getGradeColor(submission.grade_100)}`">
                {{ submission.grade_10 }}
              </div>
              <div class="text-h5 text-grey mb-2">{{ submission.grade_letter }}</div>
              <div class="text-body-2 text-grey">({{ submission.grade_100 }} / 100)</div>
            </v-card-text>
          </v-card>

          <!-- Комментарий учителя -->
          <v-card v-if="submission && submission.teacher_comment" class="mt-4">
            <v-card-title>
              <v-icon start>mdi-comment-text</v-icon>
              {{ t('coursework.teacher_comment', {}, { default: 'Комментарий учителя' }) }}
            </v-card-title>
            <v-card-text>
              <div style="white-space: pre-wrap;" class="pa-3 bg-grey-lighten-5 rounded">
                {{ submission.teacher_comment }}
              </div>
            </v-card-text>
          </v-card>
        </v-col>

        <!-- Выбор темы / Работа -->
        <v-col cols="12" md="8">
          <!-- Выбор темы (если тема не выбрана) -->
          <v-card v-if="!selectedTopic">
            <v-card-title>
              <v-icon start>mdi-format-list-bulleted</v-icon>
              {{ t('coursework.select_topic', {}, { default: 'Выберите тему' }) }}
            </v-card-title>
            <v-card-text>
              <v-alert type="info" variant="tonal" density="compact" class="mb-4">
                {{ t('coursework.topic_selection_info', {}, { default: 'После выбора темы вы не сможете её изменить. Выбирайте внимательно!' }) }}
              </v-alert>

              <v-list>
                <template v-for="(topic, index) in topics" :key="topic.id">
                  <v-list-item
                    v-if="!topic.is_taken"
                    :class="selectedTopicToConfirm === topic.id ? 'bg-primary-lighten-5' : ''"
                  >
                    <template v-slot:prepend>
                      <span class="text-grey mr-3">{{ index + 1 }}.</span>
                    </template>
                    <v-list-item-title>{{ topic.topic_text }}</v-list-item-title>
                    <template v-slot:append>
                      <v-btn
                        color="primary"
                        size="small"
                        @click="confirmSelectTopic(topic)"
                        :loading="selectingTopic === topic.id"
                      >
                        {{ t('coursework.choose', {}, { default: 'Выбрать' }) }}
                      </v-btn>
                    </template>
                  </v-list-item>
                  <v-list-item v-else disabled>
                    <template v-slot:prepend>
                      <span class="text-grey mr-3">{{ index + 1 }}.</span>
                    </template>
                    <v-list-item-title class="text-grey text-decoration-line-through">{{ topic.topic_text }}</v-list-item-title>
                    <template v-slot:append>
                      <v-chip color="grey" size="small" variant="tonal">
                        {{ t('coursework.taken', {}, { default: 'Занята' }) }}
                      </v-chip>
                    </template>
                  </v-list-item>
                  <v-divider v-if="index < topics.length - 1"></v-divider>
                </template>
              </v-list>
            </v-card-text>
          </v-card>

          <!-- Работа над заданием (если тема выбрана) -->
          <template v-else>
            <!-- Выбранная тема -->
            <v-card class="mb-4">
              <v-card-title>
                <v-icon start color="success">mdi-check-circle</v-icon>
                {{ t('coursework.your_topic', {}, { default: 'Ваша тема' }) }}
              </v-card-title>
              <v-card-text>
                <div class="text-h6 font-weight-medium">{{ selectedTopic.topic_text }}</div>
              </v-card-text>
            </v-card>

            <!-- Редактор работы (если можно редактировать) -->
            <v-card v-if="canEdit">
              <v-card-title>
                <v-icon start>mdi-pencil</v-icon>
                {{ t('coursework.work_area', {}, { default: 'Работа' }) }}
              </v-card-title>
              <v-card-text>
                <v-textarea
                  v-model="workText"
                  :label="t('coursework.work_text', {}, { default: 'Текст работы' })"
                  variant="outlined"
                  rows="12"
                  :placeholder="t('coursework.work_placeholder', {}, { default: 'Напишите вашу курсовую работу...' })"
                  class="mb-4"
                ></v-textarea>

                <v-file-input
                  v-if="!currentFileName"
                  v-model="workFile"
                  :label="t('coursework.attach_file', {}, { default: 'Прикрепить файл' })"
                  variant="outlined"
                  prepend-icon="mdi-paperclip"
                  show-size
                  accept=".doc,.docx,.pdf,.zip,.rar"
                  :hint="t('coursework.file_hint', {}, { default: 'Форматы: DOC, DOCX, PDF, ZIP, RAR. Максимум 20 МБ' })"
                  persistent-hint
                ></v-file-input>

                <v-list-item v-else class="bg-grey-lighten-5 rounded mb-4">
                  <template v-slot:prepend>
                    <v-icon color="primary">mdi-file-document-outline</v-icon>
                  </template>
                  <v-list-item-title>{{ currentFileName }}</v-list-item-title>
                  <template v-slot:append>
                    <v-btn
                      icon
                      variant="text"
                      color="error"
                      size="small"
                      @click="deleteFile"
                      :loading="deletingFile"
                    >
                      <v-icon>mdi-delete</v-icon>
                    </v-btn>
                  </template>
                </v-list-item>

                <div class="d-flex gap-3 mt-4">
                  <v-btn
                    color="secondary"
                    variant="outlined"
                    @click="saveWork"
                    :loading="saving"
                    prepend-icon="mdi-content-save"
                  >
                    {{ t('coursework.save_draft', {}, { default: 'Сохранить черновик' }) }}
                  </v-btn>
                  <v-btn
                    color="primary"
                    @click="showSubmitDialog = true"
                    prepend-icon="mdi-send"
                    :disabled="!workText && !currentFileName"
                  >
                    {{ t('coursework.submit_work', {}, { default: 'Отправить на проверку' }) }}
                  </v-btn>
                </div>
              </v-card-text>
            </v-card>

            <!-- Отправленная работа (если редактировать нельзя) -->
            <v-card v-else>
              <v-card-title>
                <v-icon start>mdi-file-document-check</v-icon>
                {{ t('coursework.submitted_work', {}, { default: 'Отправленная работа' }) }}
              </v-card-title>
              <v-card-text>
                <div v-if="submission.text" style="white-space: pre-wrap; max-height: 400px; overflow-y: auto;" class="pa-3 bg-grey-lighten-5 rounded mb-4">
                  {{ submission.text }}
                </div>

                <v-list-item v-if="submission.file_name" class="bg-grey-lighten-5 rounded">
                  <template v-slot:prepend>
                    <v-icon color="primary">mdi-file-document-outline</v-icon>
                  </template>
                  <v-list-item-title>{{ submission.file_name }}</v-list-item-title>
                </v-list-item>

                <div v-if="submission.submitted_at" class="text-body-2 text-grey mt-3">
                  {{ t('coursework.submitted_at', {}, { default: 'Отправлено' }) }}: {{ submission.submitted_at }}
                </div>
              </v-card-text>
            </v-card>
          </template>
        </v-col>
      </v-row>

      <!-- Диалог подтверждения выбора темы -->
      <v-dialog v-model="showTopicDialog" max-width="500">
        <v-card>
          <v-card-title class="text-h6">
            {{ t('coursework.confirm_topic_selection', {}, { default: 'Подтверждение выбора темы' }) }}
          </v-card-title>
          <v-card-text>
            <v-alert type="warning" variant="tonal" class="mb-4">
              {{ t('coursework.topic_change_warning', {}, { default: 'После выбора темы изменить её будет невозможно!' }) }}
            </v-alert>
            <p class="font-weight-medium">{{ t('coursework.selected_topic', {}, { default: 'Выбранная тема' }) }}:</p>
            <p class="text-body-1">{{ topicToSelect?.topic_text }}</p>
          </v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn variant="text" @click="showTopicDialog = false">
              {{ t('common.cancel', {}, { default: 'Отмена' }) }}
            </v-btn>
            <v-btn color="primary" @click="selectTopic" :loading="selectingTopic">
              {{ t('common.confirm', {}, { default: 'Подтвердить' }) }}
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>

      <!-- Диалог подтверждения отправки -->
      <v-dialog v-model="showSubmitDialog" max-width="500">
        <v-card>
          <v-card-title class="text-h6">
            {{ t('coursework.confirm_submit', {}, { default: 'Отправка работы' }) }}
          </v-card-title>
          <v-card-text>
            <p>{{ t('coursework.submit_warning', {}, { default: 'После отправки работа будет направлена на проверку преподавателю. Вы уверены?' }) }}</p>
          </v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn variant="text" @click="showSubmitDialog = false">
              {{ t('common.cancel', {}, { default: 'Отмена' }) }}
            </v-btn>
            <v-btn color="primary" @click="submitWork" :loading="submitting">
              {{ t('coursework.submit', {}, { default: 'Отправить' }) }}
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </v-container>
  </Layout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import axios from 'axios'
import Layout from '../../Layout.vue'

const { t } = useI18n()

const props = defineProps({
  schedule: Object,
  task: Object,
  topics: Array,
  selectedTopic: Object,
  submission: Object
})

const workText = ref(props.submission?.text || '')
const workFile = ref(null)
const currentFileName = ref(props.submission?.file_name || null)

const errorMessage = ref('')
const successMessage = ref('')

const showTopicDialog = ref(false)
const showSubmitDialog = ref(false)
const topicToSelect = ref(null)
const selectingTopic = ref(false)
const saving = ref(false)
const submitting = ref(false)
const deletingFile = ref(false)

const canEdit = computed(() => {
  if (!props.selectedTopic) return false
  if (!props.submission) return true
  return props.submission.status === 'draft' || props.submission.status === 'returned'
})

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

const confirmSelectTopic = (topic) => {
  topicToSelect.value = topic
  showTopicDialog.value = true
}

const selectTopic = async () => {
  if (!topicToSelect.value) return
  
  selectingTopic.value = true
  errorMessage.value = ''

  try {
    const response = await axios.post(`/student/coursework/${props.schedule.id}/select-topic`, {
      topic_id: topicToSelect.value.id
    })

    if (response.data.success) {
      successMessage.value = response.data.message
      showTopicDialog.value = false
      router.reload()
    }
  } catch (error) {
    errorMessage.value = error.response?.data?.message || t('coursework.topic_selection_error', {}, { default: 'Ошибка при выборе темы' })
    showTopicDialog.value = false
  } finally {
    selectingTopic.value = false
  }
}

const saveWork = async () => {
  saving.value = true
  errorMessage.value = ''

  try {
    const formData = new FormData()
    formData.append('text', workText.value || '')
    if (workFile.value && workFile.value.length > 0) {
      formData.append('file', workFile.value[0])
    }

    const response = await axios.post(`/student/coursework/${props.schedule.id}/save`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    if (response.data.success) {
      successMessage.value = response.data.message
      workFile.value = null
      router.reload()
    }
  } catch (error) {
    errorMessage.value = error.response?.data?.message || t('coursework.save_error', {}, { default: 'Ошибка при сохранении' })
  } finally {
    saving.value = false
  }
}

const submitWork = async () => {
  submitting.value = true
  errorMessage.value = ''

  try {
    const response = await axios.post(`/student/coursework/${props.schedule.id}/submit`, {
      text: workText.value
    })

    if (response.data.success) {
      successMessage.value = response.data.message
      showSubmitDialog.value = false
      router.reload()
    }
  } catch (error) {
    errorMessage.value = error.response?.data?.message || t('coursework.submit_error', {}, { default: 'Ошибка при отправке' })
    showSubmitDialog.value = false
  } finally {
    submitting.value = false
  }
}

const deleteFile = async () => {
  deletingFile.value = true

  try {
    const response = await axios.delete(`/student/coursework/${props.schedule.id}/file`)
    if (response.data.success) {
      currentFileName.value = null
    }
  } catch (error) {
    errorMessage.value = error.response?.data?.message || t('coursework.file_delete_error', {}, { default: 'Ошибка при удалении файла' })
  } finally {
    deletingFile.value = false
  }
}
</script>
