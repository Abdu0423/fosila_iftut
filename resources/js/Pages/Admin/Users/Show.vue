<template>
  <AdminApp>
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center mb-6">
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">Информация о пользователе</h1>
              <p class="text-body-1 text-medium-emphasis">Детальная информация о пользователе системы</p>
            </div>
            <div class="d-flex gap-3">
              <v-btn
                color="primary"
                variant="outlined"
                @click="editUser"
                prepend-icon="mdi-pencil"
              >
                Редактировать
              </v-btn>
              <v-btn
                color="secondary"
                variant="outlined"
                @click="navigateTo('/admin/users')"
                prepend-icon="mdi-arrow-left"
              >
                Назад к списку
              </v-btn>
            </div>
          </div>
        </v-col>
      </v-row>

      <!-- Flash сообщения -->
      <v-row v-if="flash && (flash.success || flash.error)" class="mb-6">
        <v-col cols="12">
                     <v-alert
             v-if="flash && flash.success"
             type="success"
             variant="tonal"
             closable
           >
             {{ flash.success }}
           </v-alert>
           <v-alert
             v-if="flash && flash.error"
             type="error"
             variant="tonal"
             closable
           >
             {{ flash.error }}
           </v-alert>
        </v-col>
      </v-row>

      <!-- Основная информация -->
      <v-row>
        <v-col cols="12" md="4">
          <!-- Карточка с аватаром и основной информацией -->
          <v-card class="mb-6">
            <v-card-text class="text-center">
              <v-avatar size="120" class="mb-4">
                <v-img :src="user.avatar" :alt="user.name"></v-img>
              </v-avatar>
              <h2 class="text-h5 font-weight-bold mb-2">{{ user.name }} {{ user.last_name }}</h2>
              <v-chip
                :color="getRoleColor(user.role)"
                size="large"
                variant="tonal"
                class="mb-3"
              >
                {{ user.role_display }}
              </v-chip>
              <div class="text-body-2 text-medium-emphasis">
                <v-icon size="small" class="mr-1">mdi-email</v-icon>
                {{ user.email }}
              </div>
              <div class="text-body-2 text-medium-emphasis mt-2">
                <v-icon size="small" class="mr-1">mdi-calendar</v-icon>
                Зарегистрирован: {{ user.created_at }}
              </div>
            </v-card-text>
          </v-card>

          <!-- Статус и действия -->
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-information</v-icon>
              Статус и действия
            </v-card-title>
            <v-card-text>
              <div class="mb-4">
                <div class="text-body-2 text-medium-emphasis mb-1">Статус аккаунта:</div>
                <v-chip
                  :color="user.status === 'Подтвержден' ? 'success' : 'warning'"
                  size="small"
                  variant="tonal"
                >
                  {{ user.status }}
                </v-chip>
              </div>
              
              <v-divider class="my-4"></v-divider>
              
              <div class="d-flex flex-column gap-2">
                <v-btn
                  color="primary"
                  variant="outlined"
                  size="small"
                  @click="editUser"
                  prepend-icon="mdi-pencil"
                >
                  Редактировать профиль
                </v-btn>
                <v-btn
                  color="warning"
                  variant="outlined"
                  size="small"
                  @click="resetPassword"
                  prepend-icon="mdi-key-reset"
                >
                  Сбросить пароль
                </v-btn>
                <v-btn
                  color="error"
                  variant="outlined"
                  size="small"
                  @click="deleteUser"
                  prepend-icon="mdi-delete"
                >
                  Удалить пользователя
                </v-btn>
              </div>
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12" md="8">
          <!-- Детальная информация -->
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-account-details</v-icon>
              Детальная информация
            </v-card-title>
            <v-card-text>
              <v-row>
                <v-col cols="12" md="6">
                  <div class="mb-4">
                    <div class="text-body-2 text-medium-emphasis mb-1">Имя:</div>
                    <div class="text-body-1 font-weight-medium">{{ user.name }}</div>
                  </div>
                </v-col>
                <v-col cols="12" md="6">
                  <div class="mb-4">
                    <div class="text-body-2 text-medium-emphasis mb-1">Фамилия:</div>
                    <div class="text-body-1 font-weight-medium">{{ user.last_name }}</div>
                  </div>
                </v-col>
                <v-col cols="12" md="6">
                  <div class="mb-4">
                    <div class="text-body-2 text-medium-emphasis mb-1">Отчество:</div>
                    <div class="text-body-1 font-weight-medium">{{ user.middle_name }}</div>
                  </div>
                </v-col>
                <v-col cols="12" md="6">
                  <div class="mb-4">
                    <div class="text-body-2 text-medium-emphasis mb-1">Email:</div>
                    <div class="text-body-1 font-weight-medium">{{ user.email }}</div>
                  </div>
                </v-col>
                <v-col cols="12" md="6">
                  <div class="mb-4">
                    <div class="text-body-2 text-medium-emphasis mb-1">Телефон:</div>
                    <div class="text-body-1 font-weight-medium">{{ user.phone }}</div>
                  </div>
                </v-col>
                <v-col cols="12" md="6">
                  <div class="mb-4">
                    <div class="text-body-2 text-medium-emphasis mb-1">Адрес:</div>
                    <div class="text-body-1 font-weight-medium">{{ user.address }}</div>
                  </div>
                </v-col>
                <v-col cols="12" md="6">
                  <div class="mb-4">
                    <div class="text-body-2 text-medium-emphasis mb-1">Телефон отца:</div>
                    <div class="text-body-1 font-weight-medium">{{ user.dad_phone }}</div>
                  </div>
                </v-col>
                <v-col cols="12" md="6">
                  <div class="mb-4">
                    <div class="text-body-2 text-medium-emphasis mb-1">Телефон матери:</div>
                    <div class="text-body-1 font-weight-medium">{{ user.mom_phone }}</div>
                  </div>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>

          <!-- Системная информация -->
          <v-card class="mt-6">
            <v-card-title class="text-h6">
              <v-icon start>mdi-cog</v-icon>
              Системная информация
            </v-card-title>
            <v-card-text>
              <v-row>
                <v-col cols="12" md="6">
                  <div class="mb-4">
                    <div class="text-body-2 text-medium-emphasis mb-1">ID пользователя:</div>
                    <div class="text-body-1 font-weight-medium">{{ user.id }}</div>
                  </div>
                </v-col>
                <v-col cols="12" md="6">
                  <div class="mb-4">
                    <div class="text-body-2 text-medium-emphasis mb-1">Роль:</div>
                    <div class="text-body-1 font-weight-medium">{{ user.role_display }}</div>
                  </div>
                </v-col>
                <v-col cols="12" md="6">
                  <div class="mb-4">
                    <div class="text-body-2 text-medium-emphasis mb-1">Дата регистрации:</div>
                    <div class="text-body-1 font-weight-medium">{{ user.created_at }}</div>
                  </div>
                </v-col>
                <v-col cols="12" md="6">
                  <div class="mb-4">
                    <div class="text-body-2 text-medium-emphasis mb-1">Последнее обновление:</div>
                    <div class="text-body-1 font-weight-medium">{{ user.updated_at }}</div>
                  </div>
                </v-col>
                <v-col cols="12" md="6">
                  <div class="mb-4">
                    <div class="text-body-2 text-medium-emphasis mb-1">Email подтвержден:</div>
                    <div class="text-body-1 font-weight-medium">
                      {{ user.email_verified_at ? user.email_verified_at : 'Не подтвержден' }}
                    </div>
                  </div>
                </v-col>
                <v-col cols="12" md="6">
                  <div class="mb-4">
                    <div class="text-body-2 text-medium-emphasis mb-1">ID группы:</div>
                    <div class="text-body-1 font-weight-medium">{{ user.group_id }}</div>
                  </div>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Диалог удаления -->
      <v-dialog v-model="deleteDialog" max-width="400">
        <v-card>
          <v-card-title class="text-h6">
            Подтверждение удаления
          </v-card-title>
          <v-card-text>
            Вы действительно хотите удалить пользователя <strong>{{ user.name }} {{ user.last_name }}</strong>?
            Это действие нельзя отменить.
          </v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn
              color="grey"
              variant="text"
              @click="deleteDialog = false"
            >
              Отмена
            </v-btn>
            <v-btn
              color="error"
              variant="text"
              @click="confirmDelete"
              :loading="deleting"
            >
              Удалить
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </v-container>
  </AdminApp>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AdminApp from '../AdminApp.vue'

