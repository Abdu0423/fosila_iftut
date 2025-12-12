<template>
  <AdminLayout>
    <v-row>
      <v-col cols="12">
        <div class="d-flex justify-space-between align-center mb-4">
          <h1 class="text-h4">Редактировать студента</h1>
          <v-btn
            variant="outlined"
            :href="route('admin.students.index')"
            prepend-icon="mdi-arrow-left"
          >
            Назад к списку
          </v-btn>
        </div>
      </v-col>
    </v-row>

    <v-row>
      <v-col cols="12" md="8">
        <v-card>
          <v-card-title>Информация о студенте</v-card-title>
          <v-card-text>
            <v-form @submit.prevent="submit">
              <v-row>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="form.name"
                    label="Имя и фамилия"
                    :error-messages="form.errors.name"
                    required
                  ></v-text-field>
                </v-col>

                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="form.email"
                    label="Email"
                    type="email"
                    :error-messages="form.errors.email"
                    required
                  ></v-text-field>
                </v-col>

                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="form.password"
                    label="Новый пароль (оставьте пустым, если не хотите менять)"
                    type="password"
                    :error-messages="form.errors.password"
                  ></v-text-field>
                </v-col>

                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="form.password_confirmation"
                    label="Подтверждение нового пароля"
                    type="password"
                    :error-messages="form.errors.password_confirmation"
                  ></v-text-field>
                </v-col>

                <v-col cols="12" md="6">
                  <v-select
                    v-model="form.group_id"
                    label="Группа"
                    :items="groups"
                    item-title="name"
                    item-value="id"
                    :error-messages="form.errors.group_id"
                    required
                  ></v-select>
                </v-col>

                <v-col cols="12" md="6">
                  <v-select
                    v-model="form.course"
                    label="Курс"
                    :items="[1, 2, 3, 4, 5, 6]"
                    :error-messages="form.errors.course"
                    required
                  ></v-select>
                </v-col>

                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="form.phone"
                    label="Телефон"
                    :error-messages="form.errors.phone"
                  ></v-text-field>
                </v-col>

                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="form.student_id"
                    label="Студенческий билет"
                    :error-messages="form.errors.student_id"
                  ></v-text-field>
                </v-col>

                <v-col cols="12">
                  <v-textarea
                    v-model="form.address"
                    label="Адрес"
                    :error-messages="form.errors.address"
                    rows="2"
                  ></v-textarea>
                </v-col>

                <v-col cols="12">
                  <v-switch
                    v-model="form.is_active"
                    label="Активный студент"
                    color="primary"
                  ></v-switch>
                </v-col>
              </v-row>

              <v-row>
                <v-col cols="12">
                  <div class="d-flex gap-2">
                    <v-btn
                      type="submit"
                      color="primary"
                      :loading="form.processing"
                      prepend-icon="mdi-content-save"
                    >
                      Сохранить изменения
                    </v-btn>
                    
                    <v-btn
                      variant="outlined"
                      :href="route('admin.students.show', student.id)"
                      :disabled="form.processing"
                    >
                      Отмена
                    </v-btn>
                  </div>
                </v-col>
              </v-row>
            </v-form>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" md="4">
        <v-card>
          <v-card-title>Информация о студенте</v-card-title>
          <v-card-text>
            <v-list>
              <v-list-item>
                <v-list-item-title>ID студента</v-list-item-title>
                <v-list-item-subtitle>{{ student.id }}</v-list-item-subtitle>
              </v-list-item>
              
              <v-list-item>
                <v-list-item-title>Дата регистрации</v-list-item-title>
                <v-list-item-subtitle>{{ formatDate(student.created_at) }}</v-list-item-subtitle>
              </v-list-item>
              
              <v-list-item>
                <v-list-item-title>Последнее обновление</v-list-item-title>
                <v-list-item-subtitle>{{ formatDate(student.updated_at) }}</v-list-item-subtitle>
              </v-list-item>
            </v-list>
          </v-card-text>
        </v-card>

        <v-card class="mt-4">
          <v-card-title>Действия</v-card-title>
          <v-card-text>
            <v-btn
              block
              color="info"
              :href="route('admin.students.show', student.id)"
              prepend-icon="mdi-eye"
              class="mb-2"
            >
              Просмотреть профиль
            </v-btn>
            
            <v-btn
              block
              color="error"
              variant="outlined"
              prepend-icon="mdi-delete"
              @click="deleteStudent"
            >
              Удалить студента
            </v-btn>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Диалог подтверждения удаления -->
    <v-dialog v-model="deleteDialog" max-width="400">
      <v-card>
        <v-card-title>Подтверждение удаления</v-card-title>
        <v-card-text>
          Вы действительно хотите удалить студента "{{ student.name }}"?
          Это действие нельзя отменить.
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn @click="deleteDialog = false">Отмена</v-btn>
          <v-btn color="error" @click="confirmDelete">Удалить</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import Layout from '../../Layout.vue'

const props = defineProps({
  student: Object,
  groups: Array,
})

const deleteDialog = ref(false)

const form = useForm({
  name: props.student.name,
  email: props.student.email,
  password: '',
  password_confirmation: '',
  group_id: props.student.group_id,
  course: props.student.course,
  phone: props.student.phone,
  student_id: props.student.student_id,
  address: props.student.address,
  is_active: props.student.is_active,
})

const submit = () => {
  form.put(route('admin.students.update', props.student.id))
}

const deleteStudent = () => {
  deleteDialog.value = true
}

const confirmDelete = () => {
  router.delete(route('admin.students.destroy', props.student.id), {
    onSuccess: () => {
      deleteDialog.value = false
    }
  })
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('ru-RU', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}
</script>
