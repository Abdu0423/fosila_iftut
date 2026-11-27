<template>
  <Layout role="admin">
    <v-container fluid class="pa-6">
      <!-- Заголовок -->
      <div class="d-flex justify-space-between align-center mb-6">
        <div>
          <h1 class="text-h4 font-weight-bold mb-2">
            {{ translations.navigation?.students_management || 'Управление студентами' }}
          </h1>
          <p class="text-body-1 text-medium-emphasis">
            {{ translations.messages?.management_description || 'Просмотр и управление студентами' }}
          </p>
        </div>
      </div>

      <!-- Быстрое добавление студента -->
      <v-card class="mb-6">
        <v-card-title class="text-h6 pb-2">
          <v-icon start color="primary">mdi-account-plus</v-icon>
          Добавить студента
        </v-card-title>
        <v-card-text>
          <v-alert v-if="successMessage" type="success" variant="tonal" class="mb-4" closable @click:close="successMessage = ''">
            {{ successMessage }}
          </v-alert>
          <v-alert v-if="generalError" type="error" variant="tonal" class="mb-4" closable @click:close="generalError = ''">
            {{ generalError }}
          </v-alert>

          <v-form @submit.prevent="createStudent">
            <v-row dense>
              <v-col cols="12" md="3">
                <v-text-field
                  ref="lastNameInput"
                  v-model="form.last_name"
                  label="Фамилия *"
                  variant="outlined"
                  density="comfortable"
                  :error-messages="errors.last_name"
                  required
                />
              </v-col>
              <v-col cols="12" md="3">
                <v-text-field
                  v-model="form.name"
                  label="Имя *"
                  variant="outlined"
                  density="comfortable"
                  :error-messages="errors.name"
                  required
                />
              </v-col>
              <v-col cols="12" md="3">
                <v-text-field
                  v-model="form.middle_name"
                  label="Отчество"
                  variant="outlined"
                  density="comfortable"
                  :error-messages="errors.middle_name"
                />
              </v-col>
              <v-col cols="12" md="3">
                <v-autocomplete
                  v-model="form.group_id"
                  :label="translations.education_department?.groups_menu || 'Группа'"
                  :items="groups || []"
                  item-title="name"
                  item-value="id"
                  clearable
                  variant="outlined"
                  density="comfortable"
                  :error-messages="errors.group_id"
                />
              </v-col>
            </v-row>

            <v-row dense class="mt-1 align-start">
              <v-col cols="12" md="3">
                <PhoneInput
                  v-model="form.phone"
                  label="Телефон"
                  density="comfortable"
                  :error-messages="errors.phone"
                />
              </v-col>
              <v-col cols="12" md="3">
                <PhoneInput
                  v-model="form.dad_phone"
                  label="Телефон отца"
                  density="comfortable"
                  :error-messages="errors.dad_phone"
                />
              </v-col>
              <v-col cols="12" md="3">
                <PhoneInput
                  v-model="form.mom_phone"
                  label="Телефон матери"
                  density="comfortable"
                  :error-messages="errors.mom_phone"
                />
              </v-col>
              <v-col cols="12" md="3" class="d-flex align-center" style="min-height: 56px;">
                <v-btn
                  type="submit"
                  color="primary"
                  size="large"
                  block
                  prepend-icon="mdi-plus"
                  :loading="submitting"
                  :disabled="submitting"
                >
                  Добавить
                </v-btn>
              </v-col>
            </v-row>
          </v-form>
        </v-card-text>
      </v-card>

      <!-- Поиск и фильтры -->
      <v-card class="mb-6">
        <v-card-text>
          <v-row>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="searchQuery"
                :label="translations.messages?.search || 'Поиск'"
                prepend-inner-icon="mdi-magnify"
                clearable
                variant="outlined"
                density="comfortable"
                @update:model-value="handleSearch"
              ></v-text-field>
            </v-col>
            <v-col cols="12" md="4">
              <v-autocomplete
                v-model="groupFilter"
                :label="translations.education_department?.groups_menu || 'Группа'"
                :items="groups || []"
                item-title="name"
                item-value="id"
                clearable
                variant="outlined"
                density="comfortable"
                @update:model-value="handleSearch"
              ></v-autocomplete>
            </v-col>
          </v-row>
        </v-card-text>
      </v-card>

      <!-- Список студентов -->
      <v-card>
        <v-card-text v-if="visibleStudents.length === 0">
          <div class="text-center py-8">
            <v-icon size="64" color="grey-lighten-1">mdi-school-outline</v-icon>
            <p class="text-h6 text-medium-emphasis mt-4">
              {{ translations.messages?.no_data || 'Студенты не найдены' }}
            </p>
          </div>
        </v-card-text>

        <ExcelSheet :columns="columns" :rows="visibleStudents">
          <template #cell-name="{ row }">
            <div class="d-flex align-center py-2">
              <v-avatar color="primary" size="40" class="mr-3">
                <v-icon color="white">mdi-account</v-icon>
              </v-avatar>
              <div>
                <div class="font-weight-medium">{{ [row.last_name, row.name, row.middle_name].filter(Boolean).join(' ') }}</div>
                <div class="text-caption text-medium-emphasis">{{ row.email || row.phone || '—' }}</div>
              </div>
            </div>
          </template>

          <template #cell-group="{ row }">
            <span v-if="row.group?.name" class="text-body-2">{{ row.group.name }}</span>
            <span v-else class="text-medium-emphasis">—</span>
          </template>

          <template #cell-phone="{ row }">
            <span v-if="row.phone" class="text-body-2">{{ row.phone }}</span>
            <span v-else class="text-medium-emphasis">—</span>
          </template>

          <template #cell-dad_phone="{ row }">
            <span v-if="row.dad_phone" class="text-body-2">{{ row.dad_phone }}</span>
            <span v-else class="text-medium-emphasis">—</span>
          </template>

          <template #cell-mom_phone="{ row }">
            <span v-if="row.mom_phone" class="text-body-2">{{ row.mom_phone }}</span>
            <span v-else class="text-medium-emphasis">—</span>
          </template>

          <template #cell-actions="{ row }">
            <v-btn
              icon
              variant="text"
              color="primary"
              size="small"
              @click="router.visit(`/admin/users/${row.id}/edit`)"
              :title="translations.messages?.edit || 'Редактировать'"
            >
              <v-icon>mdi-pencil</v-icon>
            </v-btn>
          </template>
        </ExcelSheet>

        <!-- Пагинация -->
        <v-divider v-if="students.data.length > 0"></v-divider>
        <div v-if="students.data.length > 0" class="d-flex justify-center pa-4">
          <v-pagination
            :length="totalPages"
            :model-value="students.current_page"
            @update:model-value="handlePageChange"
            :total-visible="Math.min(5, totalPages)"
          ></v-pagination>
        </div>
      </v-card>
    </v-container>
  </Layout>
