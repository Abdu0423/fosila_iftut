<template>
  <Layout role="admin">
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center mb-6">
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">Уроки в расписании</h1>
              <p class="text-body-1 text-medium-emphasis">
                {{ schedule?.subject?.name }} - {{ schedule?.group?.name }}
              </p>
            </div>
            <div class="d-flex gap-2">
              <v-btn
                color="secondary"
                variant="outlined"
                @click="goBack"
                prepend-icon="mdi-arrow-left"
              >
                Назад к расписанию
              </v-btn>
              <v-btn
                color="primary"
                @click="showAddLessonDialog = true"
                prepend-icon="mdi-plus"
              >
                Добавить урок
              </v-btn>
            </div>
          </div>
        </v-col>
      </v-row>

      <!-- Информация о расписании -->
      <v-row class="mb-6">
        <v-col cols="12">
          <v-card>
            <v-card-title>
              <v-icon start>mdi-calendar</v-icon>
              Информация о расписании
            </v-card-title>
            <v-card-text>
              <v-row>
                <v-col cols="12" md="3">
                  <div class="text-body-2 text-medium-emphasis mb-1">Предмет</div>
                  <div class="text-body-1 font-weight-medium">{{ schedule?.subject?.name }}</div>
                </v-col>
                <v-col cols="12" md="3">
                  <div class="text-body-2 text-medium-emphasis mb-1">Группа</div>
                  <div class="text-body-1 font-weight-medium">{{ schedule?.group?.name }}</div>
                </v-col>
                <v-col cols="12" md="3">
                  <div class="text-body-2 text-medium-emphasis mb-1">Кафедра</div>
                  <div class="text-body-1 font-weight-medium">{{ schedule?.department?.name || [schedule?.teacher?.name, schedule?.teacher?.last_name].filter(Boolean).join(' ') }}</div>
                </v-col>
                <v-col cols="12" md="3">
                  <div class="text-body-2 text-medium-emphasis mb-1">Семестр</div>
                  <div class="text-body-1 font-weight-medium">{{ schedule?.semester }} семестр</div>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Список уроков -->
      <v-row>
        <v-col cols="12">
          <v-card>
            <v-card-title>
              <v-icon start>mdi-book-education</v-icon>
              Уроки ({{ schedule?.lessons?.length || 0 }})
            </v-card-title>
            <v-card-text>
              <div v-if="!schedule?.lessons?.length" class="text-center py-8">
                <v-icon size="64" color="grey-lighten-2">mdi-book-outline</v-icon>
                <div class="text-h6 mt-4 text-medium-emphasis">Уроки не добавлены</div>
                <div class="text-body-2 text-medium-emphasis">Нажмите "Добавить урок" для начала</div>
              </div>

              <v-list v-else>
                <draggable 
                  v-model="sortedLessons" 
                  @end="onSortEnd"
                  item-key="id"
                  ghost-class="ghost"
                  handle=".drag-handle"
                >
                  <template #item="{ element: lesson, index }">
                    <v-list-item :key="lesson.id" class="lesson-item">
                      <template v-slot:prepend>
                        <v-icon class="drag-handle mr-2" color="grey">mdi-drag</v-icon>
                        <v-chip 
                          size="small" 
                          color="primary" 
                          variant="outlined"
                          class="mr-2"
                        >
                          {{ lesson.pivot?.order || index + 1 }}
                        </v-chip>
                      </template>

                      <v-list-item-title>{{ lesson.title }}</v-list-item-title>
                      <v-list-item-subtitle>
                        <div class="d-flex flex-wrap gap-2 mt-1">
                          <v-chip size="x-small" color="info" variant="tonal">
                            {{ lesson.pivot?.duration || lesson.duration || 90 }} мин
                          </v-chip>
                          <v-chip v-if="lesson.pivot?.start_time" size="x-small" color="success" variant="tonal">
                            {{ lesson.pivot.start_time }}
                          </v-chip>
                          <v-chip v-if="lesson.pivot?.room" size="x-small" color="warning" variant="tonal">
                            {{ lesson.pivot.room }}
                          </v-chip>
                        </div>
                        <div v-if="lesson.description" class="mt-1 text-body-2">
                          {{ lesson.description }}
                        </div>
                      </v-list-item-subtitle>

                      <template v-slot:append>
                        <div class="d-flex gap-1">
                          <v-btn
                            icon="mdi-pencil"
                            size="small"
                            variant="text"
                            color="primary"
                            @click="editLesson(lesson)"
                          />
                          <v-btn
                            icon="mdi-delete"
                            size="small"
                            variant="text"
                            color="error"
                            @click="removeLesson(lesson)"
                          />
                        </div>
                      </template>
                    </v-list-item>
                    <v-divider v-if="index < sortedLessons.length - 1" />
                  </template>
                </draggable>
              </v-list>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Диалог добавления урока -->
      <v-dialog v-model="showAddLessonDialog" max-width="600">
        <v-card>
          <v-card-title>Добавить урок в расписание</v-card-title>
          <v-card-text>
            <v-form @submit.prevent="addLesson">
              <v-row>
                <v-col cols="12">
                  <v-select
                    v-model="newLesson.lesson_id"
                    :items="availableLessons"
                    item-title="title"
                    item-value="id"
                    label="Урок"
                    variant="outlined"
                    required
                  >
                    <template v-slot:item="{ props, item }">
                      <v-list-item v-bind="props">
                        <v-list-item-title>{{ item.raw.title }}</v-list-item-title>
                        <v-list-item-subtitle>
                          {{ item.raw.subject?.name }} | {{ item.raw.duration || 90 }} мин
                        </v-list-item-subtitle>
                      </v-list-item>
                    </template>
                  </v-select>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model.number="newLesson.duration"
                    label="Длительность (мин)"
                    type="number"
                    variant="outlined"
                    min="15"
                    max="240"
                  />
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="newLesson.start_time"
                    label="Время начала"
                    type="time"
                    variant="outlined"
                  />
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="newLesson.room"
                    label="Аудитория"
                    variant="outlined"
                  />
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model.number="newLesson.order"
                    label="Порядок"
                    type="number"
                    variant="outlined"
                    min="1"
                  />
                </v-col>
                <v-col cols="12">
                  <v-textarea
                    v-model="newLesson.notes"
                    label="Заметки"
                    variant="outlined"
                    rows="2"
                  />
                </v-col>
              </v-row>
            </v-form>
          </v-card-text>
          <v-card-actions>
            <v-spacer />
            <v-btn
              variant="text"
              @click="showAddLessonDialog = false"
            >
              Отмена
            </v-btn>
            <v-btn
              color="primary"
              variant="flat"
              @click="addLesson"
              :loading="adding"
            >
              Добавить
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </v-container>
  </Layout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { router } from '@/compat/inertia'
