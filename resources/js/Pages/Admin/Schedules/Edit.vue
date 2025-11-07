<template>
  <Layout role="admin">
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center mb-6">
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">Редактирование расписания</h1>
              <p class="text-body-1 text-medium-emphasis">{{ schedule?.subject?.name || 'Загрузка...' }}</p>
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

      <!-- Форма редактирования -->
      <v-row v-if="schedule?.id">
        <v-col cols="12" lg="8">
          <v-card>
            <v-card-title class="text-h5 pa-6">
              Информация о расписании
            </v-card-title>
            <v-card-text class="pa-6">
              <v-form @submit.prevent="submit">
                <v-row>
                  <!-- Предмет -->
                  <v-col cols="12" md="6">
                    <v-select
                      v-model="form.subject_id"
                      :items="subjects || []"
                      item-title="name"
                      item-value="id"
                      label="Предмет *"
                      variant="outlined"
                      :error-messages="errors.subject_id"
                      required
                    >
                      <template v-slot:item="{ props, item }">
                        <v-list-item v-bind="props">
                          <v-list-item-title>{{ item.raw.name }}</v-list-item-title>
                          <v-list-item-subtitle>{{ item.raw.code }}</v-list-item-subtitle>
                        </v-list-item>
                      </template>
                    </v-select>
                  </v-col>

                  <!-- Преподаватель -->
                  <v-col cols="12" md="6">
                    <v-select
                      v-model="form.teacher_id"
                      :items="teachers || []"
                      item-title="name"
                      item-value="id"
                      label="Преподаватель *"
                      variant="outlined"
                      :error-messages="errors.teacher_id"
                      required
                    >
                      <template v-slot:item="{ props, item }">
                        <v-list-item v-bind="props">
                          <v-list-item-title>{{ item.raw.name }}</v-list-item-title>
                          <v-list-item-subtitle>{{ item.raw.email }}</v-list-item-subtitle>
                        </v-list-item>
                      </template>
                    </v-select>
                  </v-col>

                  <!-- Группа -->
                  <v-col cols="12" md="6">
                    <v-select
                      v-model="form.group_id"
                      :items="groups || []"
                      item-title="name"
                      item-value="id"
                      label="Группа *"
                      variant="outlined"
                      :error-messages="errors.group_id"
                      required
                    />
                  </v-col>

                  <!-- Семестр -->
                  <v-col cols="12" md="6">
                    <v-select
                      v-model="form.semester"
                      :items="semesterItems"
                      label="Семестр *"
                      variant="outlined"
                      :error-messages="errors.semester"
                      required
                    />
                  </v-col>

                  <!-- Кредиты -->
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model.number="form.credits"
                      label="Количество кредитов *"
                      type="number"
                      min="1"
                      max="10"
                      variant="outlined"
                      :error-messages="errors.credits"
                      required
                    />
                  </v-col>

                  <!-- Год обучения -->
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model.number="form.study_year"
                      label="Год обучения *"
                      type="number"
                      min="2020"
                      max="2030"
                      variant="outlined"
                      :error-messages="errors.study_year"
                      required
                    />
                  </v-col>

                  <!-- Порядок -->
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model.number="form.order"
                      label="Порядковый номер *"
                      type="number"
                      min="1"
                      variant="outlined"
                      :error-messages="errors.order"
                      hint="Порядок проведения занятия в расписании"
                      required
                    />
                  </v-col>

                  <!-- Дата и время -->
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="form.scheduled_at"
                      label="Дата и время"
                      type="datetime-local"
                      variant="outlined"
                      :error-messages="errors.scheduled_at"
                      hint="Оставьте пустым, если дата не определена"
                    />
                  </v-col>

                  <!-- Статус активности -->
                  <v-col cols="12">
                    <v-switch
                      v-model="form.is_active"
                      label="Активное расписание"
                      color="primary"
                      :error-messages="errors.is_active"
                      hide-details="auto"
                    />
                  </v-col>

                  <!-- Силлабусы -->
                  <v-col cols="12">
                    <v-select
                      v-model="form.syllabus_ids"
                      :items="syllabuses || []"
                      item-title="name"
                      item-value="id"
                      label="Силлабусы"
                      variant="outlined"
                      multiple
                      chips
                      :error-messages="errors.syllabus_ids"
                      hint="Выберите силлабусы для данного расписания"
                      persistent-hint
                    >
                      <template v-slot:chip="{ props, item }">
                        <v-chip
                          v-bind="props"
                          :text="item.raw.name"
                          size="small"
                          closable
                        />
                      </template>
                      <template v-slot:item="{ props, item }">
                        <v-list-item v-bind="props">
                          <v-list-item-title>{{ item.raw.name }}</v-list-item-title>
                          <v-list-item-subtitle v-if="item.raw.lesson">
                            Урок: {{ item.raw.lesson.name }} | Год: {{ item.raw.creation_year }}
                          </v-list-item-subtitle>
                        </v-list-item>
                      </template>
                    </v-select>
                  </v-col>

                  <!-- Уроки -->
                  <v-col cols="12">
                    <v-select
                      v-model="form.lesson_ids"
                      :items="lessons || []"
                      item-title="title"
                      item-value="id"
                      label="Уроки"
                      variant="outlined"
                      multiple
                      chips
                      :error-messages="errors.lesson_ids"
                      hint="Выберите уроки для данного расписания"
                      persistent-hint
                    >
                      <template v-slot:chip="{ props, item }">
                        <v-chip
                          v-bind="props"
                          :text="item.raw.title"
                          size="small"
                          closable
                        />
                      </template>
                      <template v-slot:item="{ props, item }">
                        <v-list-item v-bind="props">
                          <v-list-item-title>{{ item.raw.title }}</v-list-item-title>
                          <v-list-item-subtitle v-if="item.raw.subject">
                            Предмет: {{ item.raw.subject.name }} | Длительность: {{ item.raw.duration || 90 }} мин
                          </v-list-item-subtitle>
                        </v-list-item>
                      </template>
                    </v-select>
                  </v-col>
                </v-row>

                <!-- Даты экзаменов -->
                <v-row>
                  <v-col cols="12">
                    <v-divider class="my-4"></v-divider>
                    <h3 class="text-h6 mb-4">
                      <v-icon start>mdi-calendar-clock</v-icon>
                      Даты и время экзаменов
                    </h3>
                  </v-col>

                  <!-- Периодический экзамен 1 -->
                  <v-col cols="12" md="4" v-if="exams?.periodic_exam_1">
                    <v-text-field
                      v-model="form.periodic_exam_1_date"
                      label="Периодический экзамен 1"
                      type="datetime-local"
                      variant="outlined"
                      :error-messages="errors.periodic_exam_1_date"
                      :hint="exams.periodic_exam_1.title"
                      persistent-hint
                    >
                      <template v-slot:prepend-inner>
                        <v-icon>mdi-calendar</v-icon>
                      </template>
                    </v-text-field>
                  </v-col>

                  <!-- Периодический экзамен 2 -->
                  <v-col cols="12" md="4" v-if="exams?.periodic_exam_2">
                    <v-text-field
                      v-model="form.periodic_exam_2_date"
                      label="Периодический экзамен 2"
                      type="datetime-local"
                      variant="outlined"
                      :error-messages="errors.periodic_exam_2_date"
                      :hint="exams.periodic_exam_2.title"
                      persistent-hint
                    >
                      <template v-slot:prepend-inner>
                        <v-icon>mdi-calendar</v-icon>
                      </template>
                    </v-text-field>
                  </v-col>

                  <!-- Итоговый экзамен -->
                  <v-col cols="12" md="4" v-if="exams?.final_exam">
                    <v-text-field
                      v-model="form.final_exam_date"
                      label="Итоговый экзамен"
                      type="datetime-local"
                      variant="outlined"
                      :error-messages="errors.final_exam_date"
                      :hint="exams.final_exam.title"
                      persistent-hint
                    >
                      <template v-slot:prepend-inner>
                        <v-icon>mdi-calendar</v-icon>
                      </template>
                    </v-text-field>
                  </v-col>

                  <v-col cols="12" v-if="!exams?.periodic_exam_1 && !exams?.periodic_exam_2 && !exams?.final_exam">
                    <v-alert type="info" variant="tonal">
                      Экзамены не созданы для этого расписания. Используйте команду <code>php artisan exams:create {schedule_id}</code> для создания экзаменов.
                    </v-alert>
                  </v-col>
                </v-row>

                <div class="d-flex gap-3 mt-6">
                  <v-btn
                    @click="goBack"
                    variant="outlined"
                  >
                    Отмена
                  </v-btn>
                  <v-btn
                    type="submit"
                    color="primary"
                    :loading="processing"
                    :disabled="processing || !schedule?.id"
                  >
                    Сохранить изменения
                  </v-btn>
                </div>
              </v-form>
            </v-card-text>
          </v-card>
        </v-col>

        <!-- Боковая панель со статистикой -->
        <v-col cols="12" lg="4">
          <v-card>
            <v-card-title class="text-h6 pa-4">
              <v-icon icon="mdi-chart-bar" class="mr-2"></v-icon>
              Статистика
            </v-card-title>
            <v-card-text class="pa-4">
              <v-list density="compact">
                <v-list-item>
                  <v-list-item-title class="text-body-2">
                    Создано: {{ schedule?.created_at ? formatDate(schedule.created_at) : 'Н/Д' }}
                  </v-list-item-title>
                </v-list-item>
                <v-list-item>
                  <v-list-item-title class="text-body-2">
                    Обновлено: {{ schedule?.updated_at ? formatDate(schedule.updated_at) : 'Н/Д' }}
                  </v-list-item-title>
                </v-list-item>
              </v-list>
            </v-card-text>
          </v-card>

          <v-card class="mt-4">
            <v-card-title class="text-h6 pa-4">
              <v-icon icon="mdi-information" class="mr-2"></v-icon>
              Справка
            </v-card-title>
            <v-card-text class="pa-4">
              <div class="text-body-2">
                <p class="mb-2"><strong>Предмет:</strong> Выберите предмет из списка</p>
                <p class="mb-2"><strong>Преподаватель:</strong> Назначенный преподаватель</p>
                <p class="mb-2"><strong>Группа:</strong> Группа студентов</p>
                <p class="mb-2"><strong>Семестр:</strong> 1 или 2 семестр</p>
                <p class="mb-2"><strong>Кредиты:</strong> От 1 до 10 кредитов</p>
              </div>
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
                Если загрузка занимает слишком много времени, проверьте консоль браузера для отладочной информации.
              </p>
              <v-btn @click="goBack" class="mt-4">
                Вернуться к списку
              </v-btn>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </Layout>
