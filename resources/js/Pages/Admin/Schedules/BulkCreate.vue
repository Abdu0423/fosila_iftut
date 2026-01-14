<template>
  <AdminApp>
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center mb-6">
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">Массовое создание расписания</h1>
              <p class="text-body-1 text-medium-emphasis">Создание нескольких записей расписания одновременно</p>
            </div>
            <v-btn
              color="secondary"
              variant="outlined"
              @click="navigateTo('/admin/schedules')"
              prepend-icon="mdi-arrow-left"
            >
              Назад к списку
            </v-btn>
          </div>
        </v-col>
      </v-row>

      <!-- Форма массового создания -->
      <v-row>
        <v-col cols="12">
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-calendar-plus</v-icon>
              Создание расписания
            </v-card-title>
            <v-card-text>
              <v-form @submit.prevent="submitForm">
                <!-- Шаблон для новой записи -->
                <div class="mb-6">
                  <div class="d-flex justify-space-between align-center mb-4">
                    <h3 class="text-h6">Записи расписания</h3>
                    <v-btn
                      color="primary"
                      variant="outlined"
                      @click="addSchedule"
                      prepend-icon="mdi-plus"
                    >
                      Добавить запись
                    </v-btn>
                  </div>

                  <!-- Список записей -->
                  <div v-for="(schedule, index) in schedules" :key="index" class="mb-4">
                    <v-card variant="outlined" class="pa-4">
                      <div class="d-flex justify-space-between align-center mb-4">
                        <h4 class="text-subtitle-1">Запись #{{ index + 1 }}</h4>
                        <v-btn
                          icon="mdi-delete"
                          color="error"
                          variant="text"
                          size="small"
                          @click="removeSchedule(index)"
                          :disabled="schedules.length === 1"
                        ></v-btn>
                      </div>

                      <v-row>
                        <!-- Урок -->
                        <v-col cols="12" md="6">
                          <v-autocomplete
                            v-model="schedule.lesson_id"
                            :items="lessons"
                            item-title="title"
                            item-value="id"
                            label="Урок *"
                            variant="outlined"
                            density="compact"
                            prepend-inner-icon="mdi-magnify"
                            clearable
                            auto-select-first
                            required
                          >
                            <template v-slot:item="{ props, item }">
                              <v-list-item v-bind="props">
                                <v-list-item-title>{{ item.raw.title }}</v-list-item-title>
                                <v-list-item-subtitle v-if="item.raw.subject">
                                  Предмет: {{ item.raw.subject.name }}
                                </v-list-item-subtitle>
                              </v-list-item>
                            </template>
                          </v-autocomplete>
                        </v-col>

                        <!-- Преподаватель -->
                        <v-col cols="12" md="6">
                          <v-autocomplete
                            v-model="schedule.teacher_id"
                            :items="teacherItems"
                            item-title="displayName"
                            item-value="id"
                            label="Преподаватель *"
                            variant="outlined"
                            density="compact"
                            prepend-inner-icon="mdi-magnify"
                            clearable
                            auto-select-first
                            required
                          >
                            <template v-slot:item="{ props, item }">
                              <v-list-item v-bind="props">
                                <v-list-item-title>{{ item.raw.name }} {{ item.raw.last_name }}{{ item.raw.middle_name ? ' ' + item.raw.middle_name : '' }}</v-list-item-title>
                                <v-list-item-subtitle v-if="item.raw.email">{{ item.raw.email }}</v-list-item-subtitle>
                              </v-list-item>
                            </template>
                          </v-autocomplete>
                        </v-col>

                        <!-- Группа -->
                        <v-col cols="12" md="6">
                          <v-autocomplete
                            v-model="schedule.group_id"
                            :items="groups"
                            item-title="name"
                            item-value="id"
                            label="Группа *"
                            variant="outlined"
                            density="compact"
                            prepend-inner-icon="mdi-magnify"
                            clearable
                            auto-select-first
                            required
                          ></v-autocomplete>
                        </v-col>

                        <!-- Семестр -->
                        <v-col cols="12" md="6">
                          <v-select
                            v-model="schedule.semester"
                            :items="semesterOptions"
                            label="Семестр *"
                            variant="outlined"
                            density="compact"
                            required
                          ></v-select>
                        </v-col>

                        <!-- Кредиты -->
                        <v-col cols="12" md="4">
                          <v-text-field
                            v-model.number="schedule.credits"
                            label="Количество кредитов *"
                            type="number"
                            min="1"
                            max="10"
                            variant="outlined"
                            density="compact"
                            required
                          ></v-text-field>
                        </v-col>

                        <!-- Год обучения -->
                        <v-col cols="12" md="4">
                          <v-text-field
                            v-model.number="schedule.study_year"
                            label="Год обучения *"
                            type="number"
                            min="2020"
                            max="2030"
                            variant="outlined"
                            density="compact"
                            required
                          ></v-text-field>
                        </v-col>

                        <!-- Порядок -->
                        <v-col cols="12" md="4">
                          <v-text-field
                            v-model.number="schedule.order"
                            label="Порядок урока *"
                            type="number"
                            min="1"
                            variant="outlined"
                            density="compact"
                            required
                          ></v-text-field>
                        </v-col>

                        <!-- Запланированная дата -->
                        <v-col cols="12" md="6">
                          <v-text-field
                            v-model="schedule.scheduled_at"
                            label="Запланированная дата и время"
                            type="datetime-local"
                            variant="outlined"
                            density="compact"
                          ></v-text-field>
                        </v-col>

                        <!-- Статус активности -->
                        <v-col cols="12" md="6">
                          <v-switch
                            v-model="schedule.is_active"
                            label="Активно"
                            color="success"
                            density="compact"
                          ></v-switch>
                        </v-col>
                      </v-row>
                    </v-card>
                  </div>
                </div>

                <!-- Кнопки действий -->
                <div class="d-flex gap-3">
                  <v-btn
                    type="submit"
                    color="primary"
                    size="large"
                    :loading="form.processing"
                    :disabled="schedules.length === 0"
                    prepend-icon="mdi-content-save"
                  >
                    Создать {{ schedules.length }} записей
                  </v-btn>
                  
                  <v-btn
                    color="secondary"
                    variant="outlined"
                    size="large"
                    @click="resetForm"
                    prepend-icon="mdi-refresh"
                  >
                    Сбросить
                  </v-btn>
                  
                  <v-btn
                    color="secondary"
                    variant="outlined"
                    size="large"
                    @click="navigateTo('/admin/schedules')"
                    prepend-icon="mdi-close"
                  >
                    Отмена
                  </v-btn>
                </div>
              </v-form>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Информационная панель -->
      <v-row class="mt-6">
        <v-col cols="12" md="6">
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-information</v-icon>
              Информация
            </v-card-title>
            <v-card-text>
              <div class="text-body-2 mb-4">
                <strong>Количество записей:</strong> {{ schedules.length }}
              </div>
              
              <div class="text-body-2 mb-4">
                <strong>Уроки:</strong> {{ lessons.length }} доступно
              </div>
              
              <div class="text-body-2 mb-4">
                <strong>Преподаватели:</strong> {{ teachers.length }} доступно
              </div>
              
              <div class="text-body-2">
                <strong>Группы:</strong> {{ groups.length }} доступно
              </div>
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12" md="6">
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-lightbulb</v-icon>
              Советы
            </v-card-title>
            <v-card-text>
              <div class="text-body-2 mb-4">
                • Используйте кнопку "Добавить запись" для создания нескольких расписаний
              </div>
              
              <div class="text-body-2 mb-4">
                • Все обязательные поля отмечены звездочкой (*)
              </div>
              
              <div class="text-body-2 mb-4">
                • Можно удалить ненужные записи кнопкой удаления
              </div>
              
              <div class="text-body-2">
                • Все записи будут созданы одновременно при нажатии "Создать"
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </AdminApp>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import AdminApp from '../AdminApp.vue'

