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
                {{ t('teacher.back_to_schedules', {}, { default: 'Назад к расписаниям' }) }}
              </v-btn>
              <h1 class="text-h4 font-weight-bold mb-2">
                {{ schedule.subject?.name }}
              </h1>
              <p class="text-body-1 text-medium-emphasis">
                {{ t('teacher.group', {}, { default: 'Группа' }) }}: {{ schedule.group?.name }} | 
                {{ t('teacher.lessons_count', {}, { default: 'Уроков' }) }}: {{ lessons.length }}
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
                {{ t('teacher.schedule_lessons', {}, { default: 'Уроки расписания' }) }} ({{ lessons.length }})
              </div>
              <v-btn
                color="primary"
                @click="createLesson"
                prepend-icon="mdi-plus"
              >
                {{ t('teacher.add_lesson', {}, { default: 'Добавить урок' }) }}
              </v-btn>
            </v-card-title>
            <v-card-text>
              <div v-if="lessons.length === 0" class="text-center py-8">
                <v-icon size="64" color="grey-lighten-1" class="mb-4">mdi-teach-outline</v-icon>
                <h3 class="text-h6 text-grey">{{ t('teacher.no_lessons', {}, { default: 'Нет уроков' }) }}</h3>
                <p class="text-body-2 text-grey">{{ t('teacher.add_lessons_to_schedule', {}, { default: 'Добавьте уроки к этому расписанию' }) }}</p>
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
                          {{ t('teacher.order', {}, { default: 'Порядок' }) }}: {{ lesson.pivot.order }}
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
                          {{ t('common.edit', {}, { default: 'Редактировать' }) }}
                        </v-btn>
                        <v-btn
                          color="error"
                          variant="text"
                          size="small"
                          @click="removeLesson(lesson)"
                          prepend-icon="mdi-delete"
                        >
                          {{ t('common.delete', {}, { default: 'Удалить' }) }}
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
import { useI18n } from 'vue-i18n'
import Layout from '../../Layout.vue'

const page = usePage()
const { t } = useI18n()

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
  const confirmMsg = t('teacher.confirm_delete_lesson', { title: lesson.title }, { default: `Вы уверены, что хотите удалить урок "${lesson.title}" из расписания?` })
  if (!confirm(confirmMsg)) {
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

