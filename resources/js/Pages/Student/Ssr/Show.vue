<template>
  <Layout>
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <v-btn
            variant="text"
            color="secondary"
            prepend-icon="mdi-arrow-left"
            @click="$inertia.visit('/student/ssr')"
            class="mb-2"
          >
            {{ t('common.back', {}, { default: 'Назад' }) }}
          </v-btn>
          <h1 class="text-h4 font-weight-bold mb-2">{{ task.title }}</h1>
          <div class="d-flex align-center gap-2 mt-2">
            <v-chip v-if="task.schedule" color="primary" size="small">
              {{ task.schedule.subject }}
            </v-chip>
            <v-chip v-if="task.deadline" :color="task.is_overdue ? 'error' : 'info'" size="small">
              <v-icon start size="small">mdi-calendar</v-icon>
              {{ task.deadline }}
            </v-chip>
          </div>
        </v-col>
      </v-row>

      <!-- Сообщения -->
      <v-row>
        <v-col cols="12">
          <v-alert v-if="message" :type="messageType" variant="tonal" closable @click:close="message = null">
            {{ message }}
          </v-alert>
        </v-col>
      </v-row>

      <v-row>
        <!-- Информация о задании -->
        <v-col cols="12" md="4">
          <v-card class="mb-4">
            <v-card-title>
              <v-icon start>mdi-information</v-icon>
              {{ t('ssr.task_description', {}, { default: 'Описание задания' }) }}
            </v-card-title>
            <v-card-text>
              <p v-if="task.description" class="text-body-1" style="white-space: pre-wrap;">{{ task.description }}</p>
              <p v-else class="text-grey">{{ t('ssr.no_description', {}, { default: 'Описание не указано' }) }}</p>
            </v-card-text>
          </v-card>

          <v-card v-if="task.requirements">
            <v-card-title>
              <v-icon start>mdi-clipboard-list</v-icon>
              {{ t('ssr.requirements', {}, { default: 'Требования к работе' }) }}
            </v-card-title>
            <v-card-text>
              <p class="text-body-1" style="white-space: pre-wrap;">{{ task.requirements }}</p>
            </v-card-text>
          </v-card>
        </v-col>

        <!-- Основной контент -->
        <v-col cols="12" md="8">
          <!-- Если тема не выбрана - показываем список тем -->
          <v-card v-if="!selectedTopic">
            <v-card-title>
              <v-icon start>mdi-format-list-bulleted</v-icon>
              {{ t('ssr.select_topic', {}, { default: 'Выберите тему' }) }}
            </v-card-title>
            <v-card-text>
              <v-alert type="info" variant="tonal" density="compact" class="mb-4">
                {{ t('ssr.topic_selection_info', {}, { default: 'Каждую тему может выбрать только один студент. После выбора тему нельзя изменить.' }) }}
              </v-alert>

              <v-list>
                <v-list-item
                  v-for="topic in topics"
                  :key="topic.id"
                  :disabled="topic.is_taken"
                  :class="{ 'topic-taken': topic.is_taken, 'topic-available': !topic.is_taken }"
                  @click="!topic.is_taken && confirmSelectTopic(topic)"
                >
                  <template v-slot:prepend>
                    <v-icon v-if="topic.is_taken" color="grey">mdi-lock</v-icon>
                    <v-icon v-else color="primary">mdi-checkbox-blank-circle-outline</v-icon>
                  </template>
                  <v-list-item-title :class="{ 'text-grey': topic.is_taken }">
                    {{ topic.topic_text }}
                  </v-list-item-title>
                  <template v-slot:append>
                    <v-chip v-if="topic.is_taken" size="small" color="grey" variant="tonal">
                      {{ t('ssr.taken', {}, { default: 'Занята' }) }}
                    </v-chip>
                    <v-icon v-else color="primary">mdi-chevron-right</v-icon>
                  </template>
                </v-list-item>
              </v-list>
            </v-card-text>
          </v-card>

          <!-- Если тема выбрана - показываем форму работы -->
          <template v-else>
            <!-- Информация о выбранной теме -->
            <v-card class="mb-4">
              <v-card-title class="d-flex align-center">
                <v-icon start color="success">mdi-check-circle</v-icon>
                {{ t('ssr.your_topic', {}, { default: 'Ваша тема' }) }}
              </v-card-title>
              <v-card-text>
                <p class="text-h6 mb-0">{{ selectedTopic.topic_text }}</p>
              </v-card-text>
            </v-card>

            <!-- Форма работы -->
            <v-card v-if="!submission || submission.status === 'draft' || submission.status === 'returned'">
              <v-card-title>
                <v-icon start>mdi-pencil</v-icon>
                {{ t('ssr.write_work', {}, { default: 'Напишите работу' }) }}
              </v-card-title>
              <v-card-text>
                <!-- Предупреждение о возврате -->
                <v-alert v-if="submission?.status === 'returned'" type="warning" variant="tonal" class="mb-4">
                  <div class="font-weight-bold mb-2">{{ t('ssr.returned_for_revision', {}, { default: 'Работа возвращена на доработку' }) }}</div>
                  <div v-if="submission.teacher_comment" style="white-space: pre-wrap;">{{ submission.teacher_comment }}</div>
                </v-alert>

                <v-textarea
                  v-model="workText"
                  :label="t('ssr.work_text', {}, { default: 'Текст работы' })"
                  variant="outlined"
                  rows="12"
                  auto-grow
                  :hint="t('ssr.work_hint', {}, { default: 'Напишите текст своей работы' })"
                  persistent-hint
                  class="mb-4"
                ></v-textarea>

                <!-- Загрузка файла -->
                <v-file-input
                  v-model="workFile"
                  :label="t('ssr.attach_file', {}, { default: 'Прикрепить файл (необязательно)' })"
                  variant="outlined"
                  density="comfortable"
                  prepend-icon="mdi-paperclip"
                  :hint="t('ssr.file_hint', {}, { default: 'Максимальный размер: 10 МБ' })"
                  persistent-hint
                  class="mb-4"
                  accept=".pdf,.doc,.docx,.txt,.ppt,.pptx"
                ></v-file-input>

                <!-- Прикрепленный файл -->
                <v-card v-if="submission?.file_name && !workFile" variant="outlined" class="pa-3 mb-4">
                  <div class="d-flex align-center">
                    <v-icon size="32" color="primary" class="mr-3">mdi-file-document</v-icon>
                    <div class="flex-grow-1">
                      <div class="font-weight-medium">{{ submission.file_name }}</div>
                    </div>
                    <v-btn
                      variant="text"
                      color="error"
                      icon
                      @click="deleteFile"
                      :loading="deletingFile"
                    >
                      <v-icon>mdi-delete</v-icon>
                    </v-btn>
                  </div>
                </v-card>

                <div class="d-flex gap-3">
                  <v-btn
                    variant="outlined"
                    color="primary"
                    @click="saveWork"
                    :loading="saving"
                    prepend-icon="mdi-content-save"
                  >
                    {{ t('ssr.save_draft', {}, { default: 'Сохранить черновик' }) }}
                  </v-btn>
                  <v-btn
                    color="success"
                    @click="confirmSubmit"
                    :loading="submitting"
                    :disabled="!canSubmit"
                    prepend-icon="mdi-send"
                  >
                    {{ t('ssr.submit_work', {}, { default: 'Отправить на проверку' }) }}
                  </v-btn>
                </div>
              </v-card-text>
            </v-card>

            <!-- Отправленная работа -->
            <v-card v-else>
              <v-card-title>
                <v-icon start :color="submission.status === 'checked' ? 'success' : 'warning'">
                  {{ submission.status === 'checked' ? 'mdi-check-circle' : 'mdi-clock-outline' }}
                </v-icon>
                {{ submission.status === 'checked' 
                  ? t('ssr.work_checked', {}, { default: 'Работа проверена' })
                  : t('ssr.work_submitted', {}, { default: 'Работа отправлена' }) 
                }}
              </v-card-title>
              <v-card-text>
                <v-alert v-if="submission.status === 'submitted'" type="info" variant="tonal" class="mb-4">
                  {{ t('ssr.waiting_check', {}, { default: 'Ваша работа отправлена на проверку. Ожидайте результат.' }) }}
                </v-alert>

                <!-- Оценка -->
                <v-alert v-if="submission.grade !== null" :type="submission.grade >= 60 ? 'success' : 'error'" variant="tonal" class="mb-4">
                  <div class="d-flex align-center">
                    <span class="text-h5 font-weight-bold mr-3">{{ submission.grade }}</span>
                    <span>{{ t('ssr.grade_label', {}, { default: 'баллов' }) }}</span>
                  </div>
                </v-alert>

                <!-- Комментарий преподавателя -->
                <div v-if="submission.teacher_comment" class="mb-4">
                  <div class="font-weight-bold mb-2">{{ t('ssr.teacher_comment', {}, { default: 'Комментарий преподавателя' }) }}:</div>
                  <v-card variant="outlined" class="pa-3">
                    <p class="mb-0" style="white-space: pre-wrap;">{{ submission.teacher_comment }}</p>
                  </v-card>
                </div>

                <!-- Текст работы -->
                <div class="mb-4">
                  <div class="font-weight-bold mb-2">{{ t('ssr.your_work', {}, { default: 'Ваша работа' }) }}:</div>
                  <v-card variant="outlined" class="pa-3">
                    <p v-if="submission.text" class="mb-0" style="white-space: pre-wrap;">{{ submission.text }}</p>
                    <p v-else class="text-grey mb-0">{{ t('ssr.no_text', {}, { default: 'Текст не добавлен' }) }}</p>
                  </v-card>
                </div>

                <!-- Файл -->
                <v-card v-if="submission.file_name" variant="outlined" class="pa-3">
                  <div class="d-flex align-center">
                    <v-icon size="32" color="primary" class="mr-3">mdi-file-document</v-icon>
                    <div class="flex-grow-1">
                      <div class="font-weight-medium">{{ submission.file_name }}</div>
                    </div>
                  </div>
                </v-card>

                <div class="text-body-2 text-grey mt-4">
                  {{ t('ssr.submitted_at', {}, { default: 'Отправлено' }) }}: {{ submission.submitted_at }}
                </div>
              </v-card-text>
            </v-card>
          </template>
        </v-col>
      </v-row>
    </v-container>

    <!-- Диалог выбора темы -->
    <v-dialog v-model="selectDialog" max-width="500">
      <v-card>
        <v-card-title>{{ t('ssr.confirm_topic_selection', {}, { default: 'Подтверждение выбора темы' }) }}</v-card-title>
        <v-card-text>
          <v-alert type="warning" variant="tonal" class="mb-4">
            {{ t('ssr.topic_selection_warning', {}, { default: 'После выбора темы вы не сможете её изменить!' }) }}
          </v-alert>
          <p class="text-body-1 font-weight-medium">{{ topicToSelect?.topic_text }}</p>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn variant="text" @click="selectDialog = false">{{ t('common.cancel', {}, { default: 'Отмена' }) }}</v-btn>
          <v-btn color="primary" variant="flat" @click="selectTopic" :loading="selecting">
            {{ t('ssr.confirm_select', {}, { default: 'Выбрать' }) }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Диалог отправки -->
    <v-dialog v-model="submitDialog" max-width="400">
      <v-card>
        <v-card-title>{{ t('ssr.confirm_submit', {}, { default: 'Отправить работу?' }) }}</v-card-title>
        <v-card-text>
          {{ t('ssr.submit_warning', {}, { default: 'После отправки вы не сможете редактировать работу до получения результатов проверки.' }) }}
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn variant="text" @click="submitDialog = false">{{ t('common.cancel', {}, { default: 'Отмена' }) }}</v-btn>
          <v-btn color="success" variant="flat" @click="submitWork" :loading="submitting">
            {{ t('ssr.submit', {}, { default: 'Отправить' }) }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
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
  task: {
    type: Object,
    required: true
  },
  topics: {
    type: Array,
    default: () => []
  },
  selectedTopic: {
    type: Object,
    default: null
  },
  submission: {
    type: Object,
    default: null
  }
})

