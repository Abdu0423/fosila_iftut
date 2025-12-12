<template>
  <Layout role="admin">
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center mb-6">
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">{{ lesson.title }}</h1>
              <p class="text-body-1 text-medium-emphasis">Детальная информация об уроке</p>
            </div>
            <div class="d-flex gap-2">
              <v-btn
                color="primary"
                @click="router.visit(`/admin/lessons/${lesson.id}/edit`)"
                prepend-icon="mdi-pencil"
              >
                Редактировать
              </v-btn>
              <v-btn
                variant="outlined"
                @click="router.visit('/admin/lessons')"
                prepend-icon="mdi-arrow-left"
              >
                Назад к списку
              </v-btn>
            </div>
          </div>
        </v-col>
      </v-row>

      <v-row>
        <!-- Основная информация -->
        <v-col cols="12" md="8">
          <v-card class="mb-6">
            <v-card-title class="text-h6">
              <v-icon start>mdi-book-open-page-variant</v-icon>
              Информация об уроке
            </v-card-title>
            <v-card-text>
              <v-row>
                <v-col cols="12" md="6">
                  <v-list>
                    <v-list-item>
                      <v-list-item-title class="font-weight-bold">Название</v-list-item-title>
                      <v-list-item-subtitle>{{ lesson.title }}</v-list-item-subtitle>
                    </v-list-item>
                    
                    <v-list-item>
                      <v-list-item-title class="font-weight-bold">Предмет</v-list-item-title>
                      <v-list-item-subtitle>
                        <v-chip 
                          v-if="lesson.subject" 
                          color="primary" 
                          variant="tonal"
                          size="small"
                        >
                          {{ lesson.subject.name }}
                        </v-chip>
                        <span v-else class="text-medium-emphasis">Не указан</span>
                      </v-list-item-subtitle>
                    </v-list-item>
                    
                    <v-list-item>
                      <v-list-item-title class="font-weight-bold">Дата создания</v-list-item-title>
                      <v-list-item-subtitle>{{ formatDate(lesson.created_at) }}</v-list-item-subtitle>
                    </v-list-item>
                    
                    <v-list-item>
                      <v-list-item-title class="font-weight-bold">Последнее обновление</v-list-item-title>
                      <v-list-item-subtitle>{{ formatDate(lesson.updated_at) }}</v-list-item-subtitle>
                    </v-list-item>
                  </v-list>
                </v-col>
                
                <v-col cols="12" md="6">
                  <div v-if="lesson.description">
                    <h3 class="text-h6 font-weight-bold mb-3">Описание</h3>
                    <div class="text-body-1">{{ lesson.description }}</div>
                  </div>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>

          <!-- Файл урока -->
          <v-card v-if="lesson.file_name" class="mb-6">
            <v-card-title class="text-h6">
              <v-icon start>mdi-file-document</v-icon>
              Файл урока
            </v-card-title>
            <v-card-text>
              <div class="d-flex align-center">
                <v-avatar size="64" color="primary" class="mr-4">
                  <v-icon size="32" color="white">{{ getFileIcon(lesson.file_type) }}</v-icon>
                </v-avatar>
                <div class="flex-grow-1">
                  <div class="text-h6 font-weight-medium">{{ lesson.file_name }}</div>
                  <div class="text-body-2 text-medium-emphasis">
                    Размер: {{ lesson.file_size_formatted }}
                  </div>
                  <div class="text-body-2 text-medium-emphasis">
                    Тип: {{ lesson.file_type }}
                  </div>
                  <div class="text-body-2 text-medium-emphasis">
                    Загружен: {{ formatDate(lesson.updated_at) }}
                  </div>
                </div>
                <div class="d-flex flex-column gap-2">
                  <v-btn
                    v-if="lesson.download_url"
                    color="primary"
                    :href="lesson.download_url"
                    target="_blank"
                    prepend-icon="mdi-download"
                  >
                    Скачать
                  </v-btn>
                  <v-btn
                    color="secondary"
                    variant="outlined"
                    @click="router.visit(`/admin/lessons/${lesson.id}/edit`)"
                    prepend-icon="mdi-pencil"
                  >
                    Заменить файл
                  </v-btn>
                </div>
              </div>
            </v-card-text>
          </v-card>

          <!-- Сообщение об отсутствии файла -->
          <v-card v-else class="mb-6">
            <v-card-text class="text-center py-8">
              <v-icon size="64" color="grey-lighten-2">mdi-file-remove</v-icon>
              <div class="text-h6 mt-4 text-medium-emphasis">Файл не загружен</div>
              <div class="text-body-2 text-medium-emphasis mb-4">
                Для этого урока еще не загружен файл с учебным материалом
              </div>
              <v-btn
                color="primary"
                @click="router.visit(`/admin/lessons/${lesson.id}/edit`)"
                prepend-icon="mdi-upload"
              >
                Загрузить файл
              </v-btn>
            </v-card-text>
          </v-card>
        </v-col>

        <!-- Боковая панель -->
        <v-col cols="12" md="4">
          <!-- Действия -->
          <v-card class="mb-6">
            <v-card-title class="text-h6">
              <v-icon start>mdi-cog</v-icon>
              Действия
            </v-card-title>
            <v-card-text>
              <div class="d-flex flex-column gap-3">
                <v-btn
                  color="primary"
                  variant="outlined"
                  block
                  @click="router.visit(`/admin/lessons/${lesson.id}/edit`)"
                  prepend-icon="mdi-pencil"
                >
                  Редактировать урок
                </v-btn>
                
                <v-btn
                  v-if="lesson.download_url"
                  color="success"
                  variant="outlined"
                  block
                  :href="lesson.download_url"
                  target="_blank"
                  prepend-icon="mdi-download"
                >
                  Скачать файл
                </v-btn>
                
                <v-btn
                  color="error"
                  variant="outlined"
                  block
                  @click="deleteLesson"
                  prepend-icon="mdi-delete"
                >
                  Удалить урок
                </v-btn>
              </div>
            </v-card-text>
          </v-card>

          <!-- Статистика -->
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-chart-line</v-icon>
              Статистика
            </v-card-title>
            <v-card-text>
              <v-list>
                <v-list-item>
                  <v-list-item-title class="font-weight-bold">Расписаний</v-list-item-title>
                  <v-list-item-subtitle>
                    {{ lesson.schedules?.length || 0 }} связанных расписаний
                  </v-list-item-subtitle>
                </v-list-item>
                
                <v-list-item>
                  <v-list-item-title class="font-weight-bold">Статус файла</v-list-item-title>
                  <v-list-item-subtitle>
                    <v-chip 
                      :color="lesson.file_name ? 'success' : 'warning'" 
                      variant="tonal"
                      size="small"
                    >
                      {{ lesson.file_name ? 'Загружен' : 'Не загружен' }}
                    </v-chip>
                  </v-list-item-subtitle>
                </v-list-item>
              </v-list>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Диалог удаления -->
      <v-dialog v-model="deleteDialog" max-width="400">
        <v-card>
          <v-card-title class="text-h6">
            <v-icon color="error" class="mr-2">mdi-delete</v-icon>
            Удаление урока
          </v-card-title>
          <v-card-text>
            Вы уверены, что хотите удалить урок "{{ lesson.title }}"? Это действие нельзя отменить.
          </v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn
              color="secondary"
              @click="deleteDialog = false"
            >
              Отмена
            </v-btn>
            <v-btn
              color="error"
              @click="confirmDelete"
              :loading="deleting"
            >
              Удалить
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </v-container>
  </Layout>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import Layout from '../../Layout.vue'

