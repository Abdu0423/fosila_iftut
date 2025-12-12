<template>
  <Layout role="teacher">
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center mb-6">
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">Расписание</h1>
              <p class="text-body-1 text-medium-emphasis">
                {{ schedule.subject?.name }} - {{ schedule.group?.name }}
              </p>
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

      <!-- Основная информация о расписании -->
      <v-row class="mb-6">
        <v-col cols="12" md="8">
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-information</v-icon>
              Информация о расписании
            </v-card-title>
            <v-card-text>
              <v-form @submit.prevent="updateSchedule">
                <v-row>
                  <v-col cols="12" md="6">
                    <v-select
                      v-model="editForm.subject_id"
                      :items="subjects"
                      item-title="name"
                      item-value="id"
                      label="Предмет"
                      variant="outlined"
                      density="compact"
                      required
                    ></v-select>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-select
                      v-model="editForm.group_id"
                      :items="groups"
                      item-title="name"
                      item-value="id"
                      label="Группа"
                      variant="outlined"
                      density="compact"
                      required
                    ></v-select>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="editForm.start_date"
                      label="Дата начала"
                      type="date"
                      variant="outlined"
                      density="compact"
                      required
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="editForm.end_date"
                      label="Дата окончания"
                      type="date"
                      variant="outlined"
                      density="compact"
                      required
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12">
                    <v-textarea
                      v-model="editForm.description"
                      label="Описание"
                      variant="outlined"
                      density="compact"
                      rows="3"
                    ></v-textarea>
                  </v-col>
                </v-row>
                <div class="d-flex justify-end gap-2 mt-4">
                  <v-btn
                    color="secondary"
                    @click="resetForm"
                  >
                    Отмена
                  </v-btn>
                  <v-btn
                    color="primary"
                    type="submit"
                    :loading="editForm.processing"
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
              <v-icon start>mdi-chart-box</v-icon>
              Статистика
            </v-card-title>
            <v-card-text>
              <div class="text-center mb-4">
                <v-icon size="48" color="success" class="mb-2">mdi-file-document-multiple</v-icon>
                <div class="text-h4 font-weight-bold">{{ schedule.syllabuses?.length || 0 }}</div>
                <div class="text-body-2 text-medium-emphasis">Силлабусов</div>
              </div>
              <div class="text-center">
                <v-icon size="48" color="info" class="mb-2">mdi-teach</v-icon>
                <div class="text-h4 font-weight-bold">{{ schedule.lessons?.length || 0 }}</div>
                <div class="text-body-2 text-medium-emphasis">Уроков</div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Управление силлабусами -->
      <v-row class="mb-6">
        <v-col cols="12">
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-file-document-multiple</v-icon>
              Силлабусы
            </v-card-title>
            <v-card-text>
              <!-- Добавление силлабусов -->
              <div class="mb-4">
                <v-select
                  v-model="selectedSyllabuses"
                  :items="availableSyllabuses"
                  item-title="name"
                  item-value="id"
                  label="Выберите силлабусы для добавления"
                  variant="outlined"
                  density="compact"
                  multiple
                  chips
                >
                  <template v-slot:item="{ props, item }">
                    <v-list-item v-bind="props">
                      <v-list-item-title>{{ item.raw.name }}</v-list-item-title>
                      <v-list-item-subtitle>{{ item.raw.subject?.name }}</v-list-item-subtitle>
                    </v-list-item>
                  </template>
                </v-select>
                <v-btn
                  color="primary"
                  @click="addSyllabuses"
                  :disabled="selectedSyllabuses.length === 0"
                  class="mt-2"
                >
                  Добавить выбранные
                </v-btn>
              </div>

              <!-- Текущие силлабусы -->
              <div v-if="schedule.syllabuses?.length > 0">
                <h3 class="text-h6 mb-3">Текущие силлабусы</h3>
                <v-list>
                  <v-list-item
                    v-for="syllabus in schedule.syllabuses"
                    :key="syllabus.id"
                  >
                    <template v-slot:prepend>
                      <v-avatar color="success" size="32">
                        <v-icon color="white" size="16">mdi-file-document</v-icon>
                      </v-avatar>
                    </template>
                    <v-list-item-title>{{ syllabus.name }}</v-list-item-title>
                    <v-list-item-subtitle>{{ syllabus.subject?.name }}</v-list-item-subtitle>
                    <template v-slot:append>
                      <v-btn
                        icon="mdi-delete"
                        variant="text"
                        color="error"
                        size="small"
                        @click="removeSyllabus(syllabus)"
                      ></v-btn>
                    </template>
                  </v-list-item>
                </v-list>
              </div>
              <div v-else class="text-center py-4">
                <v-icon size="48" color="grey-lighten-1" class="mb-2">mdi-file-document-outline</v-icon>
                <p class="text-body-2 text-grey">Нет добавленных силлабусов</p>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Управление уроками -->
      <v-row>
        <v-col cols="12">
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-teach</v-icon>
              Уроки
            </v-card-title>
            <v-card-text>
              <!-- Добавление уроков -->
              <div class="mb-4">
                <v-select
                  v-model="selectedLessons"
                  :items="availableLessons"
                  item-title="title"
                  item-value="id"
                  label="Выберите уроки для добавления"
                  variant="outlined"
                  density="compact"
                  multiple
                  chips
                >
                  <template v-slot:item="{ props, item }">
                    <v-list-item v-bind="props">
                      <v-list-item-title>{{ item.raw.title }}</v-list-item-title>
                      <v-list-item-subtitle>{{ item.raw.subject?.name }}</v-list-item-subtitle>
                    </v-list-item>
                  </template>
                </v-select>
                <v-btn
                  color="primary"
                  @click="addLessons"
                  :disabled="selectedLessons.length === 0"
                  class="mt-2"
                >
                  Добавить выбранные
                </v-btn>
              </div>

              <!-- Текущие уроки -->
              <div v-if="schedule.lessons?.length > 0">
                <h3 class="text-h6 mb-3">Текущие уроки</h3>
                <v-list>
                  <v-list-item
                    v-for="lesson in schedule.lessons"
                    :key="lesson.id"
                  >
                    <template v-slot:prepend>
                      <v-avatar color="info" size="32">
                        <v-icon color="white" size="16">mdi-teach</v-icon>
                      </v-avatar>
                    </template>
                    <v-list-item-title>{{ lesson.title }}</v-list-item-title>
                    <v-list-item-subtitle>{{ lesson.subject?.name }}</v-list-item-subtitle>
                    <template v-slot:append>
                      <v-btn
                        icon="mdi-delete"
                        variant="text"
                        color="error"
                        size="small"
                        @click="removeLesson(lesson)"
                      ></v-btn>
                    </template>
                  </v-list-item>
                </v-list>
              </div>
              <div v-else class="text-center py-4">
                <v-icon size="48" color="grey-lighten-1" class="mb-2">mdi-teach-outline</v-icon>
                <p class="text-body-2 text-grey">Нет добавленных уроков</p>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </Layout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useForm, usePage, router } from '@inertiajs/vue3'
