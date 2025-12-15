<template>
  <Layout role="student">
    <v-container fluid>
      <v-row>
        <v-col cols="12" class="text-center">
          <v-progress-circular indeterminate color="primary" size="64" class="mb-4"></v-progress-circular>
          <p class="text-body-1">{{ translations.dashboard?.loading || 'Загрузка...' }}</p>
        </v-col>
      </v-row>
    </v-container>
  </Layout>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import Layout from './Layout.vue'

const page = usePage()
const translations = computed(() => page.props.translations || {})

onMounted(() => {
  // Перенаправляем на Dashboard
  const user = page.props.auth?.user
  if (user) {
    if (user.role?.name === 'admin') {
      router.visit('/admin')
    } else if (user.role?.name === 'teacher') {
      router.visit('/teacher')
    } else if (user.role?.name === 'education_department') {
      router.visit('/education')
    } else {
      router.visit('/student')
    }
  } else {
    router.visit('/login')
  }
})
</script>

