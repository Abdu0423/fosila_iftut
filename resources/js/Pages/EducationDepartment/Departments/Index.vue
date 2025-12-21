<template>
  <Layout role="education_department">
    <v-container fluid class="pa-6">
      <!-- Заголовок -->
      <div class="d-flex justify-space-between align-center mb-6">
        <div>
          <h1 class="text-h4 font-weight-bold mb-2">
            {{ translations.education_department?.departments_title || 'Кафедры' }}
          </h1>
          <p class="text-body-1 text-medium-emphasis">
            {{ translations.education_department?.departments_subtitle || 'Управление кафедрами' }}
          </p>
        </div>
        <v-btn
          color="primary"
          prepend-icon="mdi-plus"
          @click="createDepartment"
        >
          {{ translations.messages?.add || 'Добавить' }}
        </v-btn>
      </div>

      <!-- Фильтры и поиск -->
      <v-card class="mb-6">
        <v-card-text>
          <v-row>
            <v-col cols="12" md="6">
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
            <v-col cols="12" md="6">
              <v-select
                v-model="statusFilter"
                :items="statusOptions"
                :label="translations.education_department?.filter_by_status || 'Фильтр по статусу'"
                prepend-inner-icon="mdi-filter"
                clearable
                variant="outlined"
                density="comfortable"
                @update:model-value="handleStatusFilter"
              ></v-select>
            </v-col>
          </v-row>
        </v-card-text>
      </v-card>

      <!-- Таблица кафедр -->
      <v-card>
        <v-data-table
          :headers="headers"
          :items="departments.data"
          :items-per-page="departments.per_page"
          hide-default-footer
        >
          <template v-slot:item.name="{ item }">
            <div class="d-flex align-center py-2">
              <v-avatar color="primary" size="40" class="mr-3">
                <v-icon color="white">mdi-office-building</v-icon>
              </v-avatar>
              <div>
                <div class="font-weight-medium">{{ item.name }}</div>
                <div class="text-caption text-medium-emphasis" v-if="item.code">{{ item.code }}</div>
              </div>
            </div>
          </template>

          <template v-slot:item.groups_count="{ item }">
            <v-chip
              color="info"
              size="small"
            >
              {{ item.groups_count || 0 }}
            </v-chip>
          </template>

          <template v-slot:item.is_active="{ item }">
            <v-chip
              :color="item.is_active ? 'success' : 'error'"
              size="small"
            >
              {{ item.is_active ? (translations.messages?.active || 'Активна') : (translations.messages?.inactive || 'Неактивна') }}
            </v-chip>
          </template>

          <template v-slot:item.actions="{ item }">
            <div class="d-flex gap-2">
              <v-btn
                icon="mdi-pencil"
                variant="text"
                size="small"
                color="primary"
                @click="editDepartment(item)"
                :title="translations.messages?.edit || 'Редактировать'"
              ></v-btn>
            </div>
          </template>

          <template v-slot:no-data>
            <div class="text-center pa-8">
              <v-icon size="64" color="grey-lighten-1">mdi-office-building-outline</v-icon>
              <p class="text-h6 mt-4 text-medium-emphasis">
                {{ translations.education_department?.no_departments || 'Кафедры не найдены' }}
              </p>
            </div>
          </template>
        </v-data-table>

        <!-- Пагинация -->
        <v-divider></v-divider>
        <div class="d-flex justify-center pa-4">
          <v-pagination
            :length="Math.ceil(departments.total / departments.per_page)"
            :model-value="departments.current_page"
            @update:model-value="handlePageChange"
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
  departments: {
    type: Object,
    required: true
  },
  filters: {
    type: Object,
    default: () => ({})
  }
})

const searchQuery = ref(props.filters.search || '')
const statusFilter = ref(props.filters.status !== undefined ? props.filters.status : null)

const headers = computed(() => [
  { title: translations.value.education_department?.department_name || 'Название', key: 'name', sortable: false },
  { title: translations.value.education_department?.groups_count || 'Количество групп', key: 'groups_count', sortable: false },
  { title: translations.value.messages?.status || 'Статус', key: 'is_active', sortable: false },
  { title: translations.value.messages?.actions || 'Действия', key: 'actions', sortable: false }
])

const statusOptions = [
  { title: translations.value.messages?.active || 'Активна', value: true },
  { title: translations.value.messages?.inactive || 'Неактивна', value: false }
]

const handleSearch = () => {
  router.get('/education/departments', {
    search: searchQuery.value,
    status: statusFilter.value
  }, {
    preserveState: true,
    preserveScroll: true
  })
}

const handleStatusFilter = () => {
  router.get('/education/departments', {
    search: searchQuery.value,
    status: statusFilter.value
  }, {
    preserveState: true,
    preserveScroll: true
  })
}

const handlePageChange = (page) => {
  router.get('/education/departments', {
    page: page,
    search: searchQuery.value,
    status: statusFilter.value
  }, {
    preserveState: true,
    preserveScroll: true
  })
}

const createDepartment = () => {
  router.visit('/education/departments/create')
}

const editDepartment = (department) => {
  router.visit(`/education/departments/${department.id}/edit`)
}
</script>

