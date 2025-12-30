<template>
  <Layout role="education_department">
    <v-container fluid class="pa-6">
      <!-- Заголовок -->
      <div class="d-flex justify-space-between align-center mb-6">
        <div>
          <h1 class="text-h4 font-weight-bold mb-2">
            {{ translations.education_department?.subjects_title || 'Предметы' }}
          </h1>
          <p class="text-body-1 text-medium-emphasis">
            {{ translations.education_department?.subjects_subtitle || 'Список всех предметов' }}
          </p>
        </div>
        <v-btn
          color="primary"
          prepend-icon="mdi-plus"
          @click="router.visit('/education/subjects/create')"
        >
          {{ translations.messages?.add || 'Добавить' }}
        </v-btn>
      </div>

      <!-- Поиск -->
      <v-card class="mb-6">
        <v-card-text>
          <v-text-field
            v-model="searchQuery"
            :label="translations.messages?.search || 'Поиск'"
            prepend-inner-icon="mdi-magnify"
            clearable
            variant="outlined"
            density="comfortable"
            @update:model-value="handleSearch"
          ></v-text-field>
        </v-card-text>
      </v-card>

      <!-- Список предметов -->
      <v-card>
        <v-card-text v-if="subjects.data.length === 0">
          <div class="text-center py-8">
            <v-icon size="64" color="grey-lighten-1">mdi-book-open-page-variant-outline</v-icon>
            <p class="text-h6 text-medium-emphasis mt-4">
              {{ translations.education_department?.no_subjects || 'Предметы не найдены' }}
            </p>
          </div>
        </v-card-text>

        <v-data-table
          v-else
          :headers="headers"
          :items="subjects.data"
          :items-per-page="subjects.per_page"
          hide-default-footer
        >
          <template v-slot:item.name="{ item }">
            <div class="d-flex align-center py-2">
              <v-avatar color="primary" size="40" class="mr-3">
                <v-icon color="white">mdi-book-open-page-variant</v-icon>
              </v-avatar>
              <div>
                <div class="font-weight-medium">{{ item.name }}</div>
              </div>
            </div>
          </template>

          <template v-slot:item.description="{ item }">
            <span v-if="item.description" class="text-body-2">{{ item.description }}</span>
            <span v-else class="text-medium-emphasis">—</span>
          </template>

          <template v-slot:item.is_active="{ item }">
            <v-chip
              :color="item.is_active ? 'success' : 'default'"
              size="small"
            >
              {{ item.is_active ? (translations.messages?.active) : (translations.messages?.inactive) }}
            </v-chip>
          </template>

          <template v-slot:item.actions="{ item }">
            <v-btn
              icon
              variant="text"
              color="primary"
              size="small"
              @click="router.visit(`/education/subjects/${item.id}/edit`)"
              :title="translations.messages?.edit"
            >
              <v-icon>mdi-pencil</v-icon>
            </v-btn>
          </template>
        </v-data-table>

        <!-- Пагинация -->
        <v-divider v-if="subjects.data.length > 0"></v-divider>
        <div v-if="subjects.data.length > 0" class="d-flex justify-center pa-4">
          <v-pagination
            :length="totalPages"
            :model-value="subjects.current_page"
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
import Layout from '../../Layout.vue'

const page = usePage()
const translations = computed(() => page.props.translations || {})

const props = defineProps({
  subjects: {
    type: Object,
    required: true
  },
  filters: {
    type: Object,
    default: () => ({})
  }
})

const searchQuery = ref(props.filters.search)

const headers = computed(() => [
  { title: translations.value.education_department?.subject_name || 'Название', key: 'name', sortable: false },
  { title: translations.value.education_department?.subject_description || 'Описание', key: 'description', sortable: false },
  { title: translations.value.messages?.status || 'Статус', key: 'is_active', sortable: false },
  { title: translations.value.messages?.actions || 'Действия', key: 'actions', sortable: false }
])

const handleSearch = () => {
  router.get('/education/subjects', {
    search: searchQuery.value
  }, {
    preserveState: true,
    preserveScroll: true
  })
}

const handlePageChange = (pageNum) => {
  router.get('/education/subjects', {
    page: pageNum,
    search: searchQuery.value
  }, {
    preserveState: true,
    preserveScroll: true
  })
}

// Вычисляемое свойство для ограничения видимых страниц пагинации
// Показываем максимум 5 страниц: текущая + 1 предыдущая + 1 следующая + первая + последняя
const totalPages = computed(() => Math.ceil(subjects.total / subjects.per_page))
</script>

