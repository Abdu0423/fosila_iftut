<template>
  <Layout role="education_department">
    <v-container fluid class="pa-6">
      <!-- Заголовок -->
      <div class="d-flex justify-space-between align-center mb-6">
        <div>
          <h1 class="text-h4 font-weight-bold mb-2">
            {{ translations.education_department?.groups_title || 'Гурӯҳҳо' }}
          </h1>
          <p class="text-body-1 text-medium-emphasis">
            {{ translations.education_department?.groups_subtitle || 'Тамошои ҳамаи гурӯҳҳо' }}
          </p>
        </div>
        <v-btn
          color="primary"
          prepend-icon="mdi-plus"
          @click="createGroup"
        >
          {{ translations.messages?.add || 'Илова кардан' }}
        </v-btn>
      </div>

      <!-- Фильтры и поиск -->
      <v-card class="mb-6">
        <v-card-text>
          <v-row>
            <v-col cols="12" md="6">
              <v-text-field
                v-model="searchQuery"
                :label="translations.messages?.search || 'Ҷустуҷӯ'"
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
                :label="translations.education_department?.filter_by_status || 'Филтр аз рӯи вазъият'"
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

      <!-- Таблица групп -->
      <v-card>
        <v-data-table
          :headers="headers"
          :items="groups.data"
          :items-per-page="groups.per_page"
          hide-default-footer
        >
          <template v-slot:item.name="{ item }">
            <div class="d-flex align-center py-2">
              <v-avatar color="primary" size="40" class="mr-3">
                <v-icon color="white">mdi-account-multiple</v-icon>
              </v-avatar>
              <div>
                <div class="font-weight-medium">{{ item.name }}</div>
                <div class="text-caption text-medium-emphasis" v-if="item.full_name">{{ item.full_name }}</div>
              </div>
            </div>
          </template>

          <template v-slot:item.users_count="{ item }">
            <v-chip
              color="info"
              size="small"
            >
              {{ item.users_count || 0 }}
            </v-chip>
          </template>

          <template v-slot:item.status="{ item }">
            <v-chip
              :color="item.status === 'active' ? 'success' : 'error'"
              size="small"
            >
              {{ item.status === 'active' ? (translations.messages?.active || 'Фаъол') : (translations.messages?.inactive || 'Ғайрифаъол') }}
            </v-chip>
          </template>

          <template v-slot:item.actions="{ item }">
            <div class="d-flex gap-2">
              <v-btn
                icon
                variant="text"
                color="primary"
                size="small"
                @click="editGroup(item)"
                :title="translations.messages?.edit || 'Таҳрир кардан'"
              >
                <v-icon>mdi-pencil</v-icon>
              </v-btn>
            </div>
          </template>

          <template v-slot:no-data>
            <div class="text-center pa-8">
              <v-icon size="64" color="grey-lighten-1">mdi-account-group-off</v-icon>
              <p class="text-h6 mt-4 text-medium-emphasis">
                {{ translations.education_department?.no_groups || 'Гурӯҳҳо ёфт нашуданд' }}
              </p>
            </div>
          </template>
        </v-data-table>

        <!-- Пагинация -->
        <v-divider v-if="groups.data && groups.data.length > 0"></v-divider>
        <div v-if="groups.data && groups.data.length > 0 && totalPages > 1" class="d-flex justify-center pa-4">
          <v-pagination
            :length="totalPages"
            :model-value="groups.current_page"
            @update:model-value="handlePageChange"
            :total-visible="totalVisible"
          ></v-pagination>
        </div>
      </v-card>
    </v-container>
  </Layout>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import Layout from '../../Layout.vue'

const page = usePage()
const translations = computed(() => page.props.translations || {})

const props = defineProps({
  groups: {
    type: Object,
    required: true
  },
  filters: {
    type: Object,
    default: () => ({})
  }
})

const searchQuery = ref(props.filters.search || '')
const statusFilter = ref(props.filters.status || null)

// Синхронизируем значения с props после обновления (без вызова фильтров)
watch(() => props.filters, (newFilters) => {
  if (newFilters.search !== undefined) {
    searchQuery.value = newFilters.search || ''
  }
  if (newFilters.status !== undefined) {
    statusFilter.value = newFilters.status || null
  }
}, { deep: true })

const headers = computed(() => [
  { title: translations.value.education_department?.group_name || 'Номи гурӯҳ', key: 'name', sortable: false },
  { title: translations.value.education_department?.group_students_count || 'Шумораи донишҷӯён', key: 'users_count', sortable: false },
  { title: translations.value.messages?.status || 'Вазъият', key: 'status', sortable: false },
  { title: translations.value.messages?.actions || 'Амалҳо', key: 'actions', sortable: false }
])

const statusOptions = [
  { title: translations.value.messages?.active || 'Фаъол', value: 'active' },
  { title: translations.value.education_department?.graduated || 'Выпущена', value: 'graduated' },
  { title: translations.value.education_department?.suspended || 'Приостановлена', value: 'suspended' }
]

// Debounced функция для поиска
let searchTimeout = null

const handleSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    const params = {}
    if (searchQuery.value) {
      params.search = searchQuery.value
    }
    if (statusFilter.value) {
      params.status = statusFilter.value
    }
    
    router.get('/education/groups', params, {
      preserveState: true,
      preserveScroll: true
    })
  }, 500)
}

const handleStatusFilter = () => {
  const params = {}
  if (searchQuery.value) {
    params.search = searchQuery.value
  }
  if (statusFilter.value) {
    params.status = statusFilter.value
  }
  
  router.get('/education/groups', params, {
    preserveState: true,
    preserveScroll: true
  })
}

const handlePageChange = (pageNum) => {
  const params = {
    page: pageNum
  }
  if (searchQuery.value) {
    params.search = searchQuery.value
  }
  if (statusFilter.value) {
    params.status = statusFilter.value
  }
  
  router.get('/education/groups', params, {
    preserveState: true,
    preserveScroll: true,
    replace: false
  })
}

// Вычисляемое свойство для общего количества страниц
const totalPages = computed(() => Math.ceil(props.groups.total / props.groups.per_page))

// Вычисляемое свойство для видимых страниц пагинации
// Если страниц <= 5, показываем все, иначе максимум 5
const totalVisible = computed(() => {
  const total = totalPages.value
  return total <= 5 ? total : 5
})

const createGroup = () => {
  router.visit('/education/groups/create')
}

const editGroup = (group) => {
  router.visit(`/education/groups/${group.id}/edit`)
}
</script>

