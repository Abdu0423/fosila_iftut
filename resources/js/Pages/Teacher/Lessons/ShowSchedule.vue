<template>
  <Layout role="teacher">
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center mb-6">
            <div>
              <v-btn
                color="secondary"
                variant="text"
                @click="goBack"
                prepend-icon="mdi-arrow-left"
                class="mb-2"
              >
                Назад к расписаниям
              </v-btn>
              <h1 class="text-h4 font-weight-bold mb-2">
                {{ schedule.subject?.name || 'Расписание' }}
              </h1>
              <p class="text-body-1 text-medium-emphasis">
                Группа: {{ schedule.group?.name || 'Не указана' }} | 
                Уроков: {{ lessons.length }}
              </p>
            </div>
          </div>
        </v-col>
      </v-row>

      <!-- Уведомления -->
      <v-row v-if="page.props.flash?.success">
        <v-col cols="12">
          <v-alert
            type="success"
            variant="tonal"
            closable
          >
            {{ page.props.flash.success }}
          </v-alert>
        </v-col>
      </v-row>

      <!-- Список уроков -->
      <v-row>
        <v-col cols="12">
          <v-card>
            <v-card-title class="d-flex justify-space-between align-center">
              <div class="d-flex align-center">
                <v-icon start>mdi-teach</v-icon>
                Уроки расписания ({{ lessons.length }})
              </div>
              <v-btn
                color="primary"
                @click="createLesson"
                prepend-icon="mdi-plus"
              >
                Добавить урок
              </v-btn>
            </v-card-title>
            <v-card-text>
              <div v-if="lessons.length === 0" class="text-center py-8">
                <v-icon size="64" color="grey-lighten-1" class="mb-4">mdi-teach-outline</v-icon>
                <h3 class="text-h6 text-grey">Нет уроков</h3>
                <p class="text-body-2 text-grey">Добавьте уроки к этому расписанию</p>
              </div>
              
              <div v-else>
                <v-list>
                  <v-list-item
                    v-for="lesson in lessons"
                    :key="lesson.id"
                    class="mb-2"
                  >
                    <template v-slot:prepend>
                      <v-avatar color="info" size="40">
                        <v-icon color="white">mdi-teach</v-icon>
                      </v-avatar>
                    </template>
                    
                    <v-list-item-title class="font-weight-medium mb-2">
                      {{ lesson.title }}
                    </v-list-item-title>
                    
                    <v-list-item-subtitle>
                      <div v-if="lesson.description" class="mb-2">
                        {{ lesson.description }}
                      </div>
                      <div class="d-flex align-center gap-4 mb-2">
                        <v-chip
                          v-if="lesson.subject"
                          color="primary"
                          size="small"
                          variant="tonal"
                        >
                          {{ lesson.subject.name }}
                        </v-chip>
                        <span v-if="lesson.pivot?.order" class="text-caption">
                          <v-icon size="14">mdi-order-numeric-ascending</v-icon>
                          Порядок: {{ lesson.pivot.order }}
                        </span>
                        <span v-if="lesson.pivot?.start_time && lesson.pivot?.end_time" class="text-caption">
                          <v-icon size="14">mdi-clock</v-icon>
                          {{ lesson.pivot.start_time }} - {{ lesson.pivot.end_time }}
                        </span>
                        <span v-if="lesson.pivot?.room" class="text-caption">
                          <v-icon size="14">mdi-map-marker</v-icon>
                          {{ lesson.pivot.room }}
                        </span>
                      </div>
                      <div v-if="lesson.file_name" class="text-caption text-medium-emphasis">
                        <v-icon size="14">mdi-file</v-icon>
                        {{ lesson.file_name }}
                      </div>
                    </v-list-item-subtitle>
                    
                    <template v-slot:append>
                      <div class="d-flex gap-2">
                        <v-btn
                          color="secondary"
                          variant="text"
                          size="small"
                          @click="editLesson(lesson)"
                          prepend-icon="mdi-pencil"
                        >
                          Редактировать
                        </v-btn>
                        <v-btn
                          color="error"
                          variant="text"
                          size="small"
                          @click="removeLesson(lesson)"
                          prepend-icon="mdi-delete"
                        >
                          Удалить
                        </v-btn>
                      </div>
                    </template>
                  </v-list-item>
                </v-list>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

    </v-container>
  </Layout>
</template>

<script setup>
import { usePage, router } from '@inertiajs/vue3'
import Layout from '../../Layout.vue'

const page = usePage()

// Props
const props = defineProps({
  schedule: {
    type: Object,
    required: true
  },
  lessons: {
    type: Array,
    default: () => []
  },
  availableLessons: {
    type: Array,
    default: () => []
  }
})

// Методы
const goBack = () => {
  router.visit('/teacher/lessons')
}

const createLesson = () => {
  // Переход на страницу создания урока с параметром schedule_id
  router.visit(`/teacher/lessons/create?schedule_id=${props.schedule.id}`)
}

const editLesson = (lesson) => {
  router.visit(`/teacher/lessons/${lesson.id}/edit`)
}

const removeLesson = (lesson) => {
  if (!confirm(`Вы уверены, что хотите удалить урок "${lesson.title}" из расписания?`)) {
    return
  }

  router.delete(`/teacher/schedule/${props.schedule.id}/lessons/${lesson.id}`, {
    onSuccess: () => {
      router.reload()
    },
    onError: (errors) => {
      console.error('Ошибка при удалении урока:', errors)
    }
  })
}
</script>

<style scoped>
.v-card {
  border-radius: 12px;
}
</style>

