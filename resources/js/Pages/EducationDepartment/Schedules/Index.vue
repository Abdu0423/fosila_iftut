<template>
  <Layout :role="getRole">
    <v-container fluid class="pa-6">
      <!-- Заголовок -->
      <div class="d-flex justify-space-between align-center mb-6">
        <div>
          <h1 class="text-h4 font-weight-bold mb-2">
            {{ translations.education_department?.schedules_title || 'Расписание' }}
          </h1>
          <p class="text-body-1 text-medium-emphasis">
            {{ translations.education_department?.schedules_subtitle || 'Просмотр всех расписаний' }}
          </p>
        </div>
        <v-btn
          color="primary"
          prepend-icon="mdi-plus"
          @click="router.visit(`/${getRoutePrefix()}/schedules/create`)"
        >
          {{ translations.messages?.add || 'Добавить' }}
        </v-btn>
      </div>

      <!-- Фильтры -->
      <v-card class="mb-6">
        <v-card-text>
          <v-row>
            <v-col cols="12" md="4">
              <v-select
                v-model="statusFilter"
                :items="statusOptions"
                :label="translations.messages?.status || 'Статус'"
                prepend-inner-icon="mdi-filter"
                variant="outlined"
                density="comfortable"
              ></v-select>
            </v-col>
          </v-row>
        </v-card-text>
      </v-card>

      <!-- Таблица расписаний -->
      <v-card>
        <v-data-table
          :headers="headers"
          :items="sortedSchedules"
          :items-per-page="itemsPerPage"
          :page="currentPage"
          hide-default-footer
          class="elevation-0"
          :sort-by="sortBy"
          :sort-desc="sortDesc"
          @update:sort-by="updateSortBy"
          @update:sort-desc="updateSortDesc"
        >
          <template v-slot:header.subject>
            <div class="d-flex align-center">
              <v-icon size="small" class="mr-2">mdi-book-open-page-variant</v-icon>
              <span>{{ translations.education_department?.schedule_subject || 'Предмет' }}</span>
            </div>
          </template>
          <template v-slot:header.group>
            <div class="d-flex align-center">
              <v-icon size="small" class="mr-2">mdi-account-group</v-icon>
              <span>{{ translations.education_department?.schedule_group || 'Группа' }}</span>
            </div>
          </template>
          <template v-slot:header.teacher>
            <div class="d-flex align-center">
              <v-icon size="small" class="mr-2">mdi-teach</v-icon>
              <span>{{ translations.education_department?.schedule_teacher || 'Преподаватель' }}</span>
            </div>
          </template>
          <template v-slot:header.semester>
            <div class="d-flex align-center">
              <v-icon size="small" class="mr-2">mdi-numeric</v-icon>
              <span>{{ translations.education_department?.schedule_semester || 'Семестр' }}</span>
            </div>
          </template>
          <template v-slot:header.study_year>
            <div class="d-flex align-center">
              <v-icon size="small" class="mr-2">mdi-calendar-year</v-icon>
              <span>{{ translations.education_department?.schedule_study_year || 'Год обучения' }}</span>
            </div>
          </template>
          <template v-slot:header.status>
            <div class="d-flex align-center">
              <v-icon size="small" class="mr-2">mdi-check-circle</v-icon>
              <span>{{ translations.messages?.status || 'Статус' }}</span>
            </div>
          </template>
          <template v-slot:header.actions>
            <div class="d-flex align-center">
              <v-icon size="small" class="mr-2">mdi-cog</v-icon>
              <span>{{ translations.messages?.actions || 'Действия' }}</span>
            </div>
          </template>
          <template v-slot:item.subject="{ item }">
            <div class="font-weight-medium">
              {{ item.subject?.name || translations.education_department?.no_subject || 'Не указан' }}
            </div>
          </template>

          <template v-slot:item.group="{ item }">
            <div>
              {{ item.group?.name || '—' }}
            </div>
          </template>

          <template v-slot:item.teacher="{ item }">
            <div>
              {{ item.teacher ? `${item.teacher.name} ${item.teacher.last_name}` : '—' }}
            </div>
          </template>

          <template v-slot:item.semester="{ item }">
            <v-chip size="small" color="primary" variant="flat">
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
              {{ item.is_active ? (translations.messages?.active || 'Активен') : (translations.messages?.inactive || 'Неактивен') }}
            </v-chip>
          </template>

          <template v-slot:item.actions="{ item }">
            <v-btn
              icon
              variant="text"
              color="primary"
              size="small"
              @click="router.visit(`/${getRoutePrefix()}/schedules/${item.id}/edit`)"
              :title="translations.messages?.edit || 'Редактировать'"
            >
              <v-icon>mdi-pencil</v-icon>
            </v-btn>
          </template>

          <template v-slot:no-data>
            <div class="text-center py-8">
              <v-icon size="64" color="grey-lighten-1">mdi-calendar-blank</v-icon>
              <p class="text-h6 text-medium-emphasis mt-4">
                {{ translations.education_department?.no_schedules || 'Расписания не найдены' }}
              </p>
            </div>
          </template>
        </v-data-table>

        <!-- Пагинация -->
        <v-divider v-if="sortedSchedules.length > itemsPerPage"></v-divider>
        <div v-if="sortedSchedules.length > itemsPerPage" class="d-flex justify-center pa-4">
          <v-pagination
            :length="totalPages"
            v-model="currentPage"
            :total-visible="totalVisible"
          ></v-pagination>
        </div>
      </v-card>
    </v-container>
  </Layout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import Layout from '../../Layout.vue'

