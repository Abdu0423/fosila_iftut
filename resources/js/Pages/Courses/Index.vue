<template>
  <Layout role="student">
    <div class="courses-page">
      <v-row>
        <v-col cols="12">
          <h1 class="text-h4 mb-6">Мои курсы</h1>
        </v-col>
      </v-row>
      
      <v-row>
        <v-col v-for="course in courses" :key="course.id" cols="12" md="6" lg="4">
          <v-card>
            <v-card-title>{{ course.name }}</v-card-title>
            <v-card-text>
              <p>{{ course.description }}</p>
              <v-chip color="primary" class="mt-2">{{ course.credits }} кредитов</v-chip>
            </v-card-text>
                         <v-card-actions>
               <v-btn color="primary" @click="openCourse(course.id)">
                 Перейти к курсу
               </v-btn>
             </v-card-actions>
          </v-card>
        </v-col>
      </v-row>
    </div>
  </Layout>
</template>

<script lang="ts">
import { router } from '@/compat/inertia'
import Layout from '../Layout.vue'

export default {
  name: 'CoursesIndex',
  components: {
    App
  },
  data() {
    return {
      courses: [
        {
          id: 1,
          name: 'Введение в программирование',
          description: 'Базовые концепции программирования',
          credits: 3
        },
        {
          id: 2,
          name: 'Веб-разработка',
          description: 'Создание современных веб-приложений',
          credits: 4
        }
      ]
    }
  },
  methods: {
    openCourse(courseId) {
      router.visit(window.route('courses.show', { course: courseId }))
    }
  }
}
</script>
