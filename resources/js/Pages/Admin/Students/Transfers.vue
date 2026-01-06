<template>
  <Layout role="admin">
    <v-container fluid class="pa-6">
      <!-- Заголовок -->
      <div class="d-flex justify-space-between align-center mb-6">
        <div>
          <h1 class="text-h4 font-weight-bold mb-2">
            {{ translations.navigation?.transfers || 'Переводы' }}
          </h1>
          <p class="text-body-1 text-medium-emphasis">
            {{ translations.messages?.transfers_description || 'Управление переводами студентов между группами' }}
          </p>
        </div>
        <v-btn
          color="primary"
          prepend-icon="mdi-swap-horizontal"
          @click="router.visit('/admin/students/transfers/create')"
        >
          {{ translations.messages?.add || 'Создать перевод' }}
        </v-btn>
      </div>

      <!-- Фильтры -->
      <v-card class="mb-6">
        <v-card-text>
          <v-row>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="filters.search"
                :label="translations.messages?.search || 'Поиск'"
                prepend-inner-icon="mdi-magnify"
                clearable
                variant="outlined"
                density="comfortable"
              ></v-text-field>
            </v-col>
            <v-col cols="12" md="4">
              <v-select
                v-model="filters.group_id"
                :label="translations.education_department?.groups_menu || 'Группа'"
                :items="groups || []"
                item-title="name"
                item-value="id"
                clearable
                variant="outlined"
                density="comfortable"
              ></v-select>
            </v-col>
          </v-row>
        </v-card-text>
      </v-card>

      <!-- Список переводов -->
      <v-card>
        <v-card-text>
          <div class="text-center py-8">
            <v-icon size="64" color="grey-lighten-1">mdi-swap-horizontal-variant</v-icon>
            <p class="text-h6 text-medium-emphasis mt-4">
              {{ translations.messages?.coming_soon || 'Функционал в разработке' }}
            </p>
          </div>
        </v-card-text>
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
  groups: {
    type: Array,
    default: () => []
  },
  filters: {
    type: Object,
    default: () => ({})
  }
})

const filters = ref({
  search: props.filters.search || '',
  group_id: props.filters.group_id || null
})
</script>