// Props из Inertia
const props = defineProps({
  lesson: {
    type: Object,
    required: true
  }
})

// Состояние
const deleteDialog = ref(false)
const deleting = ref(false)

// Методы
const formatDate = (date) => {
  if (!date) return 'Не указано'
  return new Date(date).toLocaleDateString('ru-RU', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getFileIcon = (fileType) => {
  if (!fileType) return 'mdi-file'
  
  if (fileType.includes('pdf')) return 'mdi-file-pdf-box'
  if (fileType.includes('word') || fileType.includes('document')) return 'mdi-file-word-box'
  if (fileType.includes('excel') || fileType.includes('spreadsheet')) return 'mdi-file-excel-box'
  if (fileType.includes('powerpoint') || fileType.includes('presentation')) return 'mdi-file-powerpoint-box'
  if (fileType.includes('image')) return 'mdi-file-image-box'
  if (fileType.includes('text')) return 'mdi-file-document-outline'
  
  return 'mdi-file'
}

const deleteLesson = () => {
  deleteDialog.value = true
}

const confirmDelete = () => {
  deleting.value = true
  router.delete(`/admin/lessons/${props.lesson.id}`, {
    onSuccess: () => {
      router.visit('/admin/lessons')
    },
    onError: () => {
      deleting.value = false
    }
  })
}
</script>

<style scoped>
.v-card {
  border-radius: 12px;
}
</style>