<template>
  <Layout :role="getRole">
    <v-container fluid class="pa-6">
      <div class="d-flex justify-space-between align-center mb-6">
        <div>
          <h1 class="text-h4 font-weight-bold mb-2">
            {{ translations.education_department?.groups_title || 'Гурӯҳҳо' }}
          </h1>
          <p class="text-body-1 text-medium-emphasis">
            {{ translations.education_department?.groups_subtitle || 'Тамошои ҳамаи гурӯҳҳо' }}
          </p>
        </div>
      </div>

      <ExcelSheet
        :columns="columns"
        :rows="groupRows"
        can-add
        :search-placeholder="translations.messages?.search || 'Поиск'"
        :empty-text="translations.education_department?.no_groups || 'Гурӯҳҳо ёфт нашуданд'"
        :add-label="translations.messages?.add || 'Добавить'"
        @create="createGroup"
        @commit="updateGroup"
      >
        <template #cell-status="{ row }">
          <v-chip
            :color="row.status === 'active' ? 'success' : 'error'"
            size="small"
            @click.stop="toggleStatus(row)"
          >
            {{ row.status === 'active' ? (translations.messages?.active || 'Фаъол') : (translations.messages?.inactive || 'Ғайрифаъол') }}
          </v-chip>
        </template>
        <template #cell-actions="{ row }">
          <v-btn icon variant="text" size="small" :color="row.status === 'active' ? 'success' : 'error'" @click.stop="toggleStatus(row)">
            <v-icon>{{ row.status === 'active' ? 'mdi-toggle-switch' : 'mdi-toggle-switch-off-outline' }}</v-icon>
          </v-btn>
          <v-btn icon variant="text" color="primary" size="small" @click.stop="router.visit(`/${prefix()}/groups/${row.id}/edit`)">
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
  groups: { type: [Array, Object], default: () => [] },
  departments: { type: Array, default: () => [] },
  specialties: { type: Array, default: () => [] }
})

const groupRows = computed(() => props.groups?.data || props.groups || [])
const prefix = () => window.location.pathname.startsWith('/registration') ? 'registration' : 'education'
const getRole = computed(() => prefix() === 'registration' ? 'registration_center' : 'education_department')

const departmentOptions = computed(() => (props.departments || []).map((item) => ({ title: item.name, value: item.id })))
const specialtyOptions = computed(() => (props.specialties || []).map((item) => ({ title: item.name, value: item.id })))
const statusOptions = [
  { title: 'Фаъол', value: 'active' },
  { title: 'Выпущена', value: 'graduated' },
  { title: 'Приостановлена', value: 'suspended' }
]

const columns = computed(() => [
  { key: 'name', title: translations.value.education_department?.group_name || 'Группа', editable: true, editor: 'text', inherit: true, required: true },
  { key: 'enrollment_year', title: 'Год набора', editable: true, editor: 'number', required: true },
  { key: 'graduation_year', title: 'Год выпуска', editable: true, editor: 'number', required: true },
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
  {
    key: 'specialty_id',
    title: 'Специальность',
    editable: true,
    editor: 'select',
    inherit: true,
    options: specialtyOptions.value,
    get: (row) => row.specialty_id,
    text: (row) => row.specialty?.name || ''
  },
  {
    key: 'status',
    title: translations.value.messages?.status || 'Статус',
    editor: 'slot',
    text: (row) => row.status === 'active' ? (translations.value.messages?.active || 'Фаъол') : (translations.value.messages?.inactive || 'Ғайрифаъол')
  },
  { key: 'actions', title: translations.value.messages?.actions || 'Действия', editor: 'slot', text: () => '' }
])

const payload = (source) => ({
  name: source.name,
  enrollment_year: Number(source.enrollment_year),
  graduation_year: Number(source.graduation_year),
  status: source.status || 'active',
  department_id: source.department_id || null,
  specialty_id: source.specialty_id || null,
  course: source.course || null
})

const createGroup = (draft) => {
  router.post(`/${prefix()}/groups`, payload({ ...draft, status: draft.status || 'active' }))
}

const updateGroup = ({ row, column, value }) => {
  router.put(`/${prefix()}/groups/${row.id}`, payload({ ...row, [column.key]: value }))
}

const toggleStatus = (item) => {
  const previous = item.status
  item.status = item.status === 'active' ? 'suspended' : 'active'
  router.post(`/${prefix()}/groups/${item.id}/toggle-status`, {}, {
    onError: () => {
      item.status = previous
    }
  })
}
</script>
