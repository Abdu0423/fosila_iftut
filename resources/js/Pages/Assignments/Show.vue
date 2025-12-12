<template>
  <Layout role="student">
    <div class="assignment-show-page">
      <v-row>
        <v-col cols="12">
          <v-breadcrumbs :items="breadcrumbs" class="mb-4"></v-breadcrumbs>
          <h1 class="text-h4 mb-6">{{ assignment.title }}</h1>
        </v-col>
      </v-row>
      
      <v-row>
        <v-col cols="12" md="8">
          <v-card>
            <v-card-title>Описание задания</v-card-title>
            <v-card-text>
              <div v-html="assignment.description"></div>
              
              <v-divider class="my-4"></v-divider>
              
              <h3 class="text-h6 mb-3">Требования</h3>
              <v-list dense>
                <v-list-item v-for="requirement in assignment.requirements" :key="requirement.id">
                  <v-list-item-icon>
                    <v-icon color="primary">mdi-check-circle</v-icon>
                  </v-list-item-icon>
                  <v-list-item-content>
                    <v-list-item-title>{{ requirement.text }}</v-list-item-title>
                  </v-list-item-content>
                </v-list-item>
              </v-list>
              
              <v-divider class="my-4"></v-divider>
              
              <h3 class="text-h6 mb-3">Материалы</h3>
              <v-list dense>
                <v-list-item v-for="material in assignment.materials" :key="material.id">
                  <v-list-item-icon>
                    <v-icon color="secondary">mdi-file-document</v-icon>
                  </v-list-item-icon>
                  <v-list-item-content>
                    <v-list-item-title>{{ material.name }}</v-list-item-title>
                    <v-list-item-subtitle>{{ material.type }}</v-list-item-subtitle>
                  </v-list-item-content>
                  <v-list-item-action>
                    <v-btn icon small>
                      <v-icon>mdi-download</v-icon>
                    </v-btn>
                  </v-list-item-action>
                </v-list-item>
              </v-list>
            </v-card-text>
          </v-card>
        </v-col>
        
        <v-col cols="12" md="4">
          <v-card>
            <v-card-title>Информация о задании</v-card-title>
            <v-card-text>
              <v-list dense>
                <v-list-item>
                  <v-list-item-icon>
                    <v-icon>mdi-book</v-icon>
                  </v-list-item-icon>
                  <v-list-item-content>
                    <v-list-item-title>Курс</v-list-item-title>
                    <v-list-item-subtitle>{{ assignment.course }}</v-list-item-subtitle>
                  </v-list-item-content>
                </v-list-item>
                
                <v-list-item>
                  <v-list-item-icon>
                    <v-icon>mdi-calendar</v-icon>
                  </v-list-item-icon>
                  <v-list-item-content>
                    <v-list-item-title>Срок сдачи</v-list-item-title>
                    <v-list-item-subtitle>{{ assignment.dueDate }}</v-list-item-subtitle>
                  </v-list-item-content>
                </v-list-item>
                
                <v-list-item>
                  <v-list-item-icon>
                    <v-icon>mdi-weight</v-icon>
                  </v-list-item-icon>
                  <v-list-item-content>
                    <v-list-item-title>Вес в оценке</v-list-item-title>
                    <v-list-item-subtitle>{{ assignment.weight }}%</v-list-item-subtitle>
                  </v-list-item-content>
                </v-list-item>
                
                <v-list-item>
                  <v-list-item-icon>
                    <v-icon>mdi-account</v-icon>
                  </v-list-item-icon>
                  <v-list-item-content>
                    <v-list-item-title>Преподаватель</v-list-item-title>
                    <v-list-item-subtitle>{{ assignment.teacher }}</v-list-item-subtitle>
                  </v-list-item-content>
                </v-list-item>
              </v-list>
            </v-card-text>
          </v-card>
          
          <v-card class="mt-4">
            <v-card-title>Сдать работу</v-card-title>
            <v-card-text>
              <v-file-input
                v-model="submissionFile"
                label="Выберите файл"
                accept=".pdf,.doc,.docx,.zip,.rar"
                prepend-icon="mdi-file-upload"
              ></v-file-input>
              
              <v-textarea
                v-model="submissionComment"
                label="Комментарий (необязательно)"
                rows="3"
              ></v-textarea>
              
              <v-btn
                color="primary"
                block
                :disabled="!submissionFile"
                @click="submitAssignment"
              >
                Сдать работу
              </v-btn>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </div>
  </Layout>
</template>

<script>
import Layout from '../Layout.vue'

export default {
  name: 'AssignmentShow',
  components: {
    App
  },
  props: {
    assignment: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      submissionFile: null,
      submissionComment: '',
      assignment: {
        id: this.assignment?.id || 1,
        title: this.assignment?.title,
        description: this.assignment?.description,
        course: this.assignment?.course,
        dueDate: this.assignment?.dueDate,
        weight: this.assignment?.weight || 15,
        teacher: this.assignment?.teacher,
        requirements: [
          { id: 1, text: 'Программа должна поддерживать операции +, -, *, /' },
          { id: 2, text: 'Должна быть обработка ошибок деления на ноль' },
          { id: 3, text: 'Интерфейс должен быть понятным для пользователя' }
        ],
        materials: [
          { id: 1, name: 'Учебник по Python', type: 'PDF' },
          { id: 2, name: 'Примеры кода', type: 'ZIP' }
        ]
      }
    }
  },
  computed: {
    breadcrumbs() {
      return [
        { title: 'Задания', href: this.$route('assignments.index') },
        { title: this.assignment.title, disabled: true }
      ]
    }
  },
  methods: {
    submitAssignment() {
      // Здесь будет логика отправки задания
      console.log('Отправка задания:', {
        file: this.submissionFile,
        comment: this.submissionComment
      })
    }
  }
}
</script>