// Props из Inertia
const props = defineProps({
  user: {
    type: Object,
    required: true
  }
})

// Получаем flash сообщения
const page = usePage()
const flash = computed(() => page.props.flash || {})

// Состояние
const deleteDialog = ref(false)
const deleting = ref(false)

// Методы
const navigateTo = (route) => {
  router.visit(route)
}

const getRoleColor = (role) => {
  const colors = {
    'admin': 'error',
    'teacher': 'warning',
    'student': 'primary'
  }
  return colors[role]
}

const editUser = () => {
  router.visit(`/admin/users/${props.user.id}/edit`)
}

const resetPassword = () => {
  // Здесь будет логика сброса пароля
  console.log('Сброс пароля для пользователя:', props.user.id)
}

const deleteUser = () => {
  deleteDialog.value = true
}

const confirmDelete = async () => {
  deleting.value = true
  
  try {
    await router.post(`/admin/users/${props.user.id}/delete`, {}, {
      onSuccess: () => {
        deleteDialog.value = false
      },
      onError: (errors) => {
        console.error('Ошибка при удалении пользователя:', errors)
      },
      onFinish: () => {
        deleting.value = false
      }
    })
  } catch (error) {
    console.error('Ошибка при удалении пользователя:', error)
    deleting.value = false
  }
}
</script>

<style scoped>
.v-card {
  border-radius: 12px;
}
</style>