</template>

<script setup lang="ts">
import ExcelSheet from '@/Components/ExcelSheet.vue'
import PhoneInput from '@/Components/PhoneInput.vue'
import { api, validationErrors } from '@/api/client'
import { forgetPage } from '@/spa/pageCache'
import { ref, reactive, computed, watch, nextTick } from 'vue'
import { usePage, router } from '@/compat/inertia'
import Layout from '../../../Layout.vue'

const LAST_GROUP_STORAGE_KEY = 'admin_students_last_group_id'

const page = usePage()
const translations = computed(() => page.props.translations || {})

const props = defineProps({
  students: {
    type: Object,
    required: true
  },
  groups: {
    type: Array,
    default: () => []
  },
  filters: {
    type: Object,
    default: () => ({})
  }
})

const searchQuery = ref(props.filters.search || '')
const groupFilter = ref(props.filters.group_id ? Number(props.filters.group_id) : null)
const recentStudents = ref<Record<string, any>[]>([])
const submitting = ref(false)
const successMessage = ref('')
const generalError = ref('')
const errors = reactive<Record<string, string | string[]>>({})
const lastNameInput = ref<{ focus?: () => void } | null>(null)

function resolveInitialGroupId(): number | null {
  if (props.filters?.group_id) {
    const id = Number(props.filters.group_id)
    if ((props.groups || []).some((g: any) => Number(g.id) === id)) {
      return id
    }
  }
  try {
    const saved = localStorage.getItem(LAST_GROUP_STORAGE_KEY)
    if (saved) {
      const id = Number(saved)
      if ((props.groups || []).some((g: any) => Number(g.id) === id)) {
        return id
      }
    }
  } catch {
    // Игнорируем недоступность localStorage
  }
  return null
}

