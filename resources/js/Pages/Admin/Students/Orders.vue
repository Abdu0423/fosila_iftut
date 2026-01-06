<template>
  <Layout role="admin">
    <v-container fluid class="pa-6">
      <!-- Заголовок -->
      <div class="d-flex justify-space-between align-center mb-6">
        <div>
          <h1 class="text-h4 font-weight-bold mb-2">
            {{ translations.navigation?.orders || 'Приказы' }}
          </h1>
          <p class="text-body-1 text-medium-emphasis">
            {{ translations.messages?.orders_description || 'Управление приказами по студентам' }}
          </p>
        </div>
        <v-btn
          color="primary"
          prepend-icon="mdi-plus"
          @click="router.visit('/admin/students/orders/create')"
        >
          {{ translations.messages?.add || 'Добавить приказ' }}
        </v-btn>
      </div>

      <!-- Фильтры -->
      <v-card class="mb-6">
        <v-card-text>
          <v-row>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="filters.search"
                :label="translations.messages?.search || 'Поиск'"
                prepend-inner-icon="mdi-magnify"
                clearable
                variant="outlined"
                density="comfortable"
              ></v-text-field>
            </v-col>
            <v-col cols="12" md="3">
              <v-select
                v-model="filters.type"
                :label="translations.messages?.type || 'Тип приказа'"
                :items="orderTypes"
                clearable
                variant="outlined"
                density="comfortable"
              ></v-select>
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="filters.date_from"
                :label="translations.messages?.date_from || 'Дата с'"
                type="date"
                variant="outlined"
                density="comfortable"
              ></v-text-field>
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="filters.date_to"
                :label="translations.messages?.date_to || 'Дата по'"
                type="date"
                variant="outlined"
                density="comfortable"
              ></v-text-field>
            </v-col>
          </v-row>
        </v-card-text>
      </v-card>

      <!-- Список приказов -->
      <v-card>
        <v-card-text>
          <div class="text-center py-8">
            <v-icon size="64" color="grey-lighten-1">mdi-file-document-outline</v-icon>
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
  filters: {
    type: Object,
    default: () => ({})
  }
})

const filters = ref({
  search: props.filters.search || '',
  type: props.filters.type || null,
  date_from: props.filters.date_from || '',
  date_to: props.filters.date_to || ''
})

const orderTypes = [
  { title: 'О зачислении', value: 'enrollment' },
  { title: 'Об отчислении', value: 'expulsion' },
  { title: 'О переводе', value: 'transfer' },
  { title: 'О восстановлении', value: 'reinstatement' }
]
</script>

