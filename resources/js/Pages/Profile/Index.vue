<template>
  <Layout>
    <v-container fluid>
      <div class="profile-page">
        <v-row>
        <v-col cols="12">
          <h1 class="text-h4 mb-6">Мой профиль</h1>
        </v-col>
      </v-row>
      
      <v-row>
        <!-- Информация о пользователе -->
        <v-col cols="12" md="4">
          <v-card>
            <v-card-text class="text-center">
              <v-avatar size="120" class="mb-4">
                <v-img :src="user.avatar" v-if="user.avatar"></v-img>
                <v-icon size="120" v-else>mdi-account</v-icon>
              </v-avatar>
              
              <h2 class="text-h5 mb-2">{{ user.fullName }}</h2>
              <p class="text-grey mb-4">{{ user.email }}</p>
              
              <v-btn color="primary" @click="editProfile = true">
                <v-icon left>mdi-pencil</v-icon>
                Редактировать профиль
              </v-btn>
            </v-card-text>
          </v-card>
          
          <!-- Статистика -->
          <v-card class="mt-4">
            <v-card-title>Статистика</v-card-title>
            <v-card-text>
              <v-list dense>
                <v-list-item>
                  <v-list-item-icon>
                    <v-icon color="primary">mdi-book-open-variant</v-icon>
                  </v-list-item-icon>
                  <v-list-item-content>
                    <v-list-item-title>{{ user.stats.courses }} курсов</v-list-item-title>
                  </v-list-item-content>
                </v-list-item>
                
                <v-list-item>
                  <v-list-item-icon>
                    <v-icon color="success">mdi-check-circle</v-icon>
                  </v-list-item-icon>
                  <v-list-item-content>
                    <v-list-item-title>{{ user.stats.completedAssignments }} завершенных заданий</v-list-item-title>
                  </v-list-item-content>
                </v-list-item>
                
                <v-list-item>
                  <v-list-item-icon>
                    <v-icon color="info">mdi-star</v-icon>
                  </v-list-item-icon>
                  <v-list-item-content>
                    <v-list-item-title>Средний балл: {{ user.stats.averageGrade }}</v-list-item-title>
                  </v-list-item-content>
                </v-list-item>
                
                <v-list-item>
                  <v-list-item-icon>
                    <v-icon color="warning">mdi-calendar</v-icon>
                  </v-list-item-icon>
                  <v-list-item-content>
                    <v-list-item-title>{{ user.stats.daysActive }} дней в системе</v-list-item-title>
                  </v-list-item-content>
                </v-list-item>
              </v-list>
            </v-card-text>
          </v-card>
        </v-col>
        
        <!-- Детальная информация -->
        <v-col cols="12" md="8">
          <v-card>
            <v-card-title>Личная информация</v-card-title>
            <v-card-text>
              <v-row>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="user.firstName"
                    label="Имя"
                    outlined
                    dense
                    readonly
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="user.lastName"
                    label="Фамилия"
                    outlined
                    dense
                    readonly
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="user.email"
                    label="Email"
                    outlined
                    dense
                    readonly
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="user.phone"
                    label="Телефон"
                    outlined
                    dense
                    readonly
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="user.studentId"
                    label="Студенческий номер"
                    outlined
                    dense
                    readonly
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="user.group"
                    label="Группа"
                    outlined
                    dense
                    readonly
                  ></v-text-field>
                </v-col>
                <v-col cols="12">
                  <v-textarea
                    v-model="user.bio"
                    label="О себе"
                    outlined
                    dense
                    readonly
                    rows="3"
                  ></v-textarea>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>
          
          <!-- Активные курсы -->
          <v-card class="mt-4">
            <v-card-title>Активные курсы</v-card-title>
            <v-card-text>
              <v-list>
                <v-list-item v-for="course in user.activeCourses" :key="course.id">
                  <v-list-item-avatar>
                    <v-icon color="primary">mdi-book-open-variant</v-icon>
                  </v-list-item-avatar>
                  <v-list-item-content>
                    <v-list-item-title>{{ course.name }}</v-list-item-title>
                    <v-list-item-subtitle>{{ course.teacher }}</v-list-item-subtitle>
                  </v-list-item-content>
                  <v-list-item-action>
                    <v-chip :color="course.status === 'Активный' ? 'success' : 'warning'">
                      {{ course.status }}
                    </v-chip>
                  </v-list-item-action>
                </v-list-item>
              </v-list>
            </v-card-text>
          </v-card>
          
          <!-- Достижения -->
          <v-card class="mt-4">
            <v-card-title>Достижения</v-card-title>
            <v-card-text>
              <v-row>
                <v-col v-for="achievement in user.achievements" :key="achievement.id" cols="12" md="6">
                  <v-card outlined>
                    <v-card-text class="text-center">
                      <v-icon size="48" :color="achievement.color" class="mb-2">
                        {{ achievement.icon }}
                      </v-icon>
                      <h3 class="text-h6">{{ achievement.title }}</h3>
                      <p class="text-grey">{{ achievement.description }}</p>
                      <v-chip small :color="achievement.color" class="mt-2">
                        {{ achievement.date }}
                      </v-chip>
                    </v-card-text>
                  </v-card>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
      </div>
    </v-container>
    
    <!-- Диалог редактирования профиля -->
    <v-dialog v-model="editProfile" max-width="600">
      <v-card>
        <v-card-title>Редактировать профиль</v-card-title>
        <v-card-text>
          <v-form ref="form" v-model="valid">
            <v-row>
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="editForm.firstName"
                  label="Имя"
                  outlined
                  dense
                  :rules="[v => !!v]"
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="editForm.lastName"
                  label="Фамилия"
                  outlined
                  dense
                  :rules="[v => !!v]"
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="editForm.phone"
                  label="Телефон"
                  outlined
                  dense
                ></v-text-field>
              </v-col>
              <v-col cols="12">
                <v-textarea
                  v-model="editForm.bio"
                  label="О себе"
                  outlined
                  dense
                  rows="3"
                ></v-textarea>
              </v-col>
            </v-row>
          </v-form>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn text @click="editProfile = false">Отмена</v-btn>
          <v-btn color="primary" @click="saveProfile" :disabled="!valid">
            Сохранить
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </Layout>
</template>

