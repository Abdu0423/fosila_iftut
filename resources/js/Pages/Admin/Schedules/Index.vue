<template>
  <Layout role="admin">
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center mb-6">
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">Управление расписанием</h1>
              <p class="text-body-1 text-medium-emphasis">Создание и управление расписанием занятий</p>
            </div>
            <div class="d-flex gap-2">
              <v-btn
                v-if="selected.length > 0"
                color="warning"
                variant="outlined"
                prepend-icon="mdi-cog"
                @click="showBulkDialog = true"
              >
                Действия ({{ selected.length }})
              </v-btn>
              <v-btn
                color="info"
                variant="outlined"
                prepend-icon="mdi-download"
                @click="exportSchedules"
              >
                Экспорт
              </v-btn>
              <v-btn
                color="primary"
                size="large"
                prepend-icon="mdi-plus"
                @click="navigateTo('/admin/schedules/create')"
              >
                Добавить расписание
              </v-btn>
            </div>
          </div>
        </v-col>
      </v-row>

      <v-alert
        v-if="missingDepartment"
        type="warning"
        variant="tonal"
        class="mb-4"
      >
        Кафедра для вашей учётной записи не назначена. Расписания появятся после выбора пользователя на странице кафедр.
      </v-alert>

      <ExcelSheet
        :columns="sheetColumns"
        :rows="scheduleRows"
        can-add
        search-placeholder="Поиск по предмету, группе, преподавателю"
        empty-text="Расписания не найдены"
        @create="createSchedule"
        @commit="updateSchedule"
      >
        <template #cell-teacher_id="{ row }">
          <v-autocomplete
            v-if="canAssignTeacher"
            :model-value="row.teacher_id"
            :items="teacherOptions"
            item-title="title"
            item-value="value"
            density="compact"
            variant="plain"
            hide-details
            clearable
            placeholder="Учитель"
            @click.stop
            @update:model-value="assignTeacher(row, $event)"
          />
          <span v-else>{{ personName(row.teacher) || '—' }}</span>
        </template>
        <template #cell-status="{ row }">
          <v-chip
            :color="row.is_active ? 'success' : 'error'"
            size="small"
            variant="flat"
            @click.stop="toggleStatus(row)"
          >
            {{ row.is_active ? 'Активен' : 'Неактивен' }}
          </v-chip>
        </template>
        <template #cell-actions="{ row }">
          <v-btn icon="mdi-eye" size="small" variant="text" color="info" @click.stop="navigateTo(`/admin/schedules/${row.id}`)" />
          <v-btn
            :icon="row.is_active ? 'mdi-toggle-switch' : 'mdi-toggle-switch-off-outline'"
            size="small"
            variant="text"
            :color="row.is_active ? 'success' : 'error'"
            @click.stop="toggleStatus(row)"
          />
          <v-btn icon="mdi-pencil" size="small" variant="text" color="primary" @click.stop="navigateTo(`/admin/schedules/${row.id}/edit`)" />
          <v-btn icon="mdi-content-copy" size="small" variant="text" color="success" @click.stop="duplicateSchedule(row)" />
          <v-btn icon="mdi-delete" size="small" variant="text" color="error" @click.stop="confirmDelete(row)" />
        </template>
      </ExcelSheet>

    </v-container>

    <!-- Диалог массовых действий -->
    <v-dialog v-model="showBulkDialog" max-width="500px">
      <v-card>
        <v-card-title>Массовые действия</v-card-title>
        <v-card-text>
          <v-select
            v-model="bulkAction"
            :items="bulkActions"
            label="Выберите действие"
            variant="outlined"
            density="compact"
          />
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn @click="showBulkDialog = false">Отмена</v-btn>
          <v-btn 
            @click="executeBulkAction" 
            color="primary"
            :disabled="!bulkAction"
          >
            Выполнить
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Диалог подтверждения удаления -->
    <v-dialog v-model="deleteDialog" max-width="500px">
      <v-card>
        <v-card-title>Подтверждение удаления</v-card-title>
        <v-card-text>
          Вы уверены, что хотите удалить расписание "{{ selectedSchedule?.subject?.name }}"?
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn @click="deleteDialog = false">Отмена</v-btn>
          <v-btn @click="deleteSchedule" color="error">Удалить</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </Layout>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { router } from '@/compat/inertia'
import Layout from '../../Layout.vue'
import ExcelSheet from '@/Components/ExcelSheet.vue'

// Props
const props = defineProps({
  schedules: Object,
  subjects: Array,
  groups: Array,
  departments: Array,
  teachers: {
    type: Array,
    default: () => []
  },
  canAssignTeacher: {
    type: Boolean,
    default: false
  },
  missingDepartment: {
    type: Boolean,
    default: false
  },
  filters: Object
})

