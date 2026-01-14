<template>
  <Layout role="admin">
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center mb-6">
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">Отправка SMS</h1>
              <p class="text-body-1 text-medium-emphasis">Отправка SMS пользователям системы</p>
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

      <!-- Режимы отправки -->
      <v-row class="mb-6">
        <v-col cols="12">
          <v-card>
            <v-card-text>
              <v-tabs v-model="activeTab" bg-color="primary">
                <v-tab value="credentials">
                  <v-icon start>mdi-key</v-icon>
                  Отправка логина и пароля
                </v-tab>
                <v-tab value="custom">
                  <v-icon start>mdi-message-text</v-icon>
                  Произвольное SMS
                </v-tab>
                <v-tab value="history">
                  <v-icon start>mdi-history</v-icon>
                  История отправки
                </v-tab>
              </v-tabs>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Контент вкладок -->
      <v-window v-model="activeTab">
        <!-- Вкладка: Отправка логина и пароля -->
        <v-window-item value="credentials">
          <!-- Фильтры и поиск -->
          <v-row class="mb-6">
            <v-col cols="12" md="4">
              <v-text-field
                v-model="search"
                prepend-inner-icon="mdi-magnify"
                label="Поиск пользователей"
                variant="outlined"
                density="compact"
                clearable
              ></v-text-field>
            </v-col>
            <v-col cols="12" md="3">
              <v-select
                v-model="roleFilter"
                :items="roleOptions"
                label="Фильтр по роли"
                variant="outlined"
                density="compact"
                clearable
              ></v-select>
            </v-col>
            <v-col cols="12" md="3">
              <v-select
                v-model="groupFilter"
                :items="groupOptions"
                label="Фильтр по группе"
                variant="outlined"
                density="compact"
                clearable
              ></v-select>
            </v-col>
            <v-col cols="12" md="2">
              <v-btn
                color="secondary"
                variant="outlined"
                @click="clearFilters"
                prepend-icon="mdi-refresh"
                block
              >
                Сбросить
              </v-btn>
            </v-col>
          </v-row>

          <!-- Выбор пользователей -->
          <v-row class="mb-6">
            <v-col cols="12">
              <v-card>
                <v-card-title class="d-flex justify-space-between align-center">
                  <span>Выбор пользователей ({{ selectedUsers.length }} выбрано)</span>
                  <div>
                    <v-btn
                      size="small"
                      variant="text"
                      @click="selectAll"
                      :disabled="filteredUsers.length === 0"
                    >
                      Выбрать все
                    </v-btn>
                    <v-btn
                      size="small"
                      variant="text"
                      @click="deselectAll"
                      :disabled="selectedUsers.length === 0"
                    >
                      Снять все
                    </v-btn>
                  </div>
                </v-card-title>
                <v-card-text>
                  <v-data-table
                    v-model="selectedUsers"
                    :headers="headers"
                    :items="filteredUsers"
                    :search="search"
                    show-select
                    item-value="id"
                    class="elevation-0"
                    :items-per-page="25"
                  >
                    <template v-slot:item.name="{ item }">
                      <div>
                        <div class="font-weight-medium">{{ item.name }} {{ item.last_name }}</div>
                        <div class="text-caption text-medium-emphasis">{{ item.email || '—' }}</div>
                      </div>
                    </template>

                    <template v-slot:item.role="{ item }">
                      <v-chip
                        :color="getRoleColor(item.role)"
                        size="small"
                        variant="tonal"
                      >
                        {{ item.role_display }}
                      </v-chip>
                    </template>

                    <template v-slot:item.group="{ item }">
                      <span v-if="item.group">{{ item.group }}</span>
                      <span v-else class="text-medium-emphasis">—</span>
                    </template>

                    <template v-slot:item.phone="{ item }">
                      <span v-if="item.phone">{{ item.phone }}</span>
                      <v-chip v-else color="warning" size="small" variant="tonal">
                        Нет телефона
                      </v-chip>
                    </template>

                    <template v-slot:item.credentials_sent="{ item }">
                      <v-chip
                        :color="item.credentials_sent ? 'success' : 'grey'"
                        size="small"
                        variant="tonal"
                      >
                        {{ item.credentials_sent ? 'Отправлено' : 'Не отправлено' }}
                      </v-chip>
                    </template>
                  </v-data-table>
                </v-card-text>
              </v-card>
            </v-col>
          </v-row>

          <!-- Форма отправки логина и пароля -->
          <v-row>
            <v-col cols="12">
              <v-card>
                <v-card-title>Отправка логина и пароля</v-card-title>
                <v-card-text>
                  <v-alert type="info" variant="tonal" class="mb-4">
                    Выбранным пользователям будет отправлен SMS с их логином и новым временным паролем.
                    При первом входе пользователю потребуется сменить пароль.
                  </v-alert>
                  <v-btn
                    color="primary"
                    size="large"
                    prepend-icon="mdi-send"
                    @click="sendCredentials"
                    :disabled="selectedUsers.length === 0"
                    :loading="sending"
                    block
                  >
                    Отправить логин и пароль ({{ selectedUsers.length }})
                  </v-btn>
                </v-card-text>
              </v-card>
            </v-col>
          </v-row>
        </v-window-item>

        <!-- Вкладка: Произвольное SMS -->
        <v-window-item value="custom">
          <!-- Фильтры и поиск -->
          <v-row class="mb-6">
            <v-col cols="12" md="4">
              <v-text-field
                v-model="search"
                prepend-inner-icon="mdi-magnify"
                label="Поиск пользователей"
                variant="outlined"
                density="compact"
                clearable
              ></v-text-field>
            </v-col>
            <v-col cols="12" md="3">
              <v-select
                v-model="roleFilter"
                :items="roleOptions"
                label="Фильтр по роли"
                variant="outlined"
                density="compact"
                clearable
              ></v-select>
            </v-col>
            <v-col cols="12" md="3">
              <v-select
                v-model="groupFilter"
                :items="groupOptions"
                label="Фильтр по группе"
                variant="outlined"
                density="compact"
                clearable
              ></v-select>
            </v-col>
            <v-col cols="12" md="2">
              <v-btn
                color="secondary"
                variant="outlined"
                @click="clearFilters"
                prepend-icon="mdi-refresh"
                block
              >
                Сбросить
              </v-btn>
            </v-col>
          </v-row>

          <!-- Выбор пользователей -->
          <v-row class="mb-6">
            <v-col cols="12">
              <v-card>
                <v-card-title class="d-flex justify-space-between align-center">
                  <span>Выбор пользователей ({{ selectedUsers.length }} выбрано)</span>
                  <div>
                    <v-btn
                      size="small"
                      variant="text"
                      @click="selectAll"
                      :disabled="filteredUsers.length === 0"
                    >
                      Выбрать все
                    </v-btn>
                    <v-btn
                      size="small"
                      variant="text"
                      @click="deselectAll"
                      :disabled="selectedUsers.length === 0"
                    >
                      Снять все
                    </v-btn>
                  </div>
                </v-card-title>
                <v-card-text>
                  <v-data-table
                    v-model="selectedUsers"
                    :headers="headers"
                    :items="filteredUsers"
                    :search="search"
                    show-select
                    item-value="id"
                    class="elevation-0"
                    :items-per-page="25"
                  >
                    <template v-slot:item.name="{ item }">
                      <div>
                        <div class="font-weight-medium">{{ item.name }} {{ item.last_name }}</div>
                        <div class="text-caption text-medium-emphasis">{{ item.email || '—' }}</div>
                      </div>
                    </template>

                    <template v-slot:item.role="{ item }">
                      <v-chip
                        :color="getRoleColor(item.role)"
                        size="small"
                        variant="tonal"
                      >
                        {{ item.role_display }}
                      </v-chip>
                    </template>

                    <template v-slot:item.group="{ item }">
                      <span v-if="item.group">{{ item.group }}</span>
                      <span v-else class="text-medium-emphasis">—</span>
                    </template>

                    <template v-slot:item.phone="{ item }">
                      <span v-if="item.phone">{{ item.phone }}</span>
                      <v-chip v-else color="warning" size="small" variant="tonal">
                        Нет телефона
                      </v-chip>
                    </template>

                    <template v-slot:item.credentials_sent="{ item }">
                      <v-chip
                        :color="item.credentials_sent ? 'success' : 'grey'"
                        size="small"
                        variant="tonal"
                      >
                        {{ item.credentials_sent ? 'Отправлено' : 'Не отправлено' }}
                      </v-chip>
                    </template>
                  </v-data-table>
                </v-card-text>
              </v-card>
            </v-col>
          </v-row>

          <!-- Форма отправки произвольного SMS -->
          <v-row>
            <v-col cols="12">
              <v-card>
                <v-card-title>Произвольное SMS</v-card-title>
                <v-card-text>
                  <v-textarea
                    v-model="customMessage"
                    label="Текст сообщения"
                    variant="outlined"
                    rows="4"
                    counter
                    maxlength="1000"
                    :rules="[rules.required, rules.maxLength]"
                    hint="Максимум 1000 символов"
                    persistent-hint
                    class="mb-4"
                  ></v-textarea>
                  <v-alert type="warning" variant="tonal" class="mb-4">
                    Сообщение будет отправлено всем выбранным пользователям.
                  </v-alert>
                  <v-btn
                    color="primary"
                    size="large"
                    prepend-icon="mdi-send"
                    @click="sendCustom"
                    :disabled="selectedUsers.length === 0 || !customMessage.trim()"
                    :loading="sending"
                    block
                  >
                    Отправить SMS ({{ selectedUsers.length }})
                  </v-btn>
                </v-card-text>
              </v-card>
            </v-col>
          </v-row>
        </v-window-item>

        <!-- Вкладка: История отправки SMS -->
        <v-window-item value="history">
          <v-row>
        <v-col cols="12">
          <v-card>
            <v-card-title class="d-flex justify-space-between align-center">
              <span>История отправки SMS</span>
            </v-card-title>
            <v-card-text>
              <!-- Поиск по номеру телефона -->
              <v-row class="mb-4">
                <v-col cols="12" md="4">
                  <v-text-field
                    v-model="phoneSearch"
                    prepend-inner-icon="mdi-magnify"
                    label="Поиск по номеру телефона"
                    variant="outlined"
                    density="compact"
                    clearable
                    @update:model-value="searchSmsHistory"
                    @keyup.enter="searchSmsHistory"
                  ></v-text-field>
                </v-col>
              </v-row>

              <!-- Таблица истории -->
              <v-data-table
                :headers="smsHistoryHeaders"
                :items="smsHistory?.data || []"
                :loading="loadingHistory"
                :items-per-page="smsHistory?.per_page || 50"
                :page="smsHistory?.current_page || 1"
                :server-items-length="smsHistory?.total || 0"
                @update:page="loadSmsHistoryPage"
                class="elevation-0"
              >
                <template v-slot:item.phone="{ item }">
                  <span class="font-weight-medium">{{ item.phone }}</span>
                </template>

                <template v-slot:item.message="{ item }">
                  <div class="text-body-2" style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                    {{ item.message }}
                  </div>
                </template>

                <template v-slot:item.status="{ item }">
                  <v-chip
                    :color="getStatusColor(item.status)"
                    size="small"
                    variant="tonal"
                  >
                    {{ getStatusText(item.status) }}
                  </v-chip>
                </template>

                <template v-slot:item.user_name="{ item }">
                  <span v-if="item.user_name">{{ item.user_name }}</span>
                  <span v-else class="text-medium-emphasis">—</span>
                </template>

                <template v-slot:item.sent_at="{ item }">
                  <span v-if="item.sent_at">{{ item.sent_at }}</span>
                  <span v-else class="text-medium-emphasis">—</span>
                </template>

                <template v-slot:item.created_at="{ item }">
                  {{ item.created_at }}
                </template>

                <template v-slot:item.error="{ item }">
                  <v-tooltip v-if="item.error" location="top">
                    <template v-slot:activator="{ props }">
                      <v-chip
                        color="error"
                        size="small"
                        variant="tonal"
                        v-bind="props"
                      >
                        Ошибка
                      </v-chip>
                    </template>
                    <span>{{ item.error }}</span>
                  </v-tooltip>
                  <span v-else class="text-medium-emphasis">—</span>
                </template>
              </v-data-table>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
        </v-window-item>
      </v-window>
    </v-container>
  </Layout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { router, usePage, useForm } from '@inertiajs/vue3'