// Props из Inertia
const props = defineProps({
  lessons: {
    type: Array,
    default: () => []
  },
  groups: {
    type: Array,
    default: () => []
  },
  teachers: {
    type: Array,
    default: () => []
  }
})

// Данные
const lessons = ref(props.lessons)
const groups = ref(props.groups)
const teachers = ref(props.teachers)

// Преобразуем преподавателей для автокомплита
const teacherItems = computed(() => {
  return (teachers.value || []).map(teacher => ({
    ...teacher,
    displayName: `${teacher.name || ''} ${teacher.last_name || ''}${teacher.middle_name ? ' ' + teacher.middle_name : ''}`.trim()
  }))
})

const semesterOptions = ref([
  { value: 1, text: '1 семестр' },
  { value: 2, text: '2 семестр' }
])

// Состояние
const schedules = ref([createEmptySchedule()])

// Форма
const form = useForm({
  schedules: []
})

// Методы
const navigateTo = (path) => {
  router.visit(path)
}

const createEmptySchedule = () => ({
  lesson_id: '',
  teacher_id: '',
  group_id: '',
  semester: 1,
  credits: 3,
  study_year: new Date().getFullYear(),
  order: 1,
  scheduled_at: '',
  is_active: true
})

const addSchedule = () => {
  schedules.value.push(createEmptySchedule())
}

const removeSchedule = (index) => {
  if (schedules.value.length > 1) {
    schedules.value.splice(index, 1)
  }
}

const submitForm = () => {
  // Подготавливаем данные для отправки
  form.schedules = schedules.value.map(schedule => ({
    ...schedule,
    is_active: schedule.is_active || false
  }))

  form.post('/admin/schedules/bulk-store', {
    onSuccess: () => {
      console.log('Расписание успешно создано')
    },
    onError: (errors) => {
      console.error('Ошибки валидации:', errors)
    }
  })
}

const resetForm = () => {
  schedules.value = [createEmptySchedule()]
  form.reset()
}
</script>

<style scoped>
.v-card {
  border-radius: 12px;
}
</style>
