<template>
  <Layout role="admin">
    <v-container fluid class="pa-6">
      <div class="d-flex justify-space-between align-center mb-6">
        <div class="d-flex align-center">
          <v-btn icon="mdi-arrow-left" variant="text" class="mr-2" @click="router.visit('/admin/faculties')" />
          <div>
            <h1 class="text-h4 font-weight-bold mb-2">{{ faculty.name }}</h1>
            <p class="text-body-1 text-medium-emphasis">Код {{ faculty.code || '—' }}</p>
          </div>
        </div>
        <div class="d-flex ga-2">
          <v-btn variant="outlined" prepend-icon="mdi-pencil" @click="router.visit(`/admin/faculties/${faculty.id}/edit`)">
            Редактировать
          </v-btn>
          <v-btn color="error" variant="outlined" prepend-icon="mdi-delete" @click="remove">
            Удалить
          </v-btn>
        </div>
      </div>

      <v-alert v-if="page.props.flash?.success" type="success" variant="tonal" class="mb-4">
        {{ page.props.flash.success }}
      </v-alert>
      <v-alert v-if="page.props.flash?.error" type="error" variant="tonal" class="mb-4">
        {{ page.props.flash.error }}
      </v-alert>

      <v-row class="mb-6">
        <v-col cols="12" md="3">
          <v-card><v-card-text><div class="text-caption text-medium-emphasis">Декан</div><div class="text-h6">{{ faculty.dean_name || '—' }}</div></v-card-text></v-card>
        </v-col>
        <v-col cols="12" md="3">
          <v-card><v-card-text><div class="text-caption text-medium-emphasis">Телефон</div><div class="text-h6">{{ faculty.phone || '—' }}</div></v-card-text></v-card>
        </v-col>
        <v-col cols="12" md="3">
          <v-card><v-card-text><div class="text-caption text-medium-emphasis">Кафедры</div><div class="text-h6">{{ faculty.departments?.length || 0 }}</div></v-card-text></v-card>
        </v-col>
        <v-col cols="12" md="3">
          <v-card><v-card-text><div class="text-caption text-medium-emphasis">Статус</div><v-chip :color="faculty.is_active ? 'success' : 'error'" size="small">{{ faculty.is_active ? 'Активен' : 'Неактивен' }}</v-chip></v-card-text></v-card>
        </v-col>
      </v-row>

      <v-card v-if="faculty.description || faculty.address || faculty.dean_email" class="mb-6">
        <v-card-text>
          <div v-if="faculty.dean_email">Почта: {{ faculty.dean_email }}</div>
          <div v-if="faculty.address">Адрес: {{ faculty.address }}</div>
          <div v-if="faculty.description" class="mt-2">{{ faculty.description }}</div>
        </v-card-text>
      </v-card>

      <h2 class="text-h6 mb-3">Кафедры факультета</h2>
      <ExcelSheet
        :headers="departmentHeaders"
        :rows="faculty.departments || []"
        empty-text="Кафедр нет"
      >
        <template #cell-name="{ row }">
          <a class="sheet-link" @click.prevent="router.visit(`/admin/departments/${row.id}`)">{{ row.name }}</a>
        </template>
      </ExcelSheet>
    </v-container>
  </Layout>
</template>

<script setup lang="ts">
import { usePage, router } from '@/compat/inertia'
import Layout from '../../Layout.vue'
import ExcelSheet from '@/Components/ExcelSheet.vue'

const page = usePage()
const props = defineProps({
  faculty: { type: Object, required: true },
})

const departmentHeaders = [
  { title: 'Кафедра', key: 'name' },
  { title: 'Код', key: 'code' },
  { title: 'Группы', key: 'groups_count' },
  { title: 'Специальности', key: 'specialties_count' },
]

const remove = () => {
  if (!confirm('Удалить факультет?')) {
    return
  }
  router.delete(`/admin/faculties/${props.faculty.id}`)
}
</script>

<style scoped>
.sheet-link {
  color: #3d4fd8;
  cursor: pointer;
  text-decoration: none;
}
</style>