import Layout from '../../Layout.vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

// Props из Inertia
const props = defineProps({
  users: {
    type: Array,
    default: () => []
  },
  roles: {
    type: Array,
    default: () => []
  },
  groups: {
    type: Array,
    default: () => []
  },
  smsHistory: {
    type: Object,
    default: () => null
  },
  filters: {
    type: Object,
    default: () => ({})
  }
})

// Получаем flash сообщения
const page = usePage()
const flash = computed(() => page.props.flash || {})

// Состояние
const activeTab = ref('credentials')
const search = ref('')
const roleFilter = ref(null)
const groupFilter = ref(null)
const selectedUsers = ref([])
const customMessage = ref('')
const sending = ref(false)
const phoneSearch = ref(props.filters?.phone_search || '')
const loadingHistory = ref(false)

// Заголовки таблицы
const headers = [
  { title: 'Пользователь', key: 'name', sortable: true },
  { title: 'Роль', key: 'role', sortable: true },
  { title: 'Группа', key: 'group', sortable: true },
  { title: 'Телефон', key: 'phone', sortable: true },
  { title: 'Статус', key: 'credentials_sent', sortable: true },
]

// Заголовки таблицы истории SMS
const smsHistoryHeaders = [
  { title: 'Номер телефона', key: 'phone', sortable: false },
  { title: 'Сообщение', key: 'message', sortable: false },
  { title: 'Пользователь', key: 'user_name', sortable: false },
  { title: 'Статус', key: 'status', sortable: false },
  { title: 'Отправлено', key: 'sent_at', sortable: false },
  { title: 'Создано', key: 'created_at', sortable: false },
  { title: 'Ошибка', key: 'error', sortable: false },
]

