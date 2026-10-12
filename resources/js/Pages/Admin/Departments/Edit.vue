<template>
  <Layout role="admin">
    <v-container fluid class="pa-6">
      <div class="d-flex justify-space-between align-center mb-6">
        <div>
          <h1 class="text-h4 font-weight-bold mb-2">Редактирование кафедры</h1>
          <p class="text-body-1 text-medium-emphasis">{{ department.name }}</p>
        </div>
        <v-btn variant="outlined" prepend-icon="mdi-arrow-left" @click="router.visit('/admin/departments')">
          Назад
        </v-btn>
      </div>

      <v-row justify="center">
        <v-col cols="12" md="10" lg="8">
          <v-card>
            <v-card-text class="pa-6">
              <v-form @submit.prevent="submit">
                <DepartmentFields :form="form" :faculties="faculties" />
                <div class="d-flex justify-end ga-3">
                  <v-btn variant="outlined" @click="router.visit(`/admin/departments/${department.id}`)">Отмена</v-btn>
                  <v-btn type="submit" color="primary" :loading="form.processing">Сохранить</v-btn>
                </div>
              </v-form>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </Layout>
</template>

<script setup lang="ts">
import { useForm, router } from '@/compat/inertia'
import Layout from '../../Layout.vue'
import DepartmentFields from './DepartmentFields.vue'

const props = defineProps({
  department: { type: Object, required: true },
  faculties: { type: Array, default: () => [] },
})

const form = useForm({
  name: props.department.name || '',
  code: props.department.code || '',
  faculty_id: props.department.faculty_id || null,
  description: props.department.description || '',
  is_active: props.department.is_active !== false,
})

const submit = () => {
  form.put(`/admin/departments/${props.department.id}`)
}
</script>
