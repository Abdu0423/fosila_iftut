<template>
  <Layout role="education_department">
    <v-container fluid class="pa-6">
      <!-- Заголовок -->
      <div class="d-flex align-center mb-6">
        <v-btn
          icon="mdi-arrow-left"
          variant="text"
          @click="router.visit(route('education.schedules.index'))"
          class="mr-3"
        />
        <div>
          <h1 class="text-h4 font-weight-bold mb-2">
            {{ translations.education_department?.create_schedule }}
          </h1>
          <p class="text-body-1 text-medium-emphasis">
            {{ translations.education_department?.schedules_subtitle }}
          </p>
        </div>
      </div>

      <!-- Форма создания -->
      <v-row>
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
                    <v-select
                      :model-value="form.subject_id"
                      @update:model-value="form.subject_id = $event"
                      :items="subjects || []"
                      item-title="name"
                      item-value="id"
                      :label="(translations.education_department?.schedule_subject || 'Предмет') + ' *'"
                      variant="outlined"
                      :error-messages="errors.subject_id"
                      required
                    />
                  </v-col>

                  <!-- Преподаватель -->
                  <v-col cols="12" md="6">
                    <v-select
                      :model-value="form.teacher_id"
                      @update:model-value="form.teacher_id = $event"
                      :items="teachers || []"
                      item-title="name"
                      item-value="id"
                      :label="(translations.education_department?.schedule_teacher || 'Преподаватель') + ' *'"
                      variant="outlined"
                      :error-messages="errors.teacher_id"
                      required
                    />
                  </v-col>

                  <!-- Группа -->
                  <v-col cols="12" md="6">
                    <v-select
                      :model-value="form.group_id"
                      @update:model-value="form.group_id = $event"
                      :items="groups || []"
                      item-title="name"
                      item-value="id"
                      :label="(translations.education_department?.schedule_group || 'Группа') + ' *'"
                      variant="outlined"
                      :error-messages="errors.group_id"
                      required
                    />
                  </v-col>

                  <!-- Семестр -->
                  <v-col cols="12" md="6">
                    <v-select
                      :model-value="form.semester"
                      @update:model-value="form.semester = $event"
                      :items="semesterItems"
                      :label="(translations.education_department?.schedule_semester || 'Семестр') + ' *'"
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
                      :label="(translations.education_department?.subject_credits || 'Кредиты') + ' *'"
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
                      :label="(translations.education_department?.schedule_study_year || 'Учебный год') + ' *'"
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
                      :label="(translations.messages?.order || 'Порядок') + ' *'"
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
                      :label="translations.education_department?.schedule_date || 'Дата'"
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
                    {{ translations.messages?.save }}
                  </v-btn>
                  <v-btn
                    variant="outlined"
                    @click="router.visit(route('education.schedules.index'))"
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
import { reactive, computed } from 'vue'
import { useForm, router, usePage } from '@inertiajs/vue3'
import Layout from '../../Layout.vue'

const page = usePage()
const translations = computed(() => page.props.translations || {})

const props = defineProps({
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
  subject_id: null,
  teacher_id: null,
  group_id: null,
  semester: 1,
  credits: null,
  study_year: new Date().getFullYear(),
  order: 1,
  scheduled_at: null,
  is_active: true
})

const errors = computed(() => page.props.errors || {})

const semesterItems = [
  { title: '1', value: 1 },
  { title: '2', value: 2 }
]

const submit = () => {
  form.post(route('education.schedules.store'), {
    preserveScroll: true,
    onSuccess: () => {
      router.visit(route('education.schedules.index'))
    }
  })
}
</script>

