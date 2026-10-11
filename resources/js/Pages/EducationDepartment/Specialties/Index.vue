<template>
  <Layout :role="getRole">
    <v-container fluid class="pa-6">
      <div class="mb-6">
        <h1 class="text-h4 font-weight-bold mb-2">
          {{ translations.education_department?.specialties_title || 'Специальности' }}
        </h1>
        <p class="text-body-1 text-medium-emphasis">
          {{ translations.education_department?.specialties_subtitle || 'Управление специальностями' }}
        </p>
      </div>

      <ExcelSheet
        :columns="columns"
        :rows="specialtyRows"
        can-add
        :search-placeholder="translations.messages?.search || 'Поиск'"
        :empty-text="translations.education_department?.no_specialties || 'Специальности не найдены'"
        :add-label="translations.messages?.add || 'Добавить'"
        @create="createSpecialty"
        @commit="updateSpecialty"
      >
        <template #cell-is_active="{ row }">
          <v-chip :color="row.is_active ? 'success' : 'error'" size="small" @click.stop="toggleStatus(row)">
            {{ row.is_active ? (translations.messages?.active || 'Активна') : (translations.messages?.inactive || 'Неактивна') }}
          </v-chip>
        </template>
        <template #cell-actions="{ row }">
          <v-btn icon variant="text" size="small" :color="row.is_active ? 'success' : 'error'" @click.stop="toggleStatus(row)">
            <v-icon>{{ row.is_active ? 'mdi-toggle-switch' : 'mdi-toggle-switch-off-outline' }}</v-icon>
          </v-btn>
          <v-btn icon variant="text" color="primary" size="small" @click.stop="router.visit(`/${prefix()}/specialties/${row.id}/edit`)">
            <v-icon>mdi-pencil</v-icon>
          </v-btn>
        </template>
      </ExcelSheet>
    </v-container>
  </Layout>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { usePage, router } from '@/compat/inertia'
import Layout from '../../Layout.vue'
import ExcelSheet from '@/Components/ExcelSheet.vue'

const page = usePage()
const translations = computed(() => page.props.translations || {})
const props = defineProps({
  specialties: { type: [Array, Object], default: () => [] },
  departments: { type: Array, default: () => [] }
})

const specialtyRows = computed(() => props.specialties?.data || props.specialties || [])
const prefix = () => window.location.pathname.startsWith('/registration') ? 'registration' : 'education'
const getRole = computed(() => prefix() === 'registration' ? 'registration_center' : 'education_department')
const departmentOptions = computed(() => (props.departments || []).map((item) => ({ title: item.name, value: item.id })))

const columns = computed(() => [
  { key: 'name', title: translations.value.education_department?.specialty_name || 'Название', editable: true, editor: 'text', inherit: true, required: true },
  { key: 'short_name', title: translations.value.education_department?.short_name || 'Короткое имя', editable: true, editor: 'text' },
  { key: 'code', title: 'Код', editable: true, editor: 'text', inherit: true },
  {
    key: 'department_id',
    title: 'Кафедра',
    editable: true,
    editor: 'select',
    inherit: true,
    options: departmentOptions.value,
    get: (row) => row.department_id,
    text: (row) => row.department?.name || ''
  },
  { key: 'duration_years', title: translations.value.education_department?.duration || 'Длительность', editable: true, editor: 'number' },
  {
    key: 'is_active',
    title: translations.value.messages?.status || 'Статус',
    editor: 'slot',
    text: (row) => row.is_active ? (translations.value.messages?.active || 'Активна') : (translations.value.messages?.inactive || 'Неактивна')
  },
  { key: 'actions', title: translations.value.messages?.actions || 'Действия', editor: 'slot', text: () => '' }
])

const payload = (source) => ({
  name: source.name,
  short_name: source.short_name || null,
  code: source.code || null,
  description: source.description || null,
  duration_years: source.duration_years ? Number(source.duration_years) : null,
  department_id: source.department_id || null,
  is_active: source.is_active ?? true
})

const createSpecialty = (draft) => {
  router.post(`/${prefix()}/specialties`, payload({ ...draft, is_active: true }))
}

const updateSpecialty = ({ row, column, value }) => {
  router.put(`/${prefix()}/specialties/${row.id}`, payload({ ...row, [column.key]: value }))
}

const toggleStatus = (item) => {
  const previous = item.is_active
  item.is_active = !item.is_active
  router.post(`/${prefix()}/specialties/${item.id}/toggle-status`, {}, {
    onError: () => {
      item.is_active = previous
    }
  })
}
</script>
