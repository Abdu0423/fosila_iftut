<template>
  <Layout>
    <v-container fluid>
      <div class="course-show-page">
        <v-row>
        <v-col cols="12">
          <v-breadcrumbs :items="breadcrumbs" class="mb-4"></v-breadcrumbs>
          <h1 class="text-h4 mb-6">{{ course.name }}</h1>
        </v-col>
      </v-row>
      
      <v-row>
        <v-col cols="12" md="8">
          <v-card>
            <v-card-title>Описание курса</v-card-title>
            <v-card-text>
              <p>{{ course.description }}</p>
              <v-divider class="my-4"></v-divider>
              <h3 class="text-h6 mb-3">Учебные материалы</h3>
              <v-list>
                <v-list-item v-for="material in course.materials" :key="material.id">
                  <v-list-item-title>{{ material.title }}</v-list-item-title>
                  <v-list-item-subtitle>{{ material.type }}</v-list-item-subtitle>
                </v-list-item>
              </v-list>
            </v-card-text>
          </v-card>
        </v-col>
        
        <v-col cols="12" md="4">
          <v-card>
            <v-card-title>Информация о курсе</v-card-title>
            <v-card-text>
              <v-list>
                <v-list-item>
                  <v-list-item-title>Кредиты</v-list-item-title>
                  <v-list-item-subtitle>{{ course.credits }}</v-list-item-subtitle>
                </v-list-item>
                <v-list-item>
                  <v-list-item-title>Преподаватель</v-list-item-title>
                  <v-list-item-subtitle>{{ course.teacher }}</v-list-item-subtitle>
                </v-list-item>
                <v-list-item>
                  <v-list-item-title>Статус</v-list-item-title>
                  <v-list-item-subtitle>
                    <v-chip color="success">Активный</v-chip>
                  </v-list-item-subtitle>
                </v-list-item>
              </v-list>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
      </div>
    </v-container>
  </Layout>
</template>

<script>
import Layout from '../Layout.vue'

export default {
  name: 'CourseShow',
  components: {
    Layout
  },
  props: {
    course: {
      type: Object,
      required: true
    }
  },
  computed: {
    breadcrumbs() {
      return [
        { title: 'Курсы', href: this.$route('courses.index') },
        { title: this.course.name, disabled: true }
      ]
    }
  },
  data() {
    return {
      course: {
        id: this.course?.id || 1,
        name: this.course?.name,
        description: this.course?.description,
        credits: this.course?.credits || 3,
        teacher: this.course?.teacher,
        materials: [
          { id: 1, title: 'Лекция 1: Введение', type: 'Видео' },
          { id: 2, title: 'Практическая работа 1', type: 'Задание' },
          { id: 3, title: 'Учебник по Python', type: 'Документ' }
        ]
      }
    }
  }
}
</script>
