<template>
  <Layout role="education_department">
    <v-container fluid class="pa-6">
      <!-- Заголовок -->
      <div class="d-flex align-center mb-6">
        <v-btn
          icon="mdi-arrow-left"
          variant="text"
          @click="router.visit(`/${getRoutePrefix()}/subjects`)"
          class="mr-3"
        />
        <div>
          <h1 class="text-h4 font-weight-bold mb-2">
            {{ translations.education_department?.edit_subject || 'Редактирование предмета' }}
          </h1>
          <p class="text-body-1 text-medium-emphasis">
            {{ subject?.name }}
          </p>
        </div>
      </div>

      <!-- Форма редактирования -->
      <v-row v-if="subject?.id">
        <v-col cols="12" lg="8">
          <v-card>
            <v-card-title class="text-h5 pa-6">
              {{ translations.education_department?.subjects_title || 'Предметы' }}
            </v-card-title>
            <v-card-text class="pa-6">
              <v-form @submit.prevent="submit">
                <v-row>
                  <!-- Название предмета -->
                  <v-col cols="12" md="8">
                    <v-text-field
                      :model-value="form.name"
                      @update:model-value="form.name = $event"
                      :label="(translations.education_department?.subject_name || 'Название предмета') + ' *'"
                      variant="outlined"
                      :error-messages="errors.name"
                      required
                    />
                  </v-col>

                  <!-- Отделение -->
                  <v-col cols="12" md="4">
                    <v-select
                      :model-value="form.department_id"
                      @update:model-value="form.department_id = $event"
                      :label="translations.messages?.category || 'Кафедра'"
                      :items="departments || []"
                      item-title="name"
                      item-value="id"
                      variant="outlined"
                      clearable
                      :error-messages="errors.department_id"
                    />
                  </v-col>

                  <!-- Описание -->
                  <v-col cols="12">
                    <v-textarea
                      :model-value="form.description"
                      @update:model-value="form.description = $event"
                      :label="translations.education_department?.subject_description || 'Описание'"
                      variant="outlined"
                      rows="3"
                      :error-messages="errors.description"
                    />
                  </v-col>

                  <!-- Активен -->
                  <v-col cols="12">
                    <v-switch
                      :model-value="form.is_active"
                      @update:model-value="form.is_active = $event"
                      :label="translations.education_department?.subject_active || 'Активен'"
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
                    {{ translations.messages?.update || 'Обновить' }}
                  </v-btn>
                  <v-btn
                    variant="outlined"
                    @click="router.visit(`/${getRoutePrefix()}/subjects`)"
                    :disabled="form.processing"
                  >
                    {{ translations.messages?.cancel || 'Отмена' }}
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
  subject: {
    type: Object,
    required: true
  },
  departments: {
    type: Array,
    default: () => []
  }
})

const form = useForm({
  name: props.subject?.name,
  department_id: props.subject?.department_id || null,
  description: props.subject?.description,
  is_active: props.subject?.is_active !== undefined ? props.subject.is_active : true
})

const errors = computed(() => page.props.errors || {})

const submit = () => {
  if (!props.subject?.id) {
    return
  }
// Определяем префикс маршрута на основе текущего URL
const getRoutePrefix = () => {
  const path = window.location.pathname
  if (path.startsWith('/registration')) {
    return 'registration'
  }
  return 'education'
}

  const routePrefix = getRoutePrefix()
  form.transform((data) => ({
    ...data,
    _method: 'PUT'
  })).post(`/${routePrefix}/subjects/${props.subject.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      router.visit(`/${routePrefix}/subjects`)
    }
  })
}
</script>