// Reactive data (инициализированные из server-side)
const search = ref(props.filters?.search)
const subjectFilter = ref(props.filters?.subject_id || null)
const groupFilter = ref(props.filters?.group_id || null)
const semesterFilter = ref(props.filters?.semester || null)
const statusFilter = ref(props.filters?.is_active !== '' ? props.filters?.is_active : null)
const loading = ref(false)
const deleteDialog = ref(false)
const selectedSchedule = ref(null)
const selected = ref([])
const showBulkDialog = ref(false)
const bulkAction = ref(null)
const sortBy = ref([])
const sortDesc = ref([])

const scheduleRows = computed(() => props.schedules?.data || props.schedules || [])

const subjectOptions = computed(() => (props.subjects || []).map((subject) => ({
  title: subject.name,
  value: subject.id
})))

const groupOptions = computed(() => (props.groups || []).map((group) => ({
  title: group.name,
  value: group.id
})))

const departmentOptions = computed(() => (props.departments || []).map((department) => ({
  title: department.name,
  value: department.id
})))

const teacherOptions = computed(() => (props.teachers || []).map((teacher) => ({
  title: teacher.name,
  value: teacher.id
})))

const personName = (person) => person ? `${person.name || ''} ${person.last_name || ''}`.trim() : ''

const sheetColumns = computed(() => [
  {
    key: 'subject_id',
    title: 'Предмет',
    editable: true,
    editor: 'select',
    inherit: true,
    required: true,
    options: subjectOptions.value,
    get: (row) => row.subject_id,
    text: (row) => row.subject?.name || ''
  },
  {
    key: 'group_id',
    title: 'Группа',
    editable: true,
    editor: 'select',
    inherit: true,
    required: true,
    options: groupOptions.value,
    get: (row) => row.group_id,
    text: (row) => row.group?.name || ''
  },
  {
    key: 'department_id',
    title: 'Кафедра',
    editable: true,
    editor: 'select',
    inherit: true,
    required: true,
    options: departmentOptions.value,
    filterChoices: departmentOptions.value.map((department) => department.title).filter(Boolean),
    get: (row) => row.department_id,
    text: (row) => row.department?.name || ''
  },
  {
    key: 'teacher_id',
    title: 'Учитель',
    editor: 'slot',
    text: (row) => personName(row.teacher)
  },
  {
    key: 'semester',
    title: 'Семестр',
    editable: true,
    editor: 'number',
    inherit: true,
    required: true,
    text: (row) => row.semester ? String(row.semester) : ''
  },
  {
    key: 'study_year',
    title: 'Год обучения',
    editable: true,
    editor: 'number',
    inherit: true,
    required: true,
    text: (row) => row.study_year ? String(row.study_year) : ''
  },
  {
    key: 'status',
    title: 'Статус',
    editor: 'slot',
    filterDefault: 'Активен',
    filterChoices: ['Активен', 'Неактивен'],
    text: (row) => row.is_active ? 'Активен' : 'Неактивен'
  },
  {
    key: 'actions',
    title: 'Действия',
    editor: 'slot',
    text: () => ''
  }
])

const creditsFor = (groupId, fallback) => {
  const sibling = scheduleRows.value.find((item) => item.group_id === groupId && item.credits)
  return sibling?.credits || fallback || 1
}

const schedulePayload = (source) => ({
  subject_id: source.subject_id,
  department_id: source.department_id,
  group_id: source.group_id,
  semester: Number(source.semester),
  study_year: Number(source.study_year),
  credits: Number(source.credits || creditsFor(source.group_id, 1)),
  is_active: source.is_active ?? true,
  scheduled_at: source.scheduled_at || null,
  distance_control_1_date: source.distance_control_1_date || null,
  distance_control_2_date: source.distance_control_2_date || null
})

const createSchedule = (draft) => {
  router.post('/admin/schedules', schedulePayload({
    ...draft,
    credits: creditsFor(draft.group_id, 1),
    is_active: true
  }))
}

const updateSchedule = ({ row, column, value }) => {
  router.put(`/admin/schedules/${row.id}`, schedulePayload({
    ...row,
    [column.key]: value
  }))
}

const assignTeacher = (row, teacherId) => {
  const next = teacherId || null
  const current = row.teacher_id || null
  if (next == current) {
    return
  }
  router.put(`/admin/schedules/${row.id}/teacher`, {
    teacher_id: next
  })
}

// Заголовки таблицы
const headers = [
  { title: 'Предмет', key: 'subject', sortable: true },
  { title: 'Группа', key: 'group', sortable: true },
  { title: 'Кафедра', key: 'department', sortable: true },
  { title: 'Семестр', key: 'semester', sortable: true },
  { title: 'Год обучения', key: 'study_year', sortable: true },
  { title: 'Статус', key: 'status', sortable: true },
  { title: 'Действия', key: 'actions', sortable: false }
]

// Варианты для фильтров
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

const statusItems = [
  { title: 'Активные', value: true },
  { title: 'Неактивные', value: false }
]

// Массовые действия
const bulkActions = [
  { title: 'Активировать', value: 'activate' },
  { title: 'Деактивировать', value: 'deactivate' },
  { title: 'Удалить', value: 'delete' }
]

// Computed
const subjectItems = computed(() => {
  return props.subjects || []
})

const groupItems = computed(() => {
  return props.groups || []
})