const form = reactive({
  last_name: '',
  name: '',
  middle_name: '',
  group_id: resolveInitialGroupId() as number | null,
  phone: '+992',
  dad_phone: '+992',
  mom_phone: '+992',
})

watch(() => form.group_id, (value) => {
  try {
    if (value) {
      localStorage.setItem(LAST_GROUP_STORAGE_KEY, String(value))
    } else {
      localStorage.removeItem(LAST_GROUP_STORAGE_KEY)
    }
  } catch {
    // Игнорируем недоступность localStorage
  }
})

const visibleStudents = computed(() => {
  const existing = (props.students?.data || []) as Record<string, any>[]
  const seen = new Set<number | string>()
  const merged: Record<string, any>[] = []

  for (const item of [...recentStudents.value, ...existing]) {
    if (item?.id != null && seen.has(item.id)) {
      continue
    }
    if (item?.id != null) {
      seen.add(item.id)
    }
    merged.push(item)
  }

  return merged
})

const columns = computed(() => [
  {
    key: 'name',
    title: translations.value.messages?.name || 'Имя',
    editor: 'slot' as const,
    text: (row: Record<string, any>) => [row.last_name, row.name, row.middle_name, row.email, row.phone].filter(Boolean).join(' ')
  },
  {
    key: 'group',
    title: translations.value.education_department?.groups_menu || 'Группа',
    editor: 'slot' as const,
    text: (row: Record<string, any>) => row.group?.name || ''
  },
  {
    key: 'phone',
    title: translations.value.messages?.phone || 'Телефон',
    editor: 'slot' as const,
    text: (row: Record<string, any>) => row.phone || ''
  },
  {
    key: 'dad_phone',
    title: 'Тел. отца',
    editor: 'slot' as const,
    text: (row: Record<string, any>) => row.dad_phone || ''
  },
  {
    key: 'mom_phone',
    title: 'Тел. матери',
    editor: 'slot' as const,
    text: (row: Record<string, any>) => row.mom_phone || ''
  },
  {
    key: 'actions',
    title: translations.value.messages?.actions || 'Действия',
    editor: 'slot' as const,
    text: () => ''
  }
])

function clearErrors() {
  Object.keys(errors).forEach((key) => delete errors[key])
  generalError.value = ''
}

const createStudent = async () => {
  clearErrors()
  successMessage.value = ''

  if (!form.last_name.trim()) {
    errors.last_name = 'Необходимо указать фамилию'
  }
  if (!form.name.trim()) {
    errors.name = 'Необходимо указать имя'
  }
  if (errors.last_name || errors.name) {
    return
  }

  submitting.value = true
  try {
    const response = await api.post('/admin/students', {
      last_name: form.last_name.trim(),
      name: form.name.trim(),
      middle_name: form.middle_name.trim() || null,
      group_id: form.group_id || null,
      phone: form.phone,
      dad_phone: form.dad_phone,
      mom_phone: form.mom_phone,
    })

    const created = response.data?.student
    if (created) {
      recentStudents.value = [created, ...recentStudents.value.filter((item) => item.id !== created.id)]
    }

    forgetPage()
    successMessage.value = response.data?.message || 'Студент успешно добавлен'

    // Сбрасываем данные студента, но сохраняем выбранную группу для следующего ввода
    form.last_name = ''
    form.name = ''
    form.middle_name = ''
    form.phone = '+992'
    form.dad_phone = '+992'
    form.mom_phone = '+992'

    await nextTick()
    lastNameInput.value?.focus?.()
  } catch (error: any) {
    const fieldErrors = validationErrors(error)
    if (Object.keys(fieldErrors).length > 0) {
      Object.assign(errors, fieldErrors)
    } else {
      generalError.value = error?.response?.data?.message || 'Не удалось добавить студента'
    }
  } finally {
    submitting.value = false
  }
}

const handleSearch = () => {
  recentStudents.value = []
  if (groupFilter.value) {
    form.group_id = groupFilter.value
  }
  router.get('/admin/students', {
    search: searchQuery.value,
    group_id: groupFilter.value
  }, {
    preserveState: true,
    preserveScroll: true
  })
}

const handlePageChange = (pageNum: number) => {
  recentStudents.value = []
  router.get('/admin/students', {
    page: pageNum,
    search: searchQuery.value,
    group_id: groupFilter.value
  }, {
    preserveState: true,
    preserveScroll: true
  })
}

const totalPages = computed(() => Math.ceil(props.students.total / props.students.per_page))
</script>
