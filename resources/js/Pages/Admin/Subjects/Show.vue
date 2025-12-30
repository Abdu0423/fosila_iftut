<template>
  <Layout role="admin">
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex align-center justify-space-between mb-6">
            <div class="d-flex align-center">
              <v-btn
                icon="mdi-arrow-left"
                variant="text"
                @click="goBack"
                class="mr-3"
              />
              <div>
                <h1 class="text-h4 font-weight-bold mb-2">{{ subject.name }}</h1>
                <p class="text-body-1 text-medium-emphasis">
                  Статус: {{ subject.is_active ? 'Активный' : 'Неактивный' }}
                </p>
              </div>
            </div>
            <div class="d-flex gap-2">
              <v-btn
                color="warning"
                variant="outlined"
                prepend-icon="mdi-pencil"
                @click="editSubject"
              >
                Редактировать
              </v-btn>
              <v-btn
                color="error"
                variant="outlined"
                prepend-icon="mdi-delete"
                @click="confirmDelete"
              >
                Удалить
              </v-btn>
            </div>
          </div>
        </v-col>
      </v-row>

      <v-row>
        <!-- Основная информация -->
        <v-col cols="12" lg="8">
          <v-card class="mb-4">
            <v-card-title class="text-h5 pa-6">
              Основная информация
            </v-card-title>
            <v-card-text class="pa-6">
              <v-row>
                <v-col cols="12" md="6">
                  <div class="mb-4">
                    <div class="text-subtitle-2 text-medium-emphasis mb-1">Название предмета</div>
                    <div class="text-h6">{{ subject.name }}</div>
                  </div>
                </v-col>
                <v-col cols="12" md="6">
                  <div class="mb-4">
                    <div class="text-subtitle-2 text-medium-emphasis mb-1">Отделение</div>
                    <div class="text-h6">{{ subject.department?.name }}</div>
                  </div>
                </v-col>
                <v-col cols="12">
                  <div class="mb-4">
                    <div class="text-subtitle-2 text-medium-emphasis mb-1">Краткое описание</div>
                    <div class="text-body-1">{{ subject.content }}</div>
                  </div>
                </v-col>
                <v-col cols="12">
                  <div class="mb-4">
                    <div class="text-subtitle-2 text-medium-emphasis mb-1">Подробное описание</div>
                    <div class="text-body-1">{{ subject.description }}</div>
                  </div>
                </v-col>
                <v-col cols="12">
                  <div class="mb-4">
                    <div class="text-subtitle-2 text-medium-emphasis mb-1">Статус</div>
                    <v-chip
                      :color="subject.is_active ? 'success' : 'error'"
                      variant="outlined"
                    >
                      {{ subject.is_active ? 'Активный' : 'Неактивный' }}
                    </v-chip>
                  </div>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>

          <!-- Связанные уроки -->
          <v-card v-if="subject.lessons && subject.lessons.length > 0" class="mb-4">
            <v-card-title class="text-h5 pa-6">
              Связанные уроки ({{ subject.lessons.length }})
            </v-card-title>
            <v-card-text class="pa-6">
              <v-list>
                <v-list-item
                  v-for="lesson in subject.lessons"
                  :key="lesson.id"
                  class="border rounded mb-2"
                >
                  <v-list-item-title>{{ lesson.title }}</v-list-item-title>
                  <v-list-item-subtitle>{{ lesson.description }}</v-list-item-subtitle>
                  <template v-slot:append>
                    <v-chip size="small" color="info" variant="outlined">
                      {{ lesson.duration }} мин
                    </v-chip>
                    <v-chip 
                      size="small" 
                      :color="getDifficultyColor(lesson.difficulty)" 
                      variant="outlined"
                      class="ml-2"
                    >
                      {{ getDifficultyText(lesson.difficulty) }}
                    </v-chip>
                  </template>
                </v-list-item>
              </v-list>
            </v-card-text>
          </v-card>

          <!-- Связанные расписания -->
          <v-card v-if="subject.schedules && subject.schedules.length > 0">
            <v-card-title class="text-h5 pa-6">
              Связанные расписания ({{ subject.schedules.length }})
            </v-card-title>
            <v-card-text class="pa-6">
              <v-list>
                <v-list-item
                  v-for="schedule in subject.schedules"
                  :key="schedule.id"
                  class="border rounded mb-2"
                >
                  <v-list-item-title>
                    {{ schedule.group?.name }} - 
                    {{ schedule.teacher?.name }}
                  </v-list-item-title>
                  <v-list-item-subtitle>
                    Семестр {{ schedule.semester }}, {{ schedule.study_year }} год обучения
                  </v-list-item-subtitle>
                </v-list-item>
              </v-list>
            </v-card-text>
          </v-card>
        </v-col>

        <!-- Боковая панель -->
        <v-col cols="12" lg="4">
          <!-- Статистика -->
          <v-card class="mb-4">
            <v-card-title class="text-h6 pa-4">
              <v-icon icon="mdi-chart-bar" class="mr-2"></v-icon>
              Статистика
            </v-card-title>
            <v-card-text class="pa-4">
              <v-list density="compact">
                <v-list-item>
                  <v-list-item-title class="text-body-2">
                    Уроки: {{ subject.lessons?.length || 0 }}
                  </v-list-item-title>
                </v-list-item>
                <v-list-item>
                  <v-list-item-title class="text-body-2">
                    Расписания: {{ subject.schedules?.length || 0 }}
                  </v-list-item-title>
                </v-list-item>
                <v-list-item>
                  <v-list-item-title class="text-body-2">
                    Создан: {{ formatDate(subject.created_at) }}
                  </v-list-item-title>
                </v-list-item>
                <v-list-item>
                  <v-list-item-title class="text-body-2">
                    Обновлен: {{ formatDate(subject.updated_at) }}
                  </v-list-item-title>
                </v-list-item>
              </v-list>
            </v-card-text>
          </v-card>

          <!-- Быстрые действия -->
          <v-card>
            <v-card-title class="text-h6 pa-4">
              <v-icon icon="mdi-lightning-bolt" class="mr-2"></v-icon>
              Быстрые действия
            </v-card-title>
            <v-card-text class="pa-4">
              <v-list density="compact">
                <v-list-item @click="createLesson" class="cursor-pointer">
                  <template v-slot:prepend>
                    <v-icon icon="mdi-plus" color="primary"></v-icon>
                  </template>
                  <v-list-item-title>Создать урок</v-list-item-title>
                </v-list-item>
                <v-list-item @click="createSchedule" class="cursor-pointer">
                  <template v-slot:prepend>
                    <v-icon icon="mdi-calendar-plus" color="primary"></v-icon>
                  </template>
                  <v-list-item-title>Добавить в расписание</v-list-item-title>
                </v-list-item>
                <v-list-item @click="editSubject" class="cursor-pointer">
                  <template v-slot:prepend>
                    <v-icon icon="mdi-pencil" color="warning"></v-icon>
                  </template>
                  <v-list-item-title>Редактировать предмет</v-list-item-title>
                </v-list-item>
              </v-list>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Диалог подтверждения удаления -->
      <v-dialog v-model="deleteDialog" max-width="500px">
        <v-card>
          <v-card-title class="text-h5">Подтверждение удаления</v-card-title>
          <v-card-text>
            Вы уверены, что хотите удалить предмет "{{ subject.name }}"?
            <br><br>
            <v-alert
              v-if="hasRelatedData"
              type="error"
              variant="outlined"
              class="mt-2"
            >
              Невозможно удалить предмет, так как у него есть связанные уроки или расписания!
            </v-alert>
            <v-alert
              v-else
              type="warning"
              variant="outlined"
              class="mt-2"
            >
              Это действие нельзя отменить!
            </v-alert>
          </v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="grey" variant="text" @click="deleteDialog = false">
              Отмена
            </v-btn>
            <v-btn 
              v-if="!hasRelatedData"
              color="error" 
              @click="deleteSubject"
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
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import Layout from '../../Layout.vue'

