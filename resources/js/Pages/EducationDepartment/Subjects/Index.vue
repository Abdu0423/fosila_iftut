<template>
  <Layout role="education_department">
    <v-container fluid class="pa-6">
      <!-- Заголовок -->
      <div class="d-flex justify-space-between align-center mb-6">
        <div>
          <h1 class="text-h4 font-weight-bold mb-2">
            {{ translations.education_department?.subjects_title || 'Фанҳо' }}
          </h1>
          <p class="text-body-1 text-medium-emphasis">
            {{ translations.education_department?.subjects_subtitle || 'Рӯйхати ҳамаи фанҳо' }}
          </p>
        </div>
        <v-btn
          color="primary"
          prepend-icon="mdi-plus"
          @click="router.visit(route('education.subjects.create'))"
        >
          {{ translations.messages?.add || 'Илова кардан' }}
        </v-btn>
      </div>

      <!-- Поиск -->
      <v-card class="mb-6">
        <v-card-text>
          <v-text-field
            v-model="searchQuery"
            :label="translations.messages?.search || 'Ҷустуҷӯ'"
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
              {{ translations.education_department?.no_subjects || 'Фанҳо ёфт нашуданд' }}
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
                <div v-if="item.code" class="text-caption text-medium-emphasis">{{ item.code }}</div>
              </div>
            </div>
          </template>

          <template v-slot:item.description="{ item }">
            <span v-if="item.description" class="text-body-2">{{ item.description }}</span>
            <span v-else class="text-medium-emphasis">—</span>
          </template>

          <template v-slot:item.credits="{ item }">
            <span v-if="item.credits">{{ item.credits }}</span>
            <span v-else class="text-medium-emphasis">—</span>
          </template>

          <template v-slot:item.is_active="{ item }">
            <v-chip
              :color="item.is_active ? 'success' : 'default'"
              size="small"
            >
              {{ item.is_active ? (translations.messages?.active || 'Фаъол') : (translations.messages?.inactive || 'Ғайрифаъол') }}
            </v-chip>
          </template>

          <template v-slot:item.actions="{ item }">
            <v-btn
              icon
              variant="text"
              color="primary"
              size="small"
              @click="router.visit(route('education.subjects.edit', item.id))"
              :title="translations.messages?.edit || 'Таҳрир кардан'"
            >
              <v-icon>mdi-pencil</v-icon>
            </v-btn>
          </template>
        </v-data-table>

        <!-- Пагинация -->
        <v-divider v-if="subjects.data.length > 0"></v-divider>
        <div v-if="subjects.data.length > 0" class="d-flex justify-center pa-4">
          <v-pagination
            :length="Math.ceil(subjects.total / subjects.per_page)"
            :model-value="subjects.current_page"
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

const headers = computed(() => [
  { title: translations.value.education_department?.subject_name || 'Номи фан', key: 'name', sortable: false },
  { title: translations.value.education_department?.subject_code || 'Коди фан', key: 'code', sortable: false },
  { title: translations.value.education_department?.subject_description || 'Тавсиф', key: 'description', sortable: false },
  { title: translations.value.education_department?.subject_credits || 'Кредитҳо', key: 'credits', sortable: false },
  { title: translations.value.messages?.status || 'Ҳолат', key: 'is_active', sortable: false },
  { title: translations.value.messages?.actions || 'Амалҳо', key: 'actions', sortable: false }
])

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

