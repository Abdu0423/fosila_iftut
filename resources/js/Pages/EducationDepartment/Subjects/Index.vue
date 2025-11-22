<template>
  <Layout role="education_department">
    <v-container fluid class="pa-6">
      <!-- Заголовок -->
      <div class="mb-6">
        <h1 class="text-h4 font-weight-bold mb-2">
          {{ translations.education_department?.subjects_title || 'Предметы' }}
        </h1>
        <p class="text-body-1 text-medium-emphasis">
          {{ translations.education_department?.subjects_subtitle || 'Список всех предметов' }}
        </p>
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
      <v-row>
        <v-col
          v-for="subject in subjects.data"
          :key="subject.id"
          cols="12"
          md="6"
          lg="4"
        >
          <v-card class="h-100">
            <v-card-text>
              <div class="d-flex align-center mb-3">
                <v-avatar color="primary" size="48" class="mr-3">
                  <v-icon size="28">mdi-book-open-page-variant</v-icon>
                </v-avatar>
                <div class="flex-grow-1">
                  <h3 class="text-h6 font-weight-medium">{{ subject.name }}</h3>
                  <p v-if="subject.code" class="text-caption text-medium-emphasis">
                    {{ subject.code }}
                  </p>
                </div>
              </div>

              <p v-if="subject.description" class="text-body-2 text-medium-emphasis">
                {{ subject.description }}
              </p>
            </v-card-text>
          </v-card>
        </v-col>

        <!-- Пустое состояние -->
        <v-col v-if="subjects.data.length === 0" cols="12">
          <v-card>
            <v-card-text>
              <div class="text-center py-8">
                <v-icon size="64" color="grey-lighten-1">mdi-book-open-page-variant-outline</v-icon>
                <p class="text-h6 text-medium-emphasis mt-4">
                  {{ translations.education_department?.no_subjects || 'Предметы не найдены' }}
                </p>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Пагинация -->
      <div v-if="subjects.data.length > 0" class="d-flex justify-center mt-6">
        <v-pagination
          :length="Math.ceil(subjects.total / subjects.per_page)"
          :model-value="subjects.current_page"
          @update:model-value="handlePageChange"
        ></v-pagination>
      </div>
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

const searchQuery = ref(props.filters.search || '')

const handleSearch = () => {
  router.get(route('education.subjects.index'), {
    search: searchQuery.value
  }, {
    preserveState: true,
    preserveScroll: true
  })
}

const handlePageChange = (pageNum) => {
  router.get(route('education.subjects.index'), {
    page: pageNum,
    search: searchQuery.value
  }, {
    preserveState: true,
    preserveScroll: true
  })
}
</script>