// Опции фильтров
const roleOptions = computed(() => {
  return props.roles.map(role => ({
    title: role.display_name,
    value: role.name
  }))
})

const groupOptions = computed(() => {
  return props.groups.map(group => ({
    title: group.name,
    value: group.id
  }))
})

// Отфильтрованные пользователи
const filteredUsers = computed(() => {
  let filtered = props.users

  if (roleFilter.value) {
    filtered = filtered.filter(user => user.role === roleFilter.value)
  }

  if (groupFilter.value) {
    filtered = filtered.filter(user => user.group_id === groupFilter.value)
  }

  if (search.value) {
    const searchLower = search.value.toLowerCase()
    filtered = filtered.filter(user => 
      (user.name && user.name.toLowerCase().includes(searchLower)) ||
      (user.last_name && user.last_name.toLowerCase().includes(searchLower)) ||
      (user.phone && user.phone.includes(search.value))
    )
  }

  return filtered
})

// Правила валидации
const rules = {
  required: (v) => !!v || 'Обязательное поле',
  maxLength: (v) => !v || v.length <= 1000 || 'Максимум 1000 символов'
}

// Методы
const clearFilters = () => {
  search.value = ''
  roleFilter.value = null
  groupFilter.value = null
}

const selectAll = () => {
  selectedUsers.value = filteredUsers.value.map(user => user.id)
}

