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

      <v-alert v-if="successMessage" type="success" variant="tonal" class="mb-4" closable @click:close="successMessage = ''">
        {{ successMessage }}
      </v-alert>
      <v-alert v-if="generalError" type="error" variant="tonal" class="mb-4" closable @click:close="generalError = ''">
        {{ generalError }}
      </v-alert>

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

      <!-- Список студентов и быстрое добавление прямо в таблице -->
      <v-card>
        <ExcelSheet
          ref="sheetRef"
          :columns="columns"
          :rows="visibleStudents"
          can-add
          add-top
          add-label="Добавить"
          empty-text="Студенты не найдены"
          @create="createStudent"
        >
          <template #cell-last_name="{ row }">
            <span class="font-weight-medium">{{ row.last_name || '—' }}</span>
          </template>

          <template #cell-name="{ row }">
            <span>{{ row.name || '—' }}</span>
          </template>

          <template #cell-middle_name="{ row }">
            <span v-if="row.middle_name">{{ row.middle_name }}</span>
            <span v-else class="text-medium-emphasis">—</span>
          </template>

          <template #cell-group_id="{ row }">
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
import { api, validationErrors } from '@/api/client'
import { forgetPage } from '@/spa/pageCache'
import { ref, computed, onMounted, nextTick } from 'vue'
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

type SheetHandle = {
  restoreDraft: () => void
  setDraftValues: (values: Record<string, unknown>) => void
  focusFirstAddInput: () => void
}

const sheetRef = ref<SheetHandle | null>(null)
const searchQuery = ref(props.filters.search || '')
const groupFilter = ref(props.filters.group_id ? Number(props.filters.group_id) : null)
const recentStudents = ref<Record<string, any>[]>([])
const submitting = ref(false)
const successMessage = ref('')
const generalError = ref('')

function saveLastGroupId(groupId: number | null) {
  try {
    if (groupId) {
      localStorage.setItem(LAST_GROUP_STORAGE_KEY, String(groupId))
    } else {
      localStorage.removeItem(LAST_GROUP_STORAGE_KEY)
    }
  } catch {
    // Игнорируем недоступность localStorage
  }
}

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

onMounted(() => {
  const initialGroupId = resolveInitialGroupId()
  if (initialGroupId) {
    sheetRef.value?.setDraftValues({ group_id: initialGroupId })
  }
})

const groupOptions = computed(() =>
  (props.groups || []).map((g: any) => ({
    title: g.name,
    value: g.id,
  }))
)

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
    key: 'last_name',
    title: 'Фамилия',
    placeholder: 'Фамилия *',
    width: '150px',
    editor: 'text' as const,
    required: true,
    get: (row: Record<string, any>) => row.last_name || '',
    text: (row: Record<string, any>) => row.last_name || '',
  },
  {
    key: 'name',
    title: translations.value.messages?.name || 'Имя',
    placeholder: 'Имя *',
    width: '140px',
    editor: 'text' as const,
    required: true,
    get: (row: Record<string, any>) => row.name || '',
    text: (row: Record<string, any>) => row.name || '',
  },
  {
    key: 'middle_name',
    title: 'Отчество',
    placeholder: 'Отчество',
    width: '150px',
    editor: 'text' as const,
    get: (row: Record<string, any>) => row.middle_name || '',
    text: (row: Record<string, any>) => row.middle_name || '',
  },
  {
    key: 'group_id',
    title: translations.value.education_department?.groups_menu || 'Группа',
    placeholder: 'Группа',
    width: '210px',
    editor: 'select' as const,
    options: groupOptions.value,
    preserveOnAdd: true,
    get: (row: Record<string, any>) => row.group_id ?? null,
    text: (row: Record<string, any>) => row.group?.name || '',
  },
  {
    key: 'phone',
    title: translations.value.messages?.phone || 'Телефон',
    placeholder: '+992...',
    width: '150px',
    editor: 'text' as const,
    get: (row: Record<string, any>) => row.phone || '',
    text: (row: Record<string, any>) => row.phone || '',
  },
  {
    key: 'dad_phone',
    title: 'Тел. отца',
    placeholder: '+992...',
    width: '150px',
    editor: 'text' as const,
    get: (row: Record<string, any>) => row.dad_phone || '',
    text: (row: Record<string, any>) => row.dad_phone || '',
  },
  {
    key: 'mom_phone',
    title: 'Тел. матери',
    placeholder: '+992...',
    width: '150px',
    editor: 'text' as const,
    get: (row: Record<string, any>) => row.mom_phone || '',
    text: (row: Record<string, any>) => row.mom_phone || '',
  },
  {
    key: 'actions',
    title: translations.value.messages?.actions || 'Действия',
    width: '120px',
    editor: 'slot' as const,
    text: () => '',
  },
])

function formatErrorMessages(fieldErrors: Record<string, string | string[]>): string {
  return Object.values(fieldErrors)
    .flatMap((value) => (Array.isArray(value) ? value : [value]))
    .filter(Boolean)
    .join('; ')
}

const createStudent = async (draft: Record<string, any>) => {
  if (submitting.value) {
    return
  }

  successMessage.value = ''
  generalError.value = ''

  const lastName = String(draft.last_name || '').trim()
  const firstName = String(draft.name || '').trim()
  const middleName = String(draft.middle_name || '').trim()
  const groupId = draft.group_id ? Number(draft.group_id) : null

  saveLastGroupId(groupId)

  if (!lastName || !firstName) {
    generalError.value = 'Укажите фамилию и имя студента'
    sheetRef.value?.restoreDraft()
    return
  }

  submitting.value = true
  try {
    const response = await api.post('/admin/students', {
      last_name: lastName,
      name: firstName,
      middle_name: middleName || null,
      group_id: groupId,
      phone: draft.phone ? String(draft.phone) : null,
      dad_phone: draft.dad_phone ? String(draft.dad_phone) : null,
      mom_phone: draft.mom_phone ? String(draft.mom_phone) : null,
    })

    const created = response.data?.student
    if (created) {
      recentStudents.value = [created, ...recentStudents.value.filter((item) => item.id !== created.id)]
    }

    forgetPage()
    successMessage.value = response.data?.message || 'Студент успешно добавлен'

    await nextTick()
    sheetRef.value?.focusFirstAddInput()
  } catch (error: any) {
    sheetRef.value?.restoreDraft()
    const fieldErrors = validationErrors(error)
    const formatted = formatErrorMessages(fieldErrors)
    generalError.value = formatted || error?.response?.data?.message || 'Не удалось добавить студента'
  } finally {
    submitting.value = false
  }
}

const handleSearch = () => {
  recentStudents.value = []
  if (groupFilter.value) {
    saveLastGroupId(groupFilter.value)
    sheetRef.value?.setDraftValues({ group_id: groupFilter.value })
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