// Функции для обновления сортировки
const updateSortBy = (value) => {
  sortBy.value = value
}

const updateSortDesc = (value) => {
  sortDesc.value = value
}

// Сортировка расписаний
const sortedSchedules = computed(() => {
  let sorted = [...(props.schedules?.data || [])]
  
  if (sortBy.value && sortBy.value.length > 0) {
    const sortKey = sortBy.value[0]
    const isDesc = sortDesc.value && sortDesc.value.length > 0 ? sortDesc.value[0] : false
    
    sorted.sort((a, b) => {
      let aValue, bValue
      
      // Получаем значения для сортировки в зависимости от ключа
      switch (sortKey) {
        case 'subject':
          aValue = a.subject?.name || ''
          bValue = b.subject?.name || ''
          break
        case 'group':
          aValue = a.group?.name || ''
          bValue = b.group?.name || ''
          break
        case 'department':
          aValue = a.department?.name || ''
          bValue = b.department?.name || ''
          break
        case 'semester':
          aValue = a.semester || 0
          bValue = b.semester || 0
          break
        case 'study_year':
          aValue = a.study_year || 0
          bValue = b.study_year || 0
          break
        case 'status':
          aValue = a.is_active ? 1 : 0
          bValue = b.is_active ? 1 : 0
          break
        default:
          return 0
      }
      
      // Сравнение значений
      if (typeof aValue === 'string' && typeof bValue === 'string') {
        return isDesc 
          ? bValue.localeCompare(aValue)
          : aValue.localeCompare(bValue)
      } else {
        return isDesc 
          ? (bValue > aValue ? 1 : bValue < aValue ? -1 : 0)
          : (aValue > bValue ? 1 : aValue < bValue ? -1 : 0)
      }
    })
  }
  
  return sorted
})

// Server-side фильтрация
const applyFilters = () => {
  const params = {}
  
  if (search.value) {
    params.search = search.value
  }
  
  if (subjectFilter.value) {
    params.subject_id = subjectFilter.value
  }
  
  if (groupFilter.value) {
    params.group_id = groupFilter.value
  }
  
  if (semesterFilter.value) {
    params.semester = semesterFilter.value
  }
  
  if (statusFilter.value !== null) {
    params.is_active = statusFilter.value
  }
  
  router.get('/admin/schedules', params, {
    preserveState: true,
    preserveScroll: true
  })
}

// Debounced функция для поиска
let searchTimeout = null
const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    applyFilters()
  }, 500)
}

// Watch для автоматического применения фильтров
watch(search, () => {
  debouncedSearch()
})

watch([subjectFilter, groupFilter, semesterFilter, statusFilter], () => {
  applyFilters()
})

// Methods
const navigateTo = (route) => {
  router.visit(route)
}

const changePage = (page) => {
  const params = { ...props.filters, page }
  router.get('/admin/schedules', params, {
    preserveState: true,
    preserveScroll: true
  })
}

const toggleStatus = (schedule) => {
  const previous = schedule.is_active
  schedule.is_active = !schedule.is_active
  router.post(`/admin/schedules/${schedule.id}/toggle-status`, {}, {
    preserveState: true,
    onError: () => {
      schedule.is_active = previous
    }
  })
}

const duplicateSchedule = (schedule) => {
  router.post(`/admin/schedules/${schedule.id}/duplicate`, {}, {
    preserveState: true,
    onSuccess: () => {
      // Schedule duplicated successfully
    },
    onError: (errors) => {
      console.error('Ошибка при дублировании расписания:', errors)
    }
  })
}

const confirmDelete = (schedule) => {
  selectedSchedule.value = schedule
  deleteDialog.value = true
}

const deleteSchedule = () => {
  if (selectedSchedule.value) {
    console.log('Attempting to delete schedule:', selectedSchedule.value)
    console.log('Schedule ID:', selectedSchedule.value.id)
    console.log('Delete URL:', `/admin/schedules/${selectedSchedule.value.id}`)
    
    if (!selectedSchedule.value.id) {
      console.error('Schedule ID is missing!')
      return
    }
    
    router.delete(`/admin/schedules/${selectedSchedule.value.id}`, {
      onSuccess: () => {
        console.log('Schedule deleted successfully')
        deleteDialog.value = false
        selectedSchedule.value = null
      },
      onError: (errors) => {
        console.error('Ошибка при удалении расписания:', errors)
      }
    })
  }
}

const executeBulkAction = () => {
  const ids = selected.value.map(item => item.id)
  router.post('/admin/schedules/bulk-action', {
    action: bulkAction.value,
    ids: ids
  }, {
    preserveState: true,
    onSuccess: () => {
      showBulkDialog.value = false
      bulkAction.value = null
      selected.value = []
    }
  })
}

const exportSchedules = () => {
  router.get('/admin/schedules/export', props.filters, {
    preserveState: true
  })
}
</script>

<style scoped>
.v-card {
  border-radius: 12px;
}

.v-data-table {
  border-radius: 12px;
}

.status-toggle {
  cursor: pointer;
}
</style>
