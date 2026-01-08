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

      <!-- Фильтры и поиск -->
      <v-row class="mb-4">
        <v-col cols="12" md="3">
          <v-text-field
            v-model="search"
            prepend-inner-icon="mdi-magnify"
            label="Поиск по предмету, учителю или группе..."
            variant="outlined"
            density="compact"
            clearable
          />
        </v-col>
        <v-col cols="12" md="2">
          <v-select
            v-model="subjectFilter"
            :items="subjectItems"
            item-title="name"
            item-value="id"
            label="Предмет"
            variant="outlined"
            density="compact"
            clearable
          />
        </v-col>
        <v-col cols="12" md="2">
          <v-select
            v-model="groupFilter"
            :items="groupItems"
            item-title="name"
            item-value="id"
            label="Группа"
            variant="outlined"
            density="compact"
            clearable
          />
        </v-col>
        <v-col cols="12" md="2">
          <v-select
            v-model="semesterFilter"
            :items="semesterItems"
            label="Семестр"
            variant="outlined"
            density="compact"
            clearable
          />
        </v-col>
        <v-col cols="12" md="2">
          <v-select
            v-model="statusFilter"
            :items="statusItems"
            label="Статус"
            variant="outlined"
            density="compact"
            clearable
          />
        </v-col>
      </v-row>

      <!-- Таблица расписаний -->
      <v-card>
        <v-data-table
          :headers="headers"
          :items="sortedSchedules"
          :loading="loading"
          item-value="id"
          v-model="selected"
          show-select
          class="elevation-1"
          :sort-by="sortBy"
          :sort-desc="sortDesc"
          @update:sort-by="updateSortBy"
          @update:sort-desc="updateSortDesc"
        >
          <template v-slot:header.subject>
            <div class="d-flex align-center">
              <v-icon size="small" class="mr-2">mdi-book-open-page-variant</v-icon>
              <span>Предмет</span>
            </div>
          </template>
          <template v-slot:header.group>
            <div class="d-flex align-center">
              <v-icon size="small" class="mr-2">mdi-account-group</v-icon>
              <span>Группа</span>
            </div>
          </template>
          <template v-slot:header.teacher>
            <div class="d-flex align-center">
              <v-icon size="small" class="mr-2">mdi-teach</v-icon>
              <span>Преподаватель</span>
            </div>
          </template>
          <template v-slot:header.semester>
            <div class="d-flex align-center">
              <v-icon size="small" class="mr-2">mdi-numeric</v-icon>
              <span>Семестр</span>
            </div>
          </template>
          <template v-slot:header.study_year>
            <div class="d-flex align-center">
              <v-icon size="small" class="mr-2">mdi-calendar-year</v-icon>
              <span>Год обучения</span>
            </div>
          </template>
          <template v-slot:header.status>
            <div class="d-flex align-center">
              <v-icon size="small" class="mr-2">mdi-check-circle</v-icon>
              <span>Статус</span>
            </div>
          </template>
          <template v-slot:header.actions>
            <div class="d-flex align-center">
              <v-icon size="small" class="mr-2">mdi-cog</v-icon>
              <span>Действия</span>
            </div>
          </template>

          <template v-slot:item.subject="{ item }">
            <div class="font-weight-medium">
              {{ item.subject?.name }}
            </div>
          </template>

          <template v-slot:item.teacher="{ item }">
            <div class="font-weight-medium">
              {{ item.teacher ? `${item.teacher.name} ${item.teacher.last_name}` : '—' }}
              <div class="text-caption text-medium-emphasis" v-if="item.teacher?.email">
                {{ item.teacher.email }}
              </div>
            </div>
          </template>

          <template v-slot:item.group="{ item }">
            <v-chip
              size="small"
              color="primary"
              variant="tonal"
            >
              {{ item.group?.name || '—' }}
            </v-chip>
          </template>

          <template v-slot:item.semester="{ item }">
            <v-chip
              size="small"
              color="primary"
              variant="flat"
            >
              {{ item.semester || '—' }}
            </v-chip>
          </template>

          <template v-slot:item.study_year="{ item }">
            <div>
              {{ item.study_year || '—' }}
            </div>
          </template>

          <template v-slot:item.status="{ item }">
            <v-chip
              :color="item.is_active ? 'success' : 'error'"
              size="small"
              variant="flat"
            >
              {{ item.is_active ? 'Активен' : 'Неактивен' }}
            </v-chip>
          </template>

          <template v-slot:item.actions="{ item }">
            <div class="d-flex gap-1">
              <v-btn
                icon="mdi-eye"
                size="small"
                variant="text"
                color="info"
                @click="navigateTo(`/admin/schedules/${item.id}`)"
              />
              <v-btn
                icon="mdi-pencil"
                size="small"
                variant="text"
                color="primary"
                @click="navigateTo(`/admin/schedules/${item.id}/edit`)"
              />
              <v-btn
                icon="mdi-content-copy"
                size="small"
                variant="text"
                color="success"
                @click="duplicateSchedule(item)"
              />
              <v-btn
                icon="mdi-delete"
                size="small"
                variant="text"
                color="error"
                @click="confirmDelete(item)"
              />
            </div>
          </template>
        </v-data-table>

        <!-- Пагинация -->
        <div class="d-flex justify-center pa-4" v-if="props.schedules.last_page > 1">
          <v-pagination
            :model-value="props.schedules.current_page"
            :length="props.schedules.last_page"
            @update:model-value="changePage"
            :total-visible="5"
          />
        </div>
      </v-card>
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

<script setup>
import { ref, computed, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import Layout from '../../Layout.vue'

// Props
const props = defineProps({
  schedules: Object,
  subjects: Array,
  groups: Array,
  teachers: Array,
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

// Заголовки таблицы
const headers = [
  { title: 'Предмет', key: 'subject', sortable: true },
  { title: 'Группа', key: 'group', sortable: true },
  { title: 'Преподаватель', key: 'teacher', sortable: true },
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
        case 'teacher':
          aValue = a.teacher ? `${a.teacher.name} ${a.teacher.last_name}` : ''
          bValue = b.teacher ? `${b.teacher.name} ${b.teacher.last_name}` : ''
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
  router.post(`/admin/schedules/${schedule.id}/toggle-status`, {}, {
    preserveState: true,
    onSuccess: () => {
      // Status updated successfully
    },
    onError: (errors) => {
      console.error('Ошибка при изменении статуса:', errors)
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
</style>
