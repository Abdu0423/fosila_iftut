<template>
  <Layout role="teacher">
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center mb-6">
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">Редактировать урок</h1>
              <p class="text-body-1 text-medium-emphasis">{{ lesson.title }}</p>
            </div>
            <v-btn
              color="secondary"
              variant="outlined"
              @click="goBack"
              prepend-icon="mdi-arrow-left"
            >
              Назад к списку
            </v-btn>
          </div>
        </v-col>
      </v-row>

      <!-- Уведомления -->
      <v-row v-if="page.props.flash?.error">
        <v-col cols="12">
          <v-alert
            type="error"
            variant="tonal"
            closable
          >
            {{ page.props.flash.error }}
          </v-alert>
        </v-col>
      </v-row>

      <!-- Форма редактирования урока -->
      <v-row>
        <v-col cols="12" md="8">
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-pencil</v-icon>
              Информация об уроке
            </v-card-title>
            <v-card-text>
              <v-form @submit.prevent="updateLesson">
                <v-row>
                  <v-col cols="12">
                    <v-text-field
                      v-model="form.title"
                      label="Название урока"
                      variant="outlined"
                      density="compact"
                      required
                      :error-messages="form.errors.title"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12">
                    <v-textarea
                      v-model="form.description"
                      label="Описание урока"
                      variant="outlined"
                      density="compact"
                      rows="4"
                      :error-messages="form.errors.description"
                    ></v-textarea>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-select
                      v-model="form.schedule_id"
                      :items="schedules"
                      item-title="display_name"
                      item-value="id"
                      label="Расписание"
                      variant="outlined"
                      density="compact"
                      required
                      :error-messages="form.errors.schedule_id"
                    >
                      <template v-slot:item="{ props, item }">
                        <v-list-item v-bind="props">
                          <v-list-item-title>{{ item.raw.subject?.name }} - {{ item.raw.group?.name }}</v-list-item-title>
                          <v-list-item-subtitle>{{ item.raw.start_date }} - {{ item.raw.end_date }}</v-list-item-subtitle>
                        </v-list-item>
                      </template>
                    </v-select>
                  </v-col>
                  <v-col cols="12">
                    <!-- Текущий файл -->
                    <div v-if="lesson.file_name" class="mb-4">
                      <v-alert
                        type="info"
                        variant="tonal"
                        class="mb-2"
                      >
                        Текущий файл: {{ lesson.file_name }}
                        <template v-slot:append>
                          <v-btn
                            color="primary"
                            variant="text"
                            size="small"
                            @click="downloadFile"
                          >
                            Скачать
                          </v-btn>
                        </template>
                      </v-alert>
                    </div>
                    
                    <v-file-input
                      v-model="form.file"
                      label="Новый файл урока (PDF, DOC, DOCX, PPT, PPTX, TXT)"
                      variant="outlined"
                      density="compact"
                      accept=".pdf,.doc,.docx,.ppt,.pptx,.txt"
                      prepend-icon="mdi-file-document"
                      :error-messages="form.errors.file"
                    ></v-file-input>
                    <v-alert
                      type="info"
                      variant="tonal"
                      class="mt-2"
                    >
                      Максимальный размер файла: 10MB. Оставьте пустым, чтобы сохранить текущий файл.
                    </v-alert>
                  </v-col>
                </v-row>
                <div class="d-flex justify-end gap-2 mt-4">
                  <v-btn
                    color="secondary"
                    @click="goBack"
                  >
                    Отмена
                  </v-btn>
                  <v-btn
                    color="primary"
                    type="submit"
                    :loading="form.processing"
                  >
                    Сохранить изменения
                  </v-btn>
                </div>
              </v-form>
            </v-card-text>
          </v-card>
        </v-col>
        
        <v-col cols="12" md="4">
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-information</v-icon>
              Информация
            </v-card-title>
            <v-card-text>
              <div class="text-body-2 text-medium-emphasis mb-4">
                <p>Отредактируйте информацию об уроке и при необходимости замените файл.</p>
              </div>
              
              <div class="mt-4">
                <h3 class="text-h6 mb-2">Текущая информация:</h3>
                <v-card variant="outlined">
                  <v-card-text>
                    <div class="text-subtitle-1 font-weight-medium mb-2">
                      {{ lesson.title }}
                    </div>
                    <div class="text-body-2 text-medium-emphasis mb-1">
                      Предмет: {{ lesson.subject?.name }}
                    </div>
                    <div class="text-body-2 text-medium-emphasis mb-1">
                      Создан: {{ formatDate(lesson.created_at) }}
                    </div>
                    <div class="text-body-2 text-medium-emphasis">
                      Обновлен: {{ formatDate(lesson.updated_at) }}
                    </div>
                  </v-card-text>
                </v-card>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </Layout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useForm, usePage, router } from '@inertiajs/vue3'
import Layout from '../../Layout.vue'

const page = usePage()

// Props
const props = defineProps({
  lesson: {
    type: Object,
    required: true
  },
  schedules: {
    type: Array,
    default: () => []
  }
})

// Форма
const form = useForm({
  title: props.lesson.title,
  description: props.lesson.description,
  schedule_id: props.lesson.schedules?.[0]?.id || null,
  file: null
})

// Методы
const goBack = () => {
  router.visit('/teacher/lessons')
}

const updateLesson = () => {
  form.put(`/teacher/lessons/${props.lesson.id}`, {
    onSuccess: () => {
      console.log('Урок обновлен успешно')
    },
    onError: (errors) => {
      console.error('Ошибки при обновлении урока:', errors)
    }
  })
}

const downloadFile = () => {
  if (props.lesson.file_path) {
    window.open(`/storage/${props.lesson.file_path}`, '_blank')
  }
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
</script>

<style scoped>
.v-card {
  border-radius: 12px;
}
</style>
