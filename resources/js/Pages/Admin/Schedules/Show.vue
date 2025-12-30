<template>
  <Layout role="admin">
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center mb-6">
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">Детали расписания</h1>
              <p class="text-body-1 text-medium-emphasis">{{ schedule?.subject?.name }}</p>
            </div>
            <div class="d-flex gap-2">
              <v-btn
                color="secondary"
                variant="outlined"
                @click="goBack"
                prepend-icon="mdi-arrow-left"
              >
                Назад к списку
              </v-btn>
              <v-btn
                color="primary"
                @click="editSchedule"
                prepend-icon="mdi-pencil"
              >
                Редактировать
              </v-btn>
            </div>
          </div>
        </v-col>
      </v-row>

      <v-row v-if="schedule?.id">
        <!-- Основная информация -->
        <v-col cols="12" lg="8">
          <v-card class="mb-6">
            <v-card-title class="text-h5 pa-6 bg-primary">
              <v-icon icon="mdi-calendar-clock" class="mr-2"></v-icon>
              Основная информация
            </v-card-title>
            <v-card-text class="pa-6">
              <v-row>
                <v-col cols="12" md="6">
                  <div class="mb-4">
                    <div class="text-subtitle-2 text-medium-emphasis mb-1">Предмет</div>
                    <div class="text-h6">{{ schedule.subject?.name }}</div>
                  </div>
                </v-col>
                
                <v-col cols="12" md="6">
                  <div class="mb-4">
                    <div class="text-subtitle-2 text-medium-emphasis mb-1">Преподаватель</div>
                    <div class="text-h6">{{ schedule.teacher?.name }}</div>
                    <div class="text-caption text-medium-emphasis">{{ schedule.teacher?.email }}</div>
                  </div>
                </v-col>

                <v-col cols="12" md="6">
                  <div class="mb-4">
                    <div class="text-subtitle-2 text-medium-emphasis mb-1">Группа</div>
                    <v-chip 
                      color="primary" 
                      variant="tonal" 
                      size="large"
                      class="font-weight-medium"
                    >
                      {{ schedule.group?.name }}
                    </v-chip>
                  </div>
                </v-col>

                <v-col cols="12" md="6">
                  <div class="mb-4">
                    <div class="text-subtitle-2 text-medium-emphasis mb-1">Семестр</div>
                    <v-chip 
                      :color="schedule.semester === 1 ? 'success' : 'info'" 
                      variant="tonal" 
                      size="large"
                      class="font-weight-medium"
                    >
                      {{ schedule.semester }} семестр
                    </v-chip>
                  </div>
                </v-col>

                <v-col cols="12" md="6">
                  <div class="mb-4">
                    <div class="text-subtitle-2 text-medium-emphasis mb-1">Кредиты</div>
                    <div class="text-h6">{{ schedule.credits || 0 }}</div>
                  </div>
                </v-col>

                <v-col cols="12" md="6">
                  <div class="mb-4">
                    <div class="text-subtitle-2 text-medium-emphasis mb-1">Год обучения</div>
                    <div class="text-h6">{{ schedule.study_year }}</div>
                  </div>
                </v-col>

                <v-col cols="12" md="6">
                  <div class="mb-4">
                    <div class="text-subtitle-2 text-medium-emphasis mb-1">Порядковый номер</div>
                    <div class="text-h6">{{ schedule.order }}</div>
                  </div>
                </v-col>

                <v-col cols="12" md="6">
                  <div class="mb-4">
                    <div class="text-subtitle-2 text-medium-emphasis mb-1">Статус</div>
                    <v-chip 
                      :color="schedule.is_active ? 'success' : 'error'" 
                      variant="tonal" 
                      size="large"
                      class="font-weight-medium"
                    >
                      {{ schedule.is_active ? 'Активен' : 'Неактивен' }}
                    </v-chip>
                  </div>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>

          <!-- Дата и время -->
          <v-card class="mb-6" v-if="schedule.scheduled_at">
            <v-card-title class="text-h5 pa-6 bg-info">
              <v-icon icon="mdi-clock-outline" class="mr-2"></v-icon>
              Дата и время проведения
            </v-card-title>
            <v-card-text class="pa-6">
              <div class="text-center">
                <div class="text-h4 font-weight-bold mb-2">
                  {{ formatDate(schedule.scheduled_at) }}
                </div>
                <div class="text-h5 text-medium-emphasis">
                  {{ formatTime(schedule.scheduled_at) }}
                </div>
              </div>
            </v-card-text>
          </v-card>

          <!-- Действия -->
          <v-card>
            <v-card-title class="text-h5 pa-6">
              <v-icon icon="mdi-cog" class="mr-2"></v-icon>
              Действия
            </v-card-title>
            <v-card-text class="pa-6">
              <div class="d-flex flex-wrap gap-3">
                <v-btn
                  color="primary"
                  @click="editSchedule"
                  prepend-icon="mdi-pencil"
                >
                  Редактировать
                </v-btn>
                <v-btn
                  color="success"
                  @click="duplicateSchedule"
                  prepend-icon="mdi-content-copy"
                >
                  Дублировать
                </v-btn>
                <v-btn
                  :color="schedule.is_active ? 'warning' : 'success'"
                  @click="toggleStatus"
                  :prepend-icon="schedule.is_active ? 'mdi-pause' : 'mdi-play'"
                >
                  {{ schedule.is_active ? 'Деактивировать' : 'Активировать' }}
                </v-btn>
                <v-btn
                  color="error"
                  @click="confirmDelete"
                  prepend-icon="mdi-delete"
                >
                  Удалить
                </v-btn>
              </div>
            </v-card-text>
          </v-card>
        </v-col>

        <!-- Боковая панель -->
        <v-col cols="12" lg="4">
          <!-- Информация о предмете -->
          <v-card class="mb-6" v-if="schedule.subject">
            <v-card-title class="text-h6 pa-4">
              <v-icon icon="mdi-book-education" class="mr-2"></v-icon>
              О предмете
            </v-card-title>
            <v-card-text class="pa-4">
              <div class="text-body-2 mb-2">
                <strong>Название:</strong> {{ schedule.subject.name }}
              </div>
              <div class="text-body-2 mb-2" v-if="schedule.subject.description">
                <strong>Описание:</strong> {{ schedule.subject.description }}
              </div>
            </v-card-text>
          </v-card>

          <!-- Метаданные -->
          <v-card>
            <v-card-title class="text-h6 pa-4">
              <v-icon icon="mdi-information" class="mr-2"></v-icon>
              Метаданные
            </v-card-title>
            <v-card-text class="pa-4">
              <v-list density="compact">
                <v-list-item>
                  <v-list-item-title class="text-body-2">
                    <strong>ID:</strong> {{ schedule.id }}
                  </v-list-item-title>
                </v-list-item>
                <v-list-item>
                  <v-list-item-title class="text-body-2">
                    <strong>Создано:</strong> {{ formatDate(schedule.created_at) }}
                  </v-list-item-title>
                </v-list-item>
                <v-list-item>
                  <v-list-item-title class="text-body-2">
                    <strong>Обновлено:</strong> {{ formatDate(schedule.updated_at) }}
                  </v-list-item-title>
                </v-list-item>
              </v-list>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Заглушка когда данные не загружены -->
      <v-row v-else>
        <v-col cols="12">
          <v-card>
            <v-card-text class="text-center pa-6">
              <v-progress-circular
                indeterminate
                color="primary"
                size="64"
                class="mb-4"
              />
              <h3>Загрузка данных расписания...</h3>
              <p class="text-medium-emphasis mt-2">
                Если загрузка занимает слишком много времени, попробуйте обновить страницу.
              </p>
              <v-btn @click="goBack" class="mt-4">
                Вернуться к списку
              </v-btn>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>

    <!-- Диалог подтверждения удаления -->
    <v-dialog v-model="deleteDialog" max-width="500px">
      <v-card>
        <v-card-title>Подтверждение удаления</v-card-title>
        <v-card-text>
          Вы уверены, что хотите удалить это расписание?
          <br><br>
          <strong>Предмет:</strong> {{ schedule?.subject?.name }}<br>
          <strong>Группа:</strong> {{ schedule?.group?.name }}<br>
          <strong>Преподаватель:</strong> {{ schedule?.teacher?.name }}
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn @click="deleteDialog = false">Отмена</v-btn>
          <v-btn @click="deleteSchedule" color="error">Удалить</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </Layout>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import Layout from '../../Layout.vue'