<script>
import Layout from '../Layout.vue'

export default {
  name: 'ProfileIndex',
  components: {
    Layout
  },
  data() {
    return {
      editProfile: false,
      valid: false,
      user: {
        firstName: 'Иван',
        lastName: 'Иванов',
        fullName: 'Иван Иванов',
        email: 'ivan.ivanov@example.com',
        phone: '+7 (999) 123-45-67',
        studentId: '2024-001',
        group: 'ИС-2024-1',
        bio: 'Студент факультета информационных систем. Интересуюсь веб-разработкой и программированием.',
        avatar: null,
        stats: {
          courses: 2,
          completedAssignments: 5,
          averageGrade: 4.3,
          daysActive: 45
        },
        activeCourses: [
          {
            id: 1,
            name: 'Введение в программирование',
            teacher: 'Иванов И.И.',
            status: 'Активный'
          },
          {
            id: 2,
            name: 'Веб-разработка',
            teacher: 'Петров П.П.',
            status: 'Активный'
          }
        ],
        achievements: [
          {
            id: 1,
            title: 'Первые шаги',
            description: 'Завершил первый курс',
            icon: 'mdi-trophy',
            color: 'success',
            date: '2024-01-15'
          },
          {
            id: 2,
            title: 'Отличник',
            description: 'Получил отличную оценку',
            icon: 'mdi-star',
            color: 'warning',
            date: '2024-01-20'
          }
        ]
      },
      editForm: {
        firstName: '',
        lastName: '',
        phone: '',
        bio: ''
      }
    }
  },
  methods: {
    saveProfile() {
      if (this.$refs.form.validate()) {
        // Здесь будет логика сохранения профиля
        this.user.firstName = this.editForm.firstName
        this.user.lastName = this.editForm.lastName
        this.user.fullName = `${this.editForm.firstName} ${this.editForm.lastName}`
        this.user.phone = this.editForm.phone
        this.user.bio = this.editForm.bio
        
        this.editProfile = false
      }
    }
  },
  mounted() {
    // Инициализация формы редактирования
    this.editForm = {
      firstName: this.user.firstName,
      lastName: this.user.lastName,
      phone: this.user.phone,
      bio: this.user.bio
    }
  }
}
</script>
