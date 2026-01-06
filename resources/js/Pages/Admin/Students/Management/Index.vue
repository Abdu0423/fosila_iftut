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
              <v-select
                v-model="groupFilter"
                :label="translations.education_department?.groups_menu || 'Группа'"
                :items="groups || []"
                item-title="name"
                item-value="id"
                clearable
                variant="outlined"
                density="comfortable"
                @update:model-value="handleSearch"
              ></v-select>
            </v-col>
          </v-row>
        </v-card-text>
      </v-card>

      <!-- Список студентов -->
      <v-card>
        <v-card-text v-if="students.data.length === 0">
          <div class="text-center py-8">
            <v-icon size="64" color="grey-lighten-1">mdi-school-outline</v-icon>
            <p class="text-h6 text-medium-emphasis mt-4">
              {{ translations.messages?.no_data || 'Студенты не найдены' }}
            </p>
          </div>
        </v-card-text>

        <v-data-table
          v-else
          :headers="headers"
          :items="students.data"
          :items-per-page="students.per_page"
          hide-default-footer
        >
          <template v-slot:item.name="{ item }">
            <div class="d-flex align-center py-2">
              <v-avatar color="primary" size="40" class="mr-3">
                <v-icon color="white">mdi-account</v-icon>
              </v-avatar>
              <div>
                <div class="font-weight-medium">{{ item.name }} {{ item.last_name }}</div>
                <div class="text-caption text-medium-emphasis">{{ item.email || item.phone || '—' }}</div>
              </div>
            </div>
          </template>

          <template v-slot:item.group="{ item }">
            <span v-if="item.group?.name" class="text-body-2">{{ item.group.name }}</span>
            <span v-else class="text-medium-emphasis">—</span>
          </template>

          <template v-slot:item.phone="{ item }">
            <span v-if="item.phone" class="text-body-2">{{ item.phone }}</span>
            <span v-else class="text-medium-emphasis">—</span>
          </template>

          <template v-slot:item.actions="{ item }">
            <v-btn
              icon
              variant="text"
              color="primary"
              size="small"
              @click="router.visit(`/admin/users/${item.id}/edit`)"
              :title="translations.messages?.edit || 'Редактировать'"
            >
              <v-icon>mdi-pencil</v-icon>
            </v-btn>
          </template>
        </v-data-table>

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

<script setup>
import { ref, computed } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import Layout from '../../../Layout.vue'

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
const groupFilter = ref(props.filters.group_id || null)

const headers = computed(() => [
  { title: translations.value.messages?.name || 'Имя', key: 'name', sortable: false },
  { title: translations.value.education_department?.groups_menu || 'Группа', key: 'group', sortable: false },
  { title: translations.value.messages?.phone || 'Телефон', key: 'phone', sortable: false },
  { title: translations.value.messages?.actions || 'Действия', key: 'actions', sortable: false }
])

const handleSearch = () => {
  router.get('/admin/students', {
    search: searchQuery.value,
    group_id: groupFilter.value
  }, {
    preserveState: true,
    preserveScroll: true
  })
}

const handlePageChange = (pageNum) => {
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

