<template>
  <Layout role="education_department">
    <v-container fluid class="pa-6">
      <!-- Заголовок -->
      <div class="d-flex align-center mb-6">
        <v-btn
          icon="mdi-arrow-left"
          variant="text"
          @click="router.visit(route('education.subjects.index'))"
          class="mr-3"
        />
        <div>
          <h1 class="text-h4 font-weight-bold mb-2">
            {{ translations.education_department?.create_subject }}
          </h1>
          <p class="text-body-1 text-medium-emphasis">
            {{ translations.education_department?.subjects_subtitle }}
          </p>
        </div>
      </div>

      <!-- Форма создания -->
      <v-row>
        <v-col cols="12" lg="8">
          <v-card>
            <v-card-title class="text-h5 pa-6">
              {{ translations.education_department?.subjects_title }}
            </v-card-title>
            <v-card-text class="pa-6">
              <v-form @submit.prevent="submit">
                <v-row>
                  <!-- Название предмета -->
                  <v-col cols="12" md="8">
                    <v-text-field
                      :model-value="form.name"
                      @update:model-value="form.name = $event"
                      :label="(translations.education_department?.subject_name) + ' *'"
                      variant="outlined"
                      :error-messages="errors.name"
                      required
                    />
                  </v-col>

                  <!-- Код предмета -->
                  <v-col cols="12" md="4">
                    <v-text-field
                      :model-value="form.code"
                      @update:model-value="form.code = $event"
                      :label="translations.education_department?.subject_code"
                      variant="outlined"
                      :error-messages="errors.code"
                    />
                  </v-col>

                  <!-- Отделение -->
                  <v-col cols="12" md="6">
                    <v-select
                      :model-value="form.department_id"
                      @update:model-value="form.department_id = $event"
                      :label="translations.messages?.category"
                      :items="departments || []"
                      item-title="name"
                      item-value="id"
                      variant="outlined"
                      clearable
                      :error-messages="errors.department_id"
                    />
                  </v-col>

                  <!-- Количество кредитов -->
                  <v-col cols="12" md="6">
                    <v-text-field
                      :model-value="form.credits"
                      @update:model-value="form.credits = $event ? parseInt($event) : null"
                      :label="translations.education_department?.subject_credits"
                      type="number"
                      min="1"
                      max="10"
                      variant="outlined"
                      :error-messages="errors.credits"
                    />
                  </v-col>

                  <!-- Описание -->
                  <v-col cols="12">
                    <v-textarea
                      :model-value="form.description"
                      @update:model-value="form.description = $event"
                      :label="translations.education_department?.subject_description"
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
                      :label="translations.education_department?.subject_active"
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
                    @click="router.visit(route('education.subjects.index'))"
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
  departments: {
    type: Array,
    default: () => []
  }
})

const form = useForm({
  name: '',
  code: '',
  department_id: null,
  description: '',
  credits: null,
  is_active: true
})

const errors = computed(() => page.props.errors || {})

const submit = () => {
  form.post(route('education.subjects.store'), {
    preserveScroll: true,
    onSuccess: () => {
      router.visit(route('education.subjects.index'))
    }
  })
}
</script>

