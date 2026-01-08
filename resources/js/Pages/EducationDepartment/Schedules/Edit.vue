<template>
  <Layout :role="getRole">
    <v-container fluid class="pa-6">
      <!-- Заголовок -->
      <div class="d-flex align-center mb-6">
        <v-btn
          icon="mdi-arrow-left"
          variant="text"
          @click="router.visit(`/${getRoutePrefix()}/schedules`)"
          class="mr-3"
        />
        <div>
          <h1 class="text-h4 font-weight-bold mb-2">
            {{ translations.education_department?.edit_schedule }}
          </h1>
          <p class="text-body-1 text-medium-emphasis">
            {{ schedule?.subject?.name }}
          </p>
        </div>
      </div>

      <!-- Форма редактирования -->
      <v-row v-if="schedule?.id">
        <v-col cols="12" lg="8">
          <v-card>
            <v-card-title class="text-h5 pa-6">
              {{ translations.education_department?.schedules_title }}
            </v-card-title>
            <v-card-text class="pa-6">
              <v-form @submit.prevent="submit">
                <v-row>
                  <!-- Предмет -->
                  <v-col cols="12" md="6">
                    <v-autocomplete
                      v-model="form.subject_id"
                      :items="subjects || []"
                      item-title="name"
                      item-value="id"
                      :label="(translations.education_department?.schedule_subject) + ' *'"
                      variant="outlined"
                      :error-messages="errors.subject_id"
                      prepend-inner-icon="mdi-magnify"
                      clearable
                      auto-select-first
                      required
                    />
                  </v-col>

                  <!-- Преподаватель -->
                  <v-col cols="12" md="6">
                    <v-autocomplete
                      v-model="form.teacher_id"
                      :items="teacherItems"
                      item-title="displayName"
                      item-value="id"
                      :label="(translations.education_department?.schedule_teacher) + ' *'"
                      variant="outlined"
                      :error-messages="errors.teacher_id"
                      prepend-inner-icon="mdi-magnify"
                      clearable
                      auto-select-first
                      required
                    >
                      <template v-slot:item="{ props, item }">
                        <v-list-item v-bind="props">
                          <v-list-item-title>{{ item.raw.name }} {{ item.raw.last_name }}</v-list-item-title>
                          <v-list-item-subtitle v-if="item.raw.email">{{ item.raw.email }}</v-list-item-subtitle>
                        </v-list-item>
                      </template>
                    </v-autocomplete>
                  </v-col>

                  <!-- Группа -->
                  <v-col cols="12" md="6">
                    <v-autocomplete
                      v-model="form.group_id"
                      :items="groupItems"
                      item-title="displayName"
                      item-value="id"
                      :label="(translations.education_department?.schedule_group) + ' *'"
                      variant="outlined"
                      :error-messages="errors.group_id"
                      prepend-inner-icon="mdi-magnify"
                      clearable
                      auto-select-first
                      required
                    >
                      <template v-slot:item="{ props, item }">
                        <v-list-item v-bind="props">
                          <v-list-item-title>{{ item.raw.name }}</v-list-item-title>
                          <v-list-item-subtitle v-if="item.raw.full_name && item.raw.full_name !== item.raw.name">
                            {{ item.raw.full_name }}
                          </v-list-item-subtitle>
                        </v-list-item>
                      </template>
                    </v-autocomplete>
                  </v-col>

                  <!-- Семестр -->
                  <v-col cols="12" md="6">
                    <v-select
                      :model-value="form.semester"
                      @update:model-value="form.semester = $event"
                      :items="semesterItems"
                      :label="(translations.education_department?.schedule_semester) + ' *'"
                      variant="outlined"
                      :error-messages="errors.semester"
                      required
                    />
                  </v-col>

                  <!-- Кредиты -->
                  <v-col cols="12" md="6">
                    <v-text-field
                      :model-value="form.credits"
                      @update:model-value="form.credits = $event ? parseInt($event) : null"
                      :label="(translations.education_department?.subject_credits) + ' *'"
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
                      :model-value="form.study_year"
                      @update:model-value="form.study_year = $event ? parseInt($event) : null"
                      :label="(translations.education_department?.schedule_study_year) + ' *'"
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
                      :model-value="form.order"
                      @update:model-value="form.order = $event ? parseInt($event) : null"
                      :label="translations.messages?.order + ' *'"
                      type="number"
                      min="1"
                      variant="outlined"
                      :error-messages="errors.order"
                      required
                    />
                  </v-col>

                  <!-- Дата и время -->
                  <v-col cols="12" md="6">
                    <v-text-field
                      :model-value="form.scheduled_at"
                      @update:model-value="form.scheduled_at = $event"
                      :label="translations.education_department?.schedule_date"
                      type="datetime-local"
                      variant="outlined"
                      :error-messages="errors.scheduled_at"
                    />
                  </v-col>

                  <!-- Активен -->
                  <v-col cols="12">
                    <v-switch
                      :model-value="form.is_active"
                      @update:model-value="form.is_active = $event"
                      :label="translations.messages?.active"
                      color="primary"
                    />
                  </v-col>
                </v-row>

                <v-divider class="my-4" />

                <div class="d-flex gap-3">
                  <v-btn
                    type="submit"
                    color="primary"
                    :loading="form.processing"
                    :disabled="form.processing"
                  >
                    {{ translations.messages?.update }}
                  </v-btn>
                  <v-btn
                    variant="outlined"
                    @click="router.visit(`/${getRoutePrefix()}/schedules`)"
                    :disabled="form.processing"
                  >
                    {{ translations.messages?.cancel }}
                  </v-btn>
                </div>
              </v-form>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </Layout>
