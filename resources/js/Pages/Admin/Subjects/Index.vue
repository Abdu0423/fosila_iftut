<template>
  <Layout role="admin">
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center mb-6">
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">Управление предметами</h1>
              <p class="text-body-1 text-medium-emphasis">Создание и редактирование учебных предметов</p>
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
                @click="exportSubjects"
              >
                Экспорт
              </v-btn>
              <v-btn
                color="primary"
                size="large"
                prepend-icon="mdi-plus"
                @click="navigateTo('/admin/subjects/create')"
              >
                Добавить предмет
              </v-btn>
            </div>
          </div>
        </v-col>
      </v-row>

      <!-- Фильтры и поиск -->
      <v-row class="mb-4">
        <v-col cols="12" md="6">
          <v-text-field
            v-model="search"
            prepend-inner-icon="mdi-magnify"
            label="Поиск по названию или коду..."
            variant="outlined"
            clearable
            @input="filterSubjects"
          />
        </v-col>
        <v-col cols="12" md="3">
          <v-select
            v-model="statusFilter"
            label="Статус"
            :items="statusItems"
            variant="outlined"
            clearable
            @update:model-value="filterSubjects"
          />
        </v-col>
        <v-col cols="12" md="3">
          <v-select
            v-model="departmentFilter"
            label="Отделение"
            :items="departmentItems"
            item-title="name"
            item-value="id"
            variant="outlined"
            clearable
            @update:model-value="filterSubjects"
          />
        </v-col>
      </v-row>

      <!-- Таблица предметов -->
      <v-card>
        <v-data-table
          :headers="headers"
          :items="props.subjects.data"
          :loading="loading"
          item-value="id"
          v-model="selected"
          show-select
          class="elevation-1"
        >
          <template v-slot:item.code="{ item }">
            <v-chip
              v-if="item.code"
              color="primary"
              variant="outlined"
              size="small"
            >
              {{ item.code }}
            </v-chip>
            <span v-else class="text-grey">Не указан</span>
          </template>

          <template v-slot:item.department="{ item }">
            <span v-if="item.department">{{ item.department.name }}</span>
            <span v-else class="text-grey">Не указано</span>
          </template>

          <template v-slot:item.credits="{ item }">
            <v-chip
              color="info"
              variant="outlined"
              size="small"
            >
              {{ item.credits }} кр.
            </v-chip>
          </template>

          <template v-slot:item.is_active="{ item }">
            <v-switch
              :model-value="item.is_active"
              @update:model-value="toggleStatus(item)"
              color="success"
              hide-details
              density="compact"
            />
          </template>

          <template v-slot:item.actions="{ item }">
            <div class="d-flex gap-2">
              <v-btn
                icon="mdi-eye"
                size="small"
                variant="text"
                color="info"
                @click="navigateTo(`/admin/subjects/${item.id}`)"
              />
              <v-btn
                icon="mdi-pencil"
                size="small"
                variant="text"
                color="warning"
                @click="navigateTo(`/admin/subjects/${item.id}/edit`)"
              />
              <v-btn
                icon="mdi-content-copy"
                size="small"
                variant="text"
                color="info"
                @click="duplicateSubject(item)"
                title="Дублировать"
              />
              <v-btn
                icon="mdi-delete"
                size="small"
                variant="text"
                color="error"
                @click="confirmDelete(item)"
                title="Удалить"
              />
            </div>
          </template>
        </v-data-table>
      </v-card>

      <!-- Диалог подтверждения удаления -->
      <v-dialog v-model="deleteDialog" max-width="500px">
        <v-card>
          <v-card-title class="text-h5">Подтверждение удаления</v-card-title>
          <v-card-text>
            Вы уверены, что хотите удалить предмет "{{ selectedSubject?.name }}"?
            <br><br>
            <v-alert
              type="warning"
              variant="outlined"
              class="mt-2"
            >
              Это действие нельзя отменить!
            </v-alert>
          </v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="grey" variant="text" @click="deleteDialog = false">
              Отмена
            </v-btn>
            <v-btn color="error" @click="deleteSubject">
              Удалить
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>

      <!-- Диалог массовых действий -->
      <v-dialog v-model="showBulkDialog" max-width="500px">
        <v-card>
          <v-card-title class="text-h5">Массовые действия</v-card-title>
          <v-card-text>
            <p class="mb-4">Выбрано предметов: {{ selected.length }}</p>
            <v-select
              v-model="bulkAction"
              label="Выберите действие"
              :items="bulkActions"
              item-title="title"
              item-value="value"
              variant="outlined"
            />
            <v-alert
              v-if="bulkAction === 'delete'"
              type="warning"
              variant="outlined"
              class="mt-4"
            >
              Удаление предметов нельзя отменить! Предметы со связанными уроками или расписаниями не будут удалены.
            </v-alert>
          </v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="grey" variant="text" @click="showBulkDialog = false">
              Отмена
            </v-btn>
            <v-btn 
              color="primary" 
              :disabled="!bulkAction"
              @click="executeBulkAction"
            >
              Выполнить
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </v-container>
  </Layout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import Layout from '../../Layout.vue'

