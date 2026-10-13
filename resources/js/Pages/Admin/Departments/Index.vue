<template>
  <Layout role="admin">
    <v-container fluid class="pa-6">
      <div class="d-flex justify-space-between align-center mb-6">
        <div>
          <h1 class="text-h4 font-weight-bold mb-2">Кафедры</h1>
          <p class="text-body-1 text-medium-emphasis">Список кафедр и добавление новых</p>
        </div>
        <v-btn color="primary" prepend-icon="mdi-plus" @click="router.visit('/admin/departments/create')">
          Добавить
        </v-btn>
      </div>

      <v-alert v-if="page.props.flash?.success" type="success" variant="tonal" class="mb-4">
        {{ page.props.flash.success }}
      </v-alert>
      <v-alert v-if="page.props.flash?.error" type="error" variant="tonal" class="mb-4">
        {{ page.props.flash.error }}
      </v-alert>

      <ExcelSheet
        :columns="columns"
        :rows="rows"
        can-add
        search-placeholder="Поиск по кафедре, факультету, пользователю"
        empty-text="Кафедры не найдены"
        add-label="Добавить"
        @create="createDepartment"
        @commit="updateDepartment"
      >
        <template #cell-is_active="{ row }">
          <v-chip :color="row.is_active ? 'success' : 'error'" size="small" @click.stop="toggleStatus(row)">
            {{ row.is_active ? 'Активна' : 'Неактивна' }}
          </v-chip>
        </template>
        <template #cell-actions="{ row }">
          <v-btn icon variant="text" size="small" :color="row.is_active ? 'success' : 'error'" @click.stop="toggleStatus(row)">
            <v-icon>{{ row.is_active ? 'mdi-toggle-switch' : 'mdi-toggle-switch-off-outline' }}</v-icon>
          </v-btn>
          <v-btn icon variant="text" size="small" @click.stop="router.visit(`/admin/departments/${row.id}`)">
            <v-icon>mdi-eye</v-icon>
          </v-btn>
          <v-btn icon variant="text" color="primary" size="small" @click.stop="router.visit(`/admin/departments/${row.id}/edit`)">
            <v-icon>mdi-pencil</v-icon>
          </v-btn>
          <v-btn icon variant="text" color="error" size="small" @click.stop="removeDepartment(row)">
            <v-icon>mdi-delete</v-icon>
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
const props = defineProps({
  departments: { type: Array, default: () => [] },
  faculties: { type: Array, default: () => [] },
  users: { type: Array, default: () => [] },
})

const rows = computed(() => props.departments || [])
const facultyOptions = computed(() => (props.faculties || []).map((item) => ({
  title: item.name,
  value: item.id,
})))

function personName(user) {
  if (!user) return ''
  return [user.last_name, user.name, user.middle_name].filter(Boolean).join(' ')
}

const userOptions = computed(() => (props.users || []).map((user) => ({
  title: personName(user),
  value: user.id,
})))

const columns = computed(() => [
  { key: 'name', title: 'Название', editable: true, editor: 'text', inherit: true, required: true, width: '360px' },
  { key: 'code', title: 'Код', editable: true, editor: 'text', inherit: true },
  {
    key: 'faculty_id',
    title: 'Факультет',
    editable: true,
    editor: 'select',
    inherit: true,
    options: facultyOptions.value,
    get: (row) => row.faculty_id,
    text: (row) => row.faculty?.name || '',
  },
  {
    key: 'user_id',
    title: 'Пользователь',
    editable: true,
    editor: 'select',
    options: userOptions.value,
    get: (row) => row.user_id,
    text: (row) => personName(row.user),
  },
  { key: 'groups_count', title: 'Группы', text: (row) => String(row.groups_count || 0) },
  { key: 'specialties_count', title: 'Специальности', text: (row) => String(row.specialties_count || 0) },
  {
    key: 'is_active',
    title: 'Статус',
    editor: 'slot',
    text: (row) => row.is_active ? 'Активна' : 'Неактивна',
  },
  { key: 'actions', title: 'Действия', editor: 'slot', text: () => '' },
])

const payload = (source) => ({
  name: source.name,
  code: source.code || null,
  description: source.description || null,
  faculty_id: source.faculty_id || null,
  user_id: source.user_id || null,
  is_active: source.is_active ?? true,
})

const createDepartment = (draft) => {
  router.post('/admin/departments', payload({ ...draft, is_active: true }))
}

const updateDepartment = ({ row, column, value }) => {
  router.put(`/admin/departments/${row.id}`, payload({ ...row, [column.key]: value }))
}

const removeDepartment = (row) => {
  if (!confirm(`Удалить кафедру «${row.name}»?`)) {
    return
  }
  router.delete(`/admin/departments/${row.id}`)
}

const toggleStatus = (item) => {
  const previous = item.is_active
  item.is_active = !item.is_active
  router.post(`/admin/departments/${item.id}/toggle-status`, {}, {
    onError: () => {
      item.is_active = previous
    },
  })
}
</script>
