<template>
  <Layout :role="getRole">
    <v-container fluid class="pa-6">
      <!-- Заголовок -->
      <div class="d-flex justify-space-between align-center mb-6">
        <div>
          <h1 class="text-h4 font-weight-bold mb-2">
            {{ translations.education_department?.schedules_title || 'Ҷадвалҳо' }}
          </h1>
          <p class="text-body-1 text-medium-emphasis">
            {{ translations.education_department?.schedules_subtitle || 'Тамошои ҳамаи ҷадвалҳо' }}
          </p>
        </div>
        <v-btn
          color="primary"
          prepend-icon="mdi-plus"
          @click="router.visit(`/${getRoutePrefix()}/schedules/create`)"
        >
          {{ translations.messages?.add || 'Илова кардан' }}
        </v-btn>
      </div>

      <!-- Фильтры -->
      <v-card class="mb-6">
        <v-card-text>
          <v-row>
            <v-col cols="12" md="6">
              <v-text-field
                v-model="dateFilter"
                :label="translations.education_department?.filter_by_date || 'Филтр аз рӯи сана'"
                type="date"
                prepend-inner-icon="mdi-calendar"
                clearable
                variant="outlined"
                density="comfortable"
                @update:model-value="handleDateFilter"
              ></v-text-field>
            </v-col>
          </v-row>
        </v-card-text>
      </v-card>

      <!-- Список расписаний -->
      <v-card>
        <v-card-text v-if="!schedules || !schedules.data || schedules.data.length === 0">
          <div class="text-center py-8">
            <v-icon size="64" color="grey-lighten-1">mdi-calendar-blank</v-icon>
            <p class="text-h6 text-medium-emphasis mt-4">
              {{ translations.education_department?.no_schedules || 'Ҷадвалҳо ёфт нашуданд' }}
            </p>
          </div>
        </v-card-text>

        <v-list v-else-if="schedules && schedules.data && schedules.data.length > 0">
          <template v-for="(schedule, index) in schedules.data" :key="schedule.id">
            <v-list-item>
              <template v-slot:prepend>
                <v-avatar color="primary">
                  <v-icon>mdi-calendar-clock</v-icon>
                </v-avatar>
              </template>

              <v-list-item-title class="font-weight-medium">
                {{ schedule.subject?.name || translations.education_department?.no_subject || 'Бе фан' }}
              </v-list-item-title>

              <v-list-item-subtitle>
                <div class="d-flex flex-column mt-1">
                  <span>
                    <v-icon size="small" class="mr-1">mdi-account</v-icon>
                    {{ schedule.teacher?.name }} {{ schedule.teacher?.last_name }}
                  </span>
                  <span>
                    <v-icon size="small" class="mr-1">mdi-account-group</v-icon>
                    {{ schedule.group?.name }}
                  </span>
                  <span v-if="schedule.scheduled_at">
                    <v-icon size="small" class="mr-1">mdi-clock</v-icon>
                    {{ formatDate(schedule.scheduled_at) }}
                  </span>
                </div>
              </v-list-item-subtitle>
              
              <template v-slot:append>
                <v-btn
                  icon
                  variant="text"
                  color="primary"
                  size="small"
                  @click="router.visit(`/${getRoutePrefix()}/schedules/${schedule.id}/edit`)"
                  :title="translations.messages?.edit || 'Таҳрир кардан'"
                >
                  <v-icon>mdi-pencil</v-icon>
                </v-btn>
              </template>
            </v-list-item>

            <v-divider v-if="index < schedules.data.length - 1" :key="`divider-${schedule.id}`"></v-divider>
          </template>
        </v-list>

        <!-- Пагинация -->
        <v-divider v-if="schedules && schedules.data && schedules.data.length > 0"></v-divider>
        <div v-if="schedules && schedules.data && schedules.data.length > 0" class="d-flex justify-center pa-4">
          <v-pagination
            :length="totalPages"
            :model-value="schedules.current_page || 1"
            @update:model-value="handlePageChange"
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
    type: Object,
    default: () => ({
      data: [],
      total: 0,
      per_page: 20,
      current_page: 1
    })
  },
  filters: {
    type: Object,
    default: () => ({})
  }
})

const dateFilter = ref(props.filters.date || '')

const formatDate = (date) => {
  const locale = page.props.locale || 'ru'
  return new Date(date).toLocaleDateString(locale === 'tg' ? 'tg-TJ' : 'ru-RU')
}

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

const handleDateFilter = () => {
  router.get(`/${getRoutePrefix()}/schedules`, {
    date: dateFilter.value
  }, {
    preserveState: true,
    preserveScroll: true
  })
}

const handlePageChange = (pageNum) => {
  router.get(`/${getRoutePrefix()}/schedules`, {
    page: pageNum,
    date: dateFilter.value
  }, {
    preserveState: true,
    preserveScroll: true
  })
}

// Вычисляемое свойство для общего количества страниц
const totalPages = computed(() => Math.ceil((props.schedules.total || 0) / (props.schedules.per_page || 20)))

// Вычисляемое свойство для видимых страниц пагинации
// Если страниц <= 5, показываем все, иначе максимум 5
const totalVisible = computed(() => {
  const total = totalPages.value
  return total <= 5 ? total : 5
})
</script>