import Layout from '../../Layout.vue'

const page = usePage()

// Props
const props = defineProps({
  schedule: {
    type: Object,
    required: true
  },
  subjects: {
    type: Array,
    default: () => []
  },
  groups: {
    type: Array,
    default: () => []
  },
  availableSyllabuses: {
    type: Array,
    default: () => []
  },
  availableLessons: {
    type: Array,
    default: () => []
  }
})

// Состояние
const selectedSyllabuses = ref([])
const selectedLessons = ref([])

// Форма для редактирования расписания
const editForm = useForm({
  subject_id: props.schedule.subject_id,
  group_id: props.schedule.group_id,
  start_date: props.schedule.start_date,
  end_date: props.schedule.end_date,
  description: props.schedule.description
})

// Методы
const goBack = () => {
  router.visit('/teacher/schedule')
}

const resetForm = () => {
  editForm.reset()
  editForm.subject_id = props.schedule.subject_id
  editForm.group_id = props.schedule.group_id
  editForm.start_date = props.schedule.start_date
  editForm.end_date = props.schedule.end_date
  editForm.description = props.schedule.description
}

const updateSchedule = () => {
  editForm.put(`/teacher/schedule/${props.schedule.id}`, {
    onSuccess: () => {
      console.log('Расписание обновлено')
    }
  })
}

const addSyllabuses = () => {
  if (selectedSyllabuses.value.length === 0) return

  const form = useForm({
    syllabus_ids: selectedSyllabuses.value
  })

  form.post(`/teacher/schedule/${props.schedule.id}/syllabuses`, {
    onSuccess: () => {
      selectedSyllabuses.value = []
      // Обновляем страницу для получения новых данных
      router.reload()
    }
  })
}

const removeSyllabus = (syllabus) => {
  if (confirm('Вы уверены, что хотите удалить этот силлабус из расписания?')) {
    router.delete(`/teacher/schedule/${props.schedule.id}/syllabuses/${syllabus.id}`, {
      onSuccess: () => {
        router.reload()
      }
    })
  }
}

const addLessons = () => {
  if (selectedLessons.value.length === 0) return

  const form = useForm({
    lesson_ids: selectedLessons.value
  })

  form.post(`/teacher/schedule/${props.schedule.id}/lessons`, {
    onSuccess: () => {
      selectedLessons.value = []
      router.reload()
    }
  })
}

const removeLesson = (lesson) => {
  if (confirm('Вы уверены, что хотите удалить этот урок из расписания?')) {
    router.delete(`/teacher/schedule/${props.schedule.id}/lessons/${lesson.id}`, {
      onSuccess: () => {
        router.reload()
      }
    })
  }
}

// Жизненный цикл
onMounted(() => {
  console.log('Страница расписания загружена:', props.schedule.id)
})
</script>

<style scoped>
.v-card {
  border-radius: 12px;
}
</style>