</template>

<script setup>
import { computed } from 'vue'
import { useForm, router, usePage } from '@inertiajs/vue3'
import Layout from '../../Layout.vue'

const page = usePage()
const translations = computed(() => page.props.translations || {})

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
  teachers: {
    type: Array,
    default: () => []
  }
})

const form = useForm({
  subject_id: props.schedule?.subject_id || null,
  teacher_id: props.schedule?.teacher_id || null,
  group_id: props.schedule?.group_id || null,
  semester: props.schedule?.semester || 1,
  credits: props.schedule?.credits || null,
  study_year: props.schedule?.study_year || new Date().getFullYear(),
  order: props.schedule?.order || 1,
  scheduled_at: props.schedule?.scheduled_at ? new Date(props.schedule.scheduled_at).toISOString().slice(0, 16) : null,
  is_active: props.schedule?.is_active !== undefined ? props.schedule.is_active : true
})

const errors = computed(() => page.props.errors || {})

const semesterItems = [
  { title: '1', value: 1 },
  { title: '2', value: 2 },
  { title: '3', value: 3 },
  { title: '4', value: 4 },
  { title: '5', value: 5 },
  { title: '6', value: 6 },
  { title: '7', value: 7 },
  { title: '8', value: 8 },
  { title: '9', value: 9 },
  { title: '10', value: 10 }
]

// Преобразуем преподавателей для автокомплита
const teacherItems = computed(() => {
  return (props.teachers || []).map(teacher => ({
    ...teacher,
    displayName: `${teacher.name} ${teacher.last_name}${teacher.middle_name ? ' ' + teacher.middle_name : ''}`
  }))
})

// Преобразуем группы для автокомплита
const groupItems = computed(() => {
  return (props.groups || []).map(group => ({
    ...group,
    displayName: group.full_name || group.name
  }))
})

// Определяем префикс маршрута на основе текущего URL
const getRoutePrefix = () => {
  const path = window.location.pathname
  if (path.startsWith('/registration')) {
    return 'registration'
  }
  return 'education'
}

// Определяем роль для Layout
const getRole = computed(() => {
  return getRoutePrefix() === 'registration' ? 'registration_center' : 'education_department'
})

const submit = () => {
  const routePrefix = getRoutePrefix()
  form.put(`/${routePrefix}/schedules/${props.schedule.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      router.visit(`/${routePrefix}/schedules`)
    }
  })
}
</script>

