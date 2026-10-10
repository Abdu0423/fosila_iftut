<template>
  <AdminLayout>
    <v-row>
      <v-col cols="12">
        <div class="d-flex justify-space-between align-center mb-4">
          <h1 class="text-h4">Добавить учителя</h1>
          <v-btn
            variant="outlined"
            :href="route('admin.teachers.index')"
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
          <v-card-title>Информация об учителе</v-card-title>
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
                    label="Пароль"
                    type="password"
                    :error-messages="form.errors.password"
                    required
                  ></v-text-field>
                </v-col>

                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="form.password_confirmation"
                    label="Подтверждение пароля"
                    type="password"
                    :error-messages="form.errors.password_confirmation"
                    required
                  ></v-text-field>
                </v-col>

                <v-col cols="12" md="6">
                  <v-select
                    v-model="form.department_id"
                    label="Кафедра"
                    :items="departments"
                    item-title="name"
                    item-value="id"
                    :error-messages="form.errors.department_id"
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

                <v-col cols="12">
                  <v-textarea
                    v-model="form.bio"
                    label="Биография"
                    :error-messages="form.errors.bio"
                    rows="3"
                  ></v-textarea>
                </v-col>

                <v-col cols="12">
                  <v-switch
                    v-model="form.is_active"
                    label="Активный учитель"
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
                      Создать учителя
                    </v-btn>
                    
                    <v-btn
                      variant="outlined"
                      :href="route('admin.teachers.index')"
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
          <v-card-title>Подсказки</v-card-title>
          <v-card-text>
            <v-list>
              <v-list-item prepend-icon="mdi-information">
                <v-list-item-title>Имя и фамилия</v-list-item-title>
                <v-list-item-subtitle>
                  Укажите полное имя учителя
                </v-list-item-subtitle>
              </v-list-item>
              
              <v-list-item prepend-icon="mdi-information">
                <v-list-item-title>Email</v-list-item-title>
                <v-list-item-subtitle>
                  Будет использоваться для входа в систему
                </v-list-item-subtitle>
              </v-list-item>
              
              <v-list-item prepend-icon="mdi-information">
                <v-list-item-title>Пароль</v-list-item-title>
                <v-list-item-subtitle>
                  Минимум 8 символов
                </v-list-item-subtitle>
              </v-list-item>
              
              <v-list-item prepend-icon="mdi-information">
                <v-list-item-title>Кафедра</v-list-item-title>
                <v-list-item-subtitle>
                  Выберите кафедру, к которой относится учитель
                </v-list-item-subtitle>
              </v-list-item>
            </v-list>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </AdminLayout>
</template>

<script setup lang="ts">
import { useForm } from '@/compat/inertia'
import Layout from '../../Layout.vue'

const props = defineProps({
  departments: Array,
})

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  department_id: null,
  phone: '',
  bio: '',
  is_active: true,
})

const submit = () => {
  form.post(route('admin.teachers.store'))
}
</script>