const deselectAll = () => {
  selectedUsers.value = []
}

const getRoleColor = (role) => {
  const colors = {
    'admin': 'error',
    'teacher': 'warning',
    'student': 'info',
    'education_department': 'success'
  }
  return colors[role] || 'grey'
}

const sendCredentials = () => {
  if (selectedUsers.value.length === 0) {
    return
  }

  // Проверяем, что у всех выбранных пользователей есть телефон
  const usersWithoutPhone = props.users.filter(user => 
    selectedUsers.value.includes(user.id) && !user.phone
  )
  
  if (usersWithoutPhone.length > 0) {
    alert(`Невозможно отправить SMS пользователям без телефона:\n${usersWithoutPhone.map(u => `${u.name} ${u.last_name}`).join('\n')}`)
    return
  }

  sending.value = true
  router.post('/admin/sms/send-credentials', {
    user_ids: selectedUsers.value
  }, {
    preserveScroll: true,
    onSuccess: (page) => {
      // Очищаем выбранных пользователей после успешной отправки
      selectedUsers.value = []
      sending.value = false
      // Перезагружаем страницу для обновления flash сообщений и истории
      router.reload({ only: ['flash', 'smsHistory'] })
    },
    onError: (errors) => {
      sending.value = false
      console.error('Ошибка отправки SMS:', errors)
    },
    onFinish: () => {
      sending.value = false
    }
  })
}