// Props
const props = defineProps({
  subjects: Object,
  departments: Array,
  filters: Object
})

// Reactive data (инициализированные из server-side)
const search = ref(props.filters?.search)
const statusFilter = ref(props.filters?.is_active !== '' ? props.filters?.is_active : null)
const departmentFilter = ref(props.filters?.department_id || null)
const loading = ref(false)
const deleteDialog = ref(false)
const selectedSubject = ref(null)
const selected = ref([])
const showBulkDialog = ref(false)
const bulkAction = ref(null)

// Заголовки таблицы
const headers = [
  { title: 'ID', key: 'id', width: '80px' },
  { title: 'Название', key: 'name' },
  { title: 'Код', key: 'code', width: '120px' },
  { title: 'Отделение', key: 'department', width: '200px' },
  { title: 'Кредиты', key: 'credits', width: '100px' },
  { title: 'Статус', key: 'is_active', width: '120px' },
  { title: 'Действия', key: 'actions', width: '150px', sortable: false }
]

// Варианты статуса
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
const departmentItems = computed(() => {
  return props.departments || []
})

// Server-side фильтрация
const applyFilters = () => {
  const params = {}
  
  if (search.value) {
    params.search = search.value
  }
  
  if (statusFilter.value !== null) {
    params.is_active = statusFilter.value
  }
  
  if (departmentFilter.value) {
    params.department_id = departmentFilter.value
  }
  
  router.get('/admin/subjects', params, {
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

watch([statusFilter, departmentFilter], () => {
  applyFilters()
})

// Methods
const navigateTo = (route) => {
  router.visit(route)
}

const filterSubjects = () => {
  // Логика фильтрации реализована через computed свойство
}

const confirmDelete = (subject) => {
  selectedSubject.value = subject
  deleteDialog.value = true
}

const deleteSubject = () => {
  if (selectedSubject.value) {
    console.log('Deleting subject:', selectedSubject.value)
    router.delete(`/admin/subjects/${selectedSubject.value.id}`, {
      onSuccess: (page) => {
        console.log('Delete success:', page)
        deleteDialog.value = false
        selectedSubject.value = null
      },
      onError: (errors) => {
        console.log('Delete error:', errors)
      },
      onFinish: () => {
        console.log('Delete finished')
      }
    })
  }
}

const toggleStatus = (subject) => {
  router.post(`/admin/subjects/${subject.id}/toggle-status`, {}, {
    preserveState: true,
    preserveScroll: true
  })
}

const duplicateSubject = (subject) => {
  router.post(`/admin/subjects/${subject.id}/duplicate`)
}

const executeBulkAction = () => {
  if (!bulkAction.value || selected.value.length === 0) return

  router.post('/admin/subjects/bulk-action', {
    action: bulkAction.value,
    subject_ids: selected.value
  }, {
    onSuccess: () => {
      showBulkDialog.value = false
      bulkAction.value = null
      selected.value = []
    }
  })
}

const exportSubjects = () => {
  window.location.href = '/admin/subjects-export'
}

onMounted(() => {
  console.log('Страница управления предметами загружена')
})
</script>

<style scoped>
.v-card {
  border-radius: 12px;
}
</style>
