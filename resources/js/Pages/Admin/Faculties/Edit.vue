<template>
  <Layout role="admin">
    <v-container fluid class="pa-6">
      <div class="d-flex justify-space-between align-center mb-6">
        <div>
          <h1 class="text-h4 font-weight-bold mb-2">Редактирование факультета</h1>
          <p class="text-body-1 text-medium-emphasis">{{ faculty.name }}</p>
        </div>
        <v-btn variant="outlined" prepend-icon="mdi-arrow-left" @click="router.visit('/admin/faculties')">
          Назад
        </v-btn>
      </div>

      <v-row justify="center">
        <v-col cols="12" md="10" lg="8">
          <v-card>
            <v-card-text class="pa-6">
              <v-form @submit.prevent="submit">
                <FacultyFields :form="form" :departments="departments" :users="users" />
                <div class="d-flex justify-end ga-3">
                  <v-btn variant="outlined" @click="router.visit(`/admin/faculties/${faculty.id}`)">Отмена</v-btn>
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
import FacultyFields from './FacultyFields.vue'

const props = defineProps({
  faculty: { type: Object, required: true },
  departments: { type: Array, default: () => [] },
  users: { type: Array, default: () => [] },
})

const form = useForm({
  name: props.faculty.name || '',
  code: props.faculty.code || '',
  department_id: props.faculty.departments?.[0]?.id || null,
  user_id: props.faculty.user_id || null,
  address: props.faculty.address || '',
  description: props.faculty.description || '',
  is_active: props.faculty.is_active !== false,
})

const submit = () => {
  form.put(`/admin/faculties/${props.faculty.id}`)
}
</script>