const sendCustom = () => {
  if (selectedUsers.value.length === 0 || !customMessage.value.trim()) {
    return
  }

  // Проверяем, что у всех выбранных пользователей есть телефон
  const usersWithoutPhone = props.users.filter(user => 
    selectedUsers.value.includes(user.id) && !user.phone
  )
  
  if (usersWithoutPhone.length > 0) {
    alert(`Невозможно отправить SMS пользователям без телефона:\n${usersWithoutPhone.map(u => `${u.name} ${u.last_name}`).join('\n')}`)
    return
  }

  sending.value = true
  router.post('/admin/sms/send-custom', {
    user_ids: selectedUsers.value,
    message: customMessage.value
  }, {
    preserveScroll: true,
    onSuccess: (page) => {
      // Очищаем выбранных пользователей и сообщение после успешной отправки
      selectedUsers.value = []
      customMessage.value = ''
      sending.value = false
      // Перезагружаем страницу для обновления flash сообщений и истории
      router.reload({ only: ['flash', 'smsHistory'] })
    },
    onError: (errors) => {
      sending.value = false
      console.error('Ошибка отправки SMS:', errors)
    },
    onFinish: () => {
      sending.value = false
    }
  })
}

// Методы для истории SMS
const searchSmsHistory = () => {
  loadingHistory.value = true
  router.get('/admin/sms', {
    phone_search: phoneSearch.value || undefined,
    page: 1 // Сбрасываем на первую страницу при поиске
  }, {
    preserveState: true,
    preserveScroll: true,
    only: ['smsHistory', 'filters'],
    onFinish: () => {
      loadingHistory.value = false
    }
  })
}

const loadSmsHistoryPage = (page) => {
  loadingHistory.value = true
  router.get('/admin/sms', {
    phone_search: phoneSearch.value || undefined,
    page: page
  }, {
    preserveState: true,
    preserveScroll: true,
    only: ['smsHistory', 'filters'],
    onFinish: () => {
      loadingHistory.value = false
    }
  })
}

const getStatusColor = (status) => {
  const colors = {
    'pending': 'warning',
    'sent': 'success',
    'failed': 'error'
  }
  return colors[status] || 'grey'
}

const getStatusText = (status) => {
  const texts = {
    'pending': 'В ожидании',
    'sent': 'Отправлено',
    'failed': 'Ошибка'
  }
  return texts[status] || status
}
</script>