const message = ref(null)
const messageType = ref('info')

const selectDialog = ref(false)
const topicToSelect = ref(null)
const selecting = ref(false)

const submitDialog = ref(false)

const workText = ref(props.submission?.text || '')
const workFile = ref(null)

const saving = ref(false)
const submitting = ref(false)
const deletingFile = ref(false)

const canSubmit = computed(() => {
  return workText.value.trim() || props.submission?.file_name || workFile.value
})

const confirmSelectTopic = (topic) => {
  topicToSelect.value = topic
  selectDialog.value = true
}

const selectTopic = async () => {
  selecting.value = true
  try {
    const response = await axios.post(`/student/ssr/${props.task.id}/select-topic`, {
      topic_id: topicToSelect.value.id
    })
    
    if (response.data.success) {
      message.value = response.data.message
      messageType.value = 'success'
      selectDialog.value = false
      // Перезагружаем страницу для обновления данных
      router.reload()
    }
  } catch (error) {
    message.value = error.response?.data?.message || t('ssr.error_selecting_topic', {}, { default: 'Ошибка при выборе темы' })
    messageType.value = 'error'
    selectDialog.value = false
  } finally {
    selecting.value = false
  }
}

const saveWork = async () => {
  saving.value = true
  try {
    const formData = new FormData()
    formData.append('text', workText.value)
    if (workFile.value) {
      formData.append('file', workFile.value)
    }

    const response = await axios.post(`/student/ssr/${props.task.id}/save`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    if (response.data.success) {
      message.value = response.data.message
      messageType.value = 'success'
      workFile.value = null
      router.reload()
    }
  } catch (error) {
    message.value = error.response?.data?.message || t('ssr.error_saving', {}, { default: 'Ошибка при сохранении' })
    messageType.value = 'error'
  } finally {
    saving.value = false
  }
}

const confirmSubmit = () => {
  submitDialog.value = true
}

const submitWork = async () => {
  submitting.value = true
  try {
    const response = await axios.post(`/student/ssr/${props.task.id}/submit`, {
      text: workText.value
    })

    if (response.data.success) {
      message.value = response.data.message
      messageType.value = 'success'
      submitDialog.value = false
      router.reload()
    }
  } catch (error) {
    message.value = error.response?.data?.message || t('ssr.error_submitting', {}, { default: 'Ошибка при отправке' })
    messageType.value = 'error'
    submitDialog.value = false
  } finally {
    submitting.value = false
  }
}

const deleteFile = async () => {
  deletingFile.value = true
  try {
    const response = await axios.delete(`/student/ssr/${props.task.id}/file`)
    if (response.data.success) {
      router.reload()
    }
  } catch (error) {
    message.value = error.response?.data?.message || t('ssr.error_deleting_file', {}, { default: 'Ошибка при удалении файла' })
    messageType.value = 'error'
  } finally {
    deletingFile.value = false
  }
}
</script>

<style scoped>
.topic-taken {
  opacity: 0.6;
  cursor: not-allowed;
}

.topic-available {
  cursor: pointer;
}

.topic-available:hover {
  background-color: rgba(var(--v-theme-primary), 0.05);
}
</style>
