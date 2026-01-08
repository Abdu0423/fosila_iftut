<template>
  <Layout role="admin">
    <v-container fluid class="pa-6">
      <!-- Заголовок -->
      <div class="d-flex align-center mb-6">
        <v-btn
          icon="mdi-arrow-left"
          variant="text"
          @click="router.visit('/admin/students/transfers')"
          class="mr-3"
        />
        <div>
          <h1 class="text-h4 font-weight-bold mb-2">
            {{ translations.navigation?.transfers || 'Создание перевода' }}
          </h1>
          <p class="text-body-1 text-medium-emphasis">
            {{ translations.messages?.transfer_description || 'Перевод студента из одной группы в другую' }}
          </p>
        </div>
      </div>

      <!-- Форма создания перевода -->
      <v-row>
        <v-col cols="12" lg="8">
          <v-card>
            <v-card-title class="text-h5 pa-6">
              {{ translations.navigation?.transfers || 'Перевод студента' }}
            </v-card-title>
            <v-card-text class="pa-6">
              <v-form @submit.prevent="submit">
                <v-row>
                  <!-- Студент -->
                  <v-col cols="12" md="6">
                    <v-autocomplete
                      v-model="form.student_id"
                      :items="studentItems"
                      item-title="displayName"
                      item-value="id"
                      :label="(translations.messages?.student || 'Студент') + ' *'"
                      variant="outlined"
                      :error-messages="errors.student_id"
                      prepend-inner-icon="mdi-magnify"
                      clearable
                      auto-select-first
                      required
                      @update:model-value="onStudentSelected"
                    >
                      <template v-slot:item="{ props, item }">
                        <v-list-item v-bind="props">
                          <v-list-item-title>{{ item.raw.displayName }}</v-list-item-title>
                          <v-list-item-subtitle v-if="item.raw.group_name">
                            {{ translations.education_department?.groups_menu || 'Группа' }}: {{ item.raw.group_name }}
                          </v-list-item-subtitle>
                        </v-list-item>
                      </template>
                    </v-autocomplete>
                  </v-col>

                  <!-- Из группы -->
                  <v-col cols="12" md="6">
                    <v-select
                      v-model="form.from_group_id"
                      :items="groups || []"
                      item-title="display_name"
                      item-value="id"
                      :label="(translations.messages?.from_group || 'Из группы') + ' *'"
                      variant="outlined"
                      :error-messages="errors.from_group_id"
                      prepend-inner-icon="mdi-account-group"
                      clearable
                      required
                    />
                  </v-col>

                  <!-- В группу -->
                  <v-col cols="12" md="6">
                    <v-select
                      v-model="form.to_group_id"
                      :items="groups || []"
                      item-title="display_name"
                      item-value="id"
                      :label="(translations.messages?.to_group || 'В группу') + ' *'"
                      variant="outlined"
                      :error-messages="errors.to_group_id"
                      prepend-inner-icon="mdi-account-group"
                      clearable
                      required
                    />
                  </v-col>

                  <!-- Дата перевода -->
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="form.transfer_date"
                      :label="translations.messages?.transfer_date || 'Дата перевода'"
                      type="date"
                      variant="outlined"
                      :error-messages="errors.transfer_date"
                      prepend-inner-icon="mdi-calendar"
                    />
                  </v-col>

                  <!-- Причина -->
                  <v-col cols="12">
                    <v-textarea
                      v-model="form.reason"
                      :label="translations.messages?.reason || 'Причина перевода'"
                      variant="outlined"
                      rows="3"
                      :error-messages="errors.reason"
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
                    {{ translations.messages?.save || 'Сохранить' }}
                  </v-btn>
                  <v-btn
                    variant="outlined"
                    @click="router.visit('/admin/students/transfers')"
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
import { reactive, computed } from 'vue'
import { useForm, router, usePage } from '@inertiajs/vue3'
import Layout from '../../../Layout.vue'

const page = usePage()
const translations = computed(() => page.props.translations || {})

const props = defineProps({
  groups: {
    type: Array,
    default: () => []
  },
  students: {
    type: Array,
    default: () => []
  }
})

const form = useForm({
  student_id: null,
  from_group_id: null,
  to_group_id: null,
  transfer_date: new Date().toISOString().split('T')[0],
  reason: null
})

const errors = computed(() => page.props.errors || {})

// Преобразуем студентов для автокомплита
const studentItems = computed(() => {
  return (props.students || []).map(student => ({
    ...student,
    displayName: `${student.last_name || ''} ${student.name || ''} ${student.middle_name || ''}`.trim()
  }))
})

// Автозаполнение группы при выборе студента
const onStudentSelected = (studentId) => {
  if (studentId) {
    const student = props.students.find(s => s.id === studentId)
    if (student && student.group_id) {
      form.from_group_id = student.group_id
    }
  } else {
    form.from_group_id = null
  }
}

const submit = () => {
  form.post('/admin/students/transfers', {
    preserveScroll: true,
    onSuccess: () => {
      router.visit('/admin/students/transfers')
    }
  })
}
</script>