// Props
const props = defineProps({
  schedule: {
    type: Object,
    default: () => ({})
  }
})

const deleteDialog = ref(false)

// Methods
const goBack = () => {
  router.visit('/admin/schedules')
}

const editSchedule = () => {
  router.visit(`/admin/schedules/${props.schedule.id}/edit`)
}

const duplicateSchedule = () => {
  router.post(`/admin/schedules/${props.schedule.id}/duplicate`, {}, {
    preserveState: true,
    onSuccess: () => {
      // Schedule duplicated successfully
    },
    onError: (errors) => {
      console.error('Ошибка при дублировании расписания:', errors)
    }
  })
}

const toggleStatus = () => {
  router.post(`/admin/schedules/${props.schedule.id}/toggle-status`, {}, {
    preserveState: true,
    onSuccess: () => {
      // Status updated successfully
    },
    onError: (errors) => {
      console.error('Ошибка при изменении статуса:', errors)
    }
  })
}

const confirmDelete = () => {
  deleteDialog.value = true
}

const deleteSchedule = () => {
  router.delete(`/admin/schedules/${props.schedule.id}`, {
    onSuccess: () => {
      deleteDialog.value = false
      // После успешного удаления произойдет автоматический redirect на index страницу
    },
    onError: (errors) => {
      console.error('Ошибка при удалении расписания:', errors)
      deleteDialog.value = false
    }
  })
}

// Utility functions
const formatDate = (dateString) => {
  if (!dateString) return 'Не указано'
  return new Date(dateString).toLocaleDateString('ru-RU', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const formatTime = (dateString) => {
  if (!dateString) return ''
  return new Date(dateString).toLocaleTimeString('ru-RU', {
    hour: '2-digit',
    minute: '2-digit'
  })
}
</script>

<style scoped>
.v-card {
  border-radius: 12px;
}

.bg-primary {
  background-color: rgb(var(--v-theme-primary)) !important;
  color: white;
}

.bg-info {
  background-color: rgb(var(--v-theme-info)) !important;
  color: white;
}
</style>