import Layout from '../../Layout.vue'
import draggable from 'vuedraggable'

// Props
const props = defineProps({
  schedule: {
    type: Object,
    required: true
  },
  lessons: {
    type: Array,
    default: () => []
  }
})

// Состояние
const showAddLessonDialog = ref(false)
const adding = ref(false)
const sortedLessons = ref([...(props.schedule?.lessons || [])].sort((a, b) => (a.pivot?.order || 0) - (b.pivot?.order || 0)))

// Новый урок
const newLesson = ref({
  lesson_id: null,
  duration: 90,
  start_time: '',
  room: '',
  order: 1,
  notes: ''
})

// Вычисляемые свойства
const availableLessons = computed(() => {
  const assignedLessonIds = props.schedule?.lessons?.map(l => l.id) || []
  return props.lessons.filter(lesson => !assignedLessonIds.includes(lesson.id))
})

// Методы
const goBack = () => {
  router.visit(`/admin/schedules/${props.schedule.id}`)
}

const addLesson = async () => {
  if (!newLesson.value.lesson_id) return

  adding.value = true
  
  try {
    const response = await fetch(`/admin/schedules/${props.schedule.id}/lessons`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify({
        lesson_id: newLesson.value.lesson_id,
        duration: newLesson.value.duration,
        start_time: newLesson.value.start_time,
        room: newLesson.value.room,
        order: newLesson.value.order,
        notes: newLesson.value.notes
      })
    })

    if (response.ok) {
      router.reload()
      showAddLessonDialog.value = false
      resetNewLesson()
    }
  } catch (error) {
    console.error('Ошибка при добавлении урока:', error)
  } finally {
    adding.value = false
  }
}

const editLesson = (lesson) => {
  // TODO: Реализовать редактирование урока
  console.log('Редактирование урока:', lesson)
}

const removeLesson = async (lesson) => {
  if (!confirm('Удалить урок из расписания?')) return

  try {
    const response = await fetch(`/admin/schedules/${props.schedule.id}/lessons/${lesson.id}`, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      }
    })

    if (response.ok) {
      router.reload()
    }
  } catch (error) {
    console.error('Ошибка при удалении урока:', error)
  }
}

const onSortEnd = async () => {
  // Обновляем порядок уроков на сервере
  const updates = sortedLessons.value.map((lesson, index) => ({
    lesson_id: lesson.id,
    order: index + 1
  }))

  try {
    const response = await fetch(`/admin/schedules/${props.schedule.id}/lessons/reorder`, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify({ lessons: updates })
    })

    if (!response.ok) {
      // Если ошибка, перезагружаем страницу
      router.reload()
    }
  } catch (error) {
    console.error('Ошибка при обновлении порядка:', error)
    router.reload()
  }
}

const resetNewLesson = () => {
  newLesson.value = {
    lesson_id: null,
    duration: 90,
    start_time: '',
    room: '',
    order: (props.schedule?.lessons?.length || 0) + 1,
    notes: ''
  }
}

// Watcher для отслеживания изменений в schedule.lessons
watch(() => props.schedule?.lessons, (newLessons) => {
  sortedLessons.value = [...(newLessons || [])]
    .sort((a, b) => (a.pivot?.order || 0) - (b.pivot?.order || 0))
}, { immediate: true })

// Инициализация
onMounted(() => {
  resetNewLesson()
})
</script>

<style scoped>
.lesson-item {
  border: 1px solid transparent;
  transition: all 0.2s ease;
}

.lesson-item:hover {
  background-color: rgba(0, 0, 0, 0.04);
  border-color: rgba(0, 0, 0, 0.12);
}

.drag-handle {
  cursor: grab;
}

.drag-handle:active {
  cursor: grabbing;
}

.ghost {
  opacity: 0.5;
  background: #f0f0f0;
}

.v-card {
  border-radius: 12px;
}
</style>
