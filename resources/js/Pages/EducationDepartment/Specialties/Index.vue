<template>
  <Layout role="education_department">
    <v-container fluid class="pa-6">
      <!-- Заголовок -->
      <div class="d-flex justify-space-between align-center mb-6">
        <div>
          <h1 class="text-h4 font-weight-bold mb-2">
            {{ translations.education_department?.specialties_title || 'Специальности' }}
          </h1>
          <p class="text-body-1 text-medium-emphasis">
            {{ translations.education_department?.specialties_subtitle || 'Управление специальностями' }}
          </p>
        </div>
        <v-btn
          color="primary"
          prepend-icon="mdi-plus"
          @click="createSpecialty"
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

      <!-- Таблица специальностей -->
      <v-card>
        <v-data-table
          :headers="headers"
          :items="specialties.data"
          :items-per-page="specialties.per_page"
          hide-default-footer
        >
          <template v-slot:item.name="{ item }">
            <div class="d-flex align-center py-2">
              <v-avatar color="primary" size="40" class="mr-3">
                <v-icon color="white">mdi-school</v-icon>
              </v-avatar>
              <div>
                <div class="font-weight-medium">{{ item.name }}</div>
                <div class="text-caption text-medium-emphasis" v-if="item.code">{{ item.code }}</div>
              </div>
            </div>
          </template>

          <template v-slot:item.short_name="{ item }">
            <span class="font-weight-medium">{{ item.short_name || '-' }}</span>
          </template>

          <template v-slot:item.department="{ item }">
            <span class="font-weight-medium">{{ item.department?.name || '-' }}</span>
          </template>

          <template v-slot:item.duration_years="{ item }">
            <v-chip
              color="info"
              size="small"
            >
              {{ item.duration_years || '-' }} {{ translations.education_department?.years_short || 'лет' }}
            </v-chip>
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
                @click="editSpecialty(item)"
                :title="translations.messages?.edit || 'Редактировать'"
              ></v-btn>
            </div>
          </template>

          <template v-slot:no-data>
            <div class="text-center pa-8">
              <v-icon size="64" color="grey-lighten-1">mdi-school-outline</v-icon>
              <p class="text-h6 mt-4 text-medium-emphasis">
                {{ translations.education_department?.no_specialties || 'Специальности не найдены' }}
              </p>
            </div>
          </template>
        </v-data-table>

        <!-- Пагинация -->
        <v-divider></v-divider>
        <div class="d-flex justify-center pa-4">
          <v-pagination
            :length="Math.ceil(specialties.total / specialties.per_page)"
            :model-value="specialties.current_page"
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
  specialties: {
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
  { title: translations.value.education_department?.specialty_name || 'Название', key: 'name', sortable: false },
  { title: translations.value.education_department?.short_name || 'Короткое имя', key: 'short_name', sortable: false },
  { title: translations.value.messages?.category || 'Кафедра', key: 'department', sortable: false },
  { title: translations.value.education_department?.duration || 'Длительность', key: 'duration_years', sortable: false },
  { title: translations.value.education_department?.groups_count || 'Количество групп', key: 'groups_count', sortable: false },
  { title: translations.value.messages?.status || 'Статус', key: 'is_active', sortable: false },
  { title: translations.value.messages?.actions || 'Действия', key: 'actions', sortable: false }
])

const statusOptions = [
  { title: translations.value.messages?.active || 'Активна', value: true },
  { title: translations.value.messages?.inactive || 'Неактивна', value: false }
]

const handleSearch = () => {
  router.get('/education/specialties', {
    search: searchQuery.value,
    status: statusFilter.value
  }, {
    preserveState: true,
    preserveScroll: true
  })
}

const handleStatusFilter = () => {
  router.get('/education/specialties', {
    search: searchQuery.value,
    status: statusFilter.value
  }, {
    preserveState: true,
    preserveScroll: true
  })
}

const handlePageChange = (page) => {
  router.get('/education/specialties', {
    page: page,
    search: searchQuery.value,
    status: statusFilter.value
  }, {
    preserveState: true,
    preserveScroll: true
  })
}

const createSpecialty = () => {
  router.visit('/education/specialties/create')
}

const editSpecialty = (specialty) => {
  router.visit(`/education/specialties/${specialty.id}/edit`)
}
</script>

