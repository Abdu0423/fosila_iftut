<template>
  <Layout role="admin">
    <v-container fluid class="pa-6">
      <div class="d-flex justify-space-between align-center mb-6">
        <div class="d-flex align-center">
          <v-btn icon="mdi-arrow-left" variant="text" class="mr-2" @click="router.visit('/admin/departments')" />
          <div>
            <h1 class="text-h4 font-weight-bold mb-2">{{ department.name }}</h1>
            <p class="text-body-1 text-medium-emphasis">Код {{ department.code || '—' }}</p>
          </div>
        </div>
        <div class="d-flex ga-2">
          <v-btn variant="outlined" prepend-icon="mdi-pencil" @click="router.visit(`/admin/departments/${department.id}/edit`)">
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
          <v-card><v-card-text><div class="text-caption text-medium-emphasis">Факультет</div><div class="text-h6">{{ department.faculty?.name || '—' }}</div></v-card-text></v-card>
        </v-col>
        <v-col cols="12" md="3">
          <v-card><v-card-text><div class="text-caption text-medium-emphasis">Пользователь</div><div class="text-h6">{{ personName(department.user) || '—' }}</div></v-card-text></v-card>
        </v-col>
        <v-col cols="12" md="3">
          <v-card><v-card-text><div class="text-caption text-medium-emphasis">Группы</div><div class="text-h6">{{ department.groups_count || 0 }}</div></v-card-text></v-card>
        </v-col>
        <v-col cols="12" md="3">
          <v-card><v-card-text><div class="text-caption text-medium-emphasis">Специальности</div><div class="text-h6">{{ department.specialties_count || 0 }}</div></v-card-text></v-card>
        </v-col>
        <v-col cols="12" md="3">
          <v-card><v-card-text><div class="text-caption text-medium-emphasis">Статус</div><v-chip :color="department.is_active ? 'success' : 'error'" size="small">{{ department.is_active ? 'Активна' : 'Неактивна' }}</v-chip></v-card-text></v-card>
        </v-col>
      </v-row>

      <v-card v-if="department.description" class="mb-6">
        <v-card-text>{{ department.description }}</v-card-text>
      </v-card>

      <h2 class="text-h6 mb-3">Специальности</h2>
      <ExcelSheet class="mb-6" :headers="specialtyHeaders" :rows="department.specialties || []" empty-text="Специальностей нет" />

      <h2 class="text-h6 mb-3">Группы</h2>
      <ExcelSheet class="mb-6" :headers="groupHeaders" :rows="department.groups || []" empty-text="Групп нет" />

      <h2 class="text-h6 mb-3">Предметы</h2>
      <ExcelSheet :headers="subjectHeaders" :rows="department.subjects || []" empty-text="Предметов нет" />
    </v-container>
  </Layout>
</template>

<script setup lang="ts">
import { usePage, router } from '@/compat/inertia'
import Layout from '../../Layout.vue'
import ExcelSheet from '@/Components/ExcelSheet.vue'

const page = usePage()
const props = defineProps({
  department: { type: Object, required: true },
})

function personName(user) {
  if (!user) return ''
  return [user.last_name, user.name, user.middle_name].filter(Boolean).join(' ')
}

const specialtyHeaders = [
  { title: 'Название', key: 'name' },
  { title: 'Код', key: 'code' },
]
const groupHeaders = [
  { title: 'Группа', key: 'name' },
  { title: 'Статус', key: 'status' },
]
const subjectHeaders = [
  { title: 'Предмет', key: 'name' },
]

const remove = () => {
  if (!confirm('Удалить кафедру?')) {
    return
  }
  router.delete(`/admin/departments/${props.department.id}`)
}
</script>