const page = usePage()
const translations = computed(() => page.props.translations || {})

const props = defineProps({
  schedules: {
    type: Array,
    default: () => []
  }
})

const statusFilter = ref('active') // По умолчанию активные
const currentPage = ref(1)
const itemsPerPage = ref(20)
const sortBy = ref([])
const sortDesc = ref([])

const statusOptions = [
  { title: translations.value.messages?.active || 'Активные', value: 'active' },
  { title: translations.value.messages?.inactive || 'Неактивные', value: 'inactive' },
  { title: translations.value.messages?.all || 'Все', value: 'all' }
]

const headers = computed(() => [
  { 
    title: translations.value.education_department?.schedule_subject || 'Предмет', 
    key: 'subject', 
    sortable: true
  },
  { 
    title: translations.value.education_department?.schedule_group || 'Группа', 
    key: 'group', 
    sortable: true
  },
  { 
    title: translations.value.education_department?.schedule_teacher || 'Преподаватель', 
    key: 'teacher', 
    sortable: true
  },
  { 
    title: translations.value.education_department?.schedule_semester || 'Семестр', 
    key: 'semester', 
    sortable: true
  },
  { 
    title: translations.value.education_department?.schedule_study_year || 'Год обучения', 
    key: 'study_year', 
    sortable: true
  },
  { 
    title: translations.value.messages?.status || 'Статус', 
    key: 'status', 
    sortable: true
  },
  { 
    title: translations.value.messages?.actions || 'Действия', 
    key: 'actions', 
    sortable: false
  }
])

// Фильтруем расписания на клиенте
const filteredSchedules = computed(() => {
  let filtered = props.schedules || []
  
  // Фильтр по статусу
  if (statusFilter.value === 'active') {
    filtered = filtered.filter(schedule => schedule.is_active === true)
  } else if (statusFilter.value === 'inactive') {
    filtered = filtered.filter(schedule => schedule.is_active === false)
  }
  // Если 'all', то не фильтруем
  
  return filtered
})

// Функции для обновления сортировки
const updateSortBy = (value) => {
  sortBy.value = value
  currentPage.value = 1 // Сбрасываем страницу при сортировке
}

const updateSortDesc = (value) => {
  sortDesc.value = value
  currentPage.value = 1 // Сбрасываем страницу при сортировке
}

// Сортировка расписаний
const sortedSchedules = computed(() => {
  let sorted = [...filteredSchedules.value]
  
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

// Пагинация на клиенте
const paginatedSchedules = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  const end = start + itemsPerPage.value
  return sortedSchedules.value.slice(start, end)
})

// Вычисляемое свойство для общего количества страниц
const totalPages = computed(() => {
  return Math.ceil(sortedSchedules.value.length / itemsPerPage.value)
})

// Вычисляемое свойство для видимых страниц пагинации
const totalVisible = computed(() => {
  const total = totalPages.value
  return total <= 5 ? total : 5
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

// Сбрасываем страницу при изменении фильтра
const resetPage = () => {
  currentPage.value = 1
}

// Отслеживаем изменение фильтра статуса
import { watch } from 'vue'
watch(statusFilter, () => {
  resetPage()
})
</script>
