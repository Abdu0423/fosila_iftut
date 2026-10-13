<template>
  <Layout role="student">
    <v-container fluid>
      <v-row>
        <v-col cols="12" class="text-center">
          <v-progress-circular indeterminate color="primary" size="64" class="mb-4"></v-progress-circular>
          <p class="text-body-1">{{ t('messages.info') }}</p>
        </v-col>
      </v-row>
    </v-container>
  </Layout>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { router, usePage } from '@/compat/inertia'
import { useI18n } from 'vue-i18n'
import Layout from './Layout.vue'

const page = usePage()
const { t } = useI18n()

onMounted(() => {
  // Перенаправляем на Dashboard
  const user = page.props.auth?.user
  if (user) {
    const homes: Record<string, string> = {
      admin: '/admin',
      teacher: '/teacher',
      education_department: '/education',
      registration_center: '/registration',
      department: '/department',
      faculty: '/faculty',
      student: '/student',
    }
    router.visit(homes[user.role?.name] || '/student')
  } else {
    router.visit('/login')
  }
})
</script>