// Props
const props = defineProps({
  subject: Object
})

// Reactive data
const deleteDialog = ref(false)

// Computed
const hasRelatedData = computed(() => {
  return (props.subject.lessons && props.subject.lessons.length > 0) ||
         (props.subject.schedules && props.subject.schedules.length > 0)
})

// Methods
const goBack = () => {
  router.visit('/admin/subjects')
}

const editSubject = () => {
  router.visit(`/admin/subjects/${props.subject.id}/edit`)
}

const confirmDelete = () => {
  deleteDialog.value = true
}

const deleteSubject = () => {
  router.delete(`/admin/subjects/${props.subject.id}`, {
    onSuccess: () => {
      deleteDialog.value = false
    }
  })
}

const createLesson = () => {
  router.visit(`/admin/lessons/create?subject_id=${props.subject.id}`)
}

const createSchedule = () => {
  router.visit(`/admin/schedules/create?subject_id=${props.subject.id}`)
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('ru-RU', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const getDifficultyColor = (difficulty) => {
  const colors = {
    easy: 'success',
    medium: 'warning',
    hard: 'error'
  }
  return colors[difficulty]
}

const getDifficultyText = (difficulty) => {
  const texts = {
    easy: 'Легкий',
    medium: 'Средний',
    hard: 'Сложный'
  }
  return texts[difficulty] || difficulty
}
</script>

<style scoped>
.cursor-pointer {
  cursor: pointer;
}

.v-card {
  border-radius: 12px;
}
</style>