</template>

<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'
import Layout from '../../Layout.vue'

// Props
const props = defineProps({
  schedule: {
    type: Object,
    default: () => ({})
  },
  subjects: {
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
  },
  syllabuses: {
    type: Array,
    default: () => []
  },
  lessons: {
    type: Array,
    default: () => []
  },
  exams: {
    type: Object,
    default: () => ({
      periodic_exam_1: null,
      periodic_exam_2: null,
      final_exam: null
    })
  },
  errors: {
    type: Object,
    default: () => ({})
  }
})

// Функция для форматирования даты для input datetime-local
const formatDateTimeForInput = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toISOString().slice(0, 16) // Format: YYYY-MM-DDTHH:mm
}

// Form data
const form = useForm({
  subject_id: props.schedule?.subject_id || null,
  teacher_id: props.schedule?.teacher_id || null,
  group_id: props.schedule?.group_id || null,
  semester: props.schedule?.semester || null,
  credits: props.schedule?.credits || 3,
  study_year: props.schedule?.study_year || new Date().getFullYear(),
  order: props.schedule?.order || 1,
  scheduled_at: props.schedule?.scheduled_at ? formatDateTimeForInput(props.schedule.scheduled_at) : '',
  is_active: props.schedule?.is_active ?? true,
  syllabus_ids: props.schedule?.syllabuses?.map(s => s.id) || [],
  lesson_ids: props.schedule?.lessons?.map(l => l.id) || [],
  periodic_exam_1_date: props.exams?.periodic_exam_1?.exam_date ? formatDateTimeForInput(props.exams.periodic_exam_1.exam_date) : '',
  periodic_exam_2_date: props.exams?.periodic_exam_2?.exam_date ? formatDateTimeForInput(props.exams.periodic_exam_2.exam_date) : '',
  final_exam_date: props.exams?.final_exam?.exam_date ? formatDateTimeForInput(props.exams.final_exam.exam_date) : ''
})

const processing = ref(false)

// Варианты семестров
const semesterItems = [
  { title: '1 семестр', value: 1 },
  { title: '2 семестр', value: 2 }
]

// Methods
const submit = () => {
  if (!props.schedule?.id) {
    console.error('Cannot submit - schedule ID not available')
    return
  }
  
  processing.value = true
  
  form.patch(`/admin/schedules/${props.schedule.id}`, {
    onSuccess: (page) => {
      processing.value = false
      // Программный редирект на список расписаний
      router.visit('/admin/schedules', { 
        method: 'get',
        data: {},
        preserveState: false,
        preserveScroll: false
      })
    },
    onError: (errors) => {
      console.error('Update failed:', errors)
      processing.value = false
    },
    onFinish: () => {
      processing.value = false
    }
  })
}

const goBack = () => {
  router.visit('/admin/schedules')
}

const formatDate = (date) => {
  if (!date) return 'Н/Д'
  return new Date(date).toLocaleDateString('ru-RU', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

</script>

<style scoped>
.v-card {
  border-radius: 12px;
}
</style>