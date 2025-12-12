<template>
  <AdminApp>
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center mb-6">
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">Настройки системы</h1>
              <p class="text-body-1 text-medium-emphasis">Управление настройками системы ИФТУТ</p>
            </div>
          </div>
        </v-col>
      </v-row>

      <!-- Уведомления -->
      <v-row v-if="page.props.flash?.success">
        <v-col cols="12">
          <v-alert
            type="success"
            variant="tonal"
            closable
          >
            {{ page.props.flash.success }}
          </v-alert>
        </v-col>
      </v-row>

      <v-row v-if="page.props.flash?.error">
        <v-col cols="12">
          <v-alert
            type="error"
            variant="tonal"
            closable
          >
            {{ page.props.flash.error }}
          </v-alert>
        </v-col>
      </v-row>

      <!-- Вкладки настроек -->
      <v-row>
        <v-col cols="12">
          <v-card>
            <v-tabs
              v-model="activeTab"
              color="primary"
              align-tabs="center"
            >
              <v-tab value="general">
                <v-icon start>mdi-cog</v-icon>
                Общие
              </v-tab>
              <v-tab value="files">
                <v-icon start>mdi-file</v-icon>
                Файлы
              </v-tab>
              <v-tab value="system">
                <v-icon start>mdi-server</v-icon>
                Система
              </v-tab>
              <v-tab value="email">
                <v-icon start>mdi-email</v-icon>
                Email
              </v-tab>
              <v-tab value="profile">
                <v-icon start>mdi-account</v-icon>
                Профиль
              </v-tab>
              <v-tab value="tools">
                <v-icon start>mdi-tools</v-icon>
                Инструменты
              </v-tab>
            </v-tabs>

            <v-window v-model="activeTab">
              <!-- Общие настройки -->
              <v-window-item value="general">
                <v-card-text>
                  <v-form @submit.prevent="updateGeneral">
                    <v-row>
                      <v-col cols="12" md="6">
                        <v-text-field
                          v-model="generalForm.site_name"
                          label="Название сайта"
                          variant="outlined"
                          :error-messages="generalForm.errors.site_name"
                          required
                        ></v-text-field>
                      </v-col>
                      <v-col cols="12" md="6">
                        <v-text-field
                          v-model="generalForm.admin_email"
                          label="Email администратора"
                          type="email"
                          variant="outlined"
                          :error-messages="generalForm.errors.admin_email"
                          required
                        ></v-text-field>
                      </v-col>
                      <v-col cols="12">
                        <v-textarea
                          v-model="generalForm.site_description"
                          label="Описание сайта"
                          variant="outlined"
                          :error-messages="generalForm.errors.site_description"
                          rows="3"
                        ></v-textarea>
                      </v-col>
                      <v-col cols="12" md="6">
                        <v-select
                          v-model="generalForm.default_timezone"
                          :items="timezones"
                          item-title="text"
                          item-value="value"
                          label="Часовой пояс по умолчанию"
                          variant="outlined"
                          :error-messages="generalForm.errors.default_timezone"
                          required
                        ></v-select>
                      </v-col>
                      <v-col cols="12" md="6">
                        <v-text-field
                          v-model.number="generalForm.session_timeout"
                          label="Таймаут сессии (минуты)"
                          type="number"
                          min="30"
                          max="1440"
                          variant="outlined"
                          :error-messages="generalForm.errors.session_timeout"
                          required
                        ></v-text-field>
                      </v-col>
                    </v-row>
                    <v-card-actions>
                      <v-spacer></v-spacer>
                      <v-btn
                        color="primary"
                        type="submit"
                        :loading="generalForm.processing"
                      >
                        Сохранить
                      </v-btn>
                    </v-card-actions>
                  </v-form>
                </v-card-text>
              </v-window-item>

              <!-- Настройки файлов -->
              <v-window-item value="files">
                <v-card-text>
                  <v-form @submit.prevent="updateFileSettings">
                    <v-row>
                      <v-col cols="12" md="6">
                        <v-text-field
                          v-model.number="fileForm.max_file_size"
                          label="Максимальный размер файла (МБ)"
                          type="number"
                          min="1"
                          max="100"
                          variant="outlined"
                          :error-messages="fileForm.errors.max_file_size"
                          required
                        ></v-text-field>
                      </v-col>
                      <v-col cols="12" md="6">
                        <v-text-field
                          v-model="fileForm.allowed_file_types"
                          label="Разрешенные типы файлов"
                          variant="outlined"
                          :error-messages="fileForm.errors.allowed_file_types"
                          hint="Разделяйте типы файлов запятыми (например: pdf,doc,docx,jpg)"
                          persistent-hint
                          required
                        ></v-text-field>
                      </v-col>
                      <v-col cols="12">
                        <v-card variant="outlined">
                          <v-card-title class="text-h6">
                            Доступные типы файлов
                          </v-card-title>
                          <v-card-text>
                            <v-chip-group>
                              <v-chip
                                v-for="type in fileTypes"
                                :key="type"
                                variant="outlined"
                                size="small"
                                @click="addFileType(type)"
                              >
                                {{ type }}
                              </v-chip>
                            </v-chip-group>
                          </v-card-text>
                        </v-card>
                      </v-col>
                    </v-row>
                    <v-card-actions>
                      <v-spacer></v-spacer>
                      <v-btn
                        color="primary"
                        type="submit"
                        :loading="fileForm.processing"
                      >
                        Сохранить
                      </v-btn>
                    </v-card-actions>
                  </v-form>
                </v-card-text>
              </v-window-item>

              <!-- Системные настройки -->
              <v-window-item value="system">
                <v-card-text>
                  <v-form @submit.prevent="updateSystemSettings">
                    <v-row>
                      <v-col cols="12" md="6">
                        <v-switch
                          v-model="systemForm.maintenance_mode"
                          label="Режим обслуживания"
                          color="warning"
                          hide-details
                        ></v-switch>
                      </v-col>
                      <v-col cols="12" md="6">
                        <v-switch
                          v-model="systemForm.registration_enabled"
                          label="Разрешить регистрацию"
                          color="primary"
                          hide-details
                        ></v-switch>
                      </v-col>
                      <v-col cols="12" md="6">
                        <v-switch
                          v-model="systemForm.email_notifications"
                          label="Email уведомления"
                          color="primary"
                          hide-details
                        ></v-switch>
                      </v-col>
                      <v-col cols="12" md="6">
                        <v-switch
                          v-model="systemForm.backup_enabled"
                          label="Автоматическое резервное копирование"
                          color="primary"
                          hide-details
                        ></v-switch>
                      </v-col>
                      <v-col cols="12" md="6" v-if="systemForm.backup_enabled">
                        <v-select
                          v-model="systemForm.backup_frequency"
                          :items="backupFrequencies"
                          item-title="text"
                          item-value="value"
                          label="Частота резервного копирования"
                          variant="outlined"
                          :error-messages="systemForm.errors.backup_frequency"
                          required
                        ></v-select>
                      </v-col>
                    </v-row>
                    <v-card-actions>
                      <v-spacer></v-spacer>
                      <v-btn
                        color="primary"
                        type="submit"
                        :loading="systemForm.processing"
                      >
                        Сохранить
                      </v-btn>
                    </v-card-actions>
                  </v-form>
                </v-card-text>
              </v-window-item>

              <!-- Настройки Email -->
              <v-window-item value="email">
                <v-card-text>
                  <v-form @submit.prevent="updateEmailSettings">
                    <v-row>
                      <v-col cols="12" md="6">
                        <v-text-field
                          v-model="emailForm.smtp_host"
                          label="SMTP хост"
                          variant="outlined"
                          :error-messages="emailForm.errors.smtp_host"
                          required
                        ></v-text-field>
                      </v-col>
                      <v-col cols="12" md="6">
                        <v-text-field
                          v-model.number="emailForm.smtp_port"
                          label="SMTP порт"
                          type="number"
                          min="1"
                          max="65535"
                          variant="outlined"
                          :error-messages="emailForm.errors.smtp_port"
                          required
                        ></v-text-field>
                      </v-col>
                      <v-col cols="12" md="6">
                        <v-text-field
                          v-model="emailForm.smtp_username"
                          label="SMTP пользователь"
                          variant="outlined"
                          :error-messages="emailForm.errors.smtp_username"
                          required
                        ></v-text-field>
                      </v-col>
                      <v-col cols="12" md="6">
                        <v-text-field
                          v-model="emailForm.smtp_password"
                          label="SMTP пароль"
                          type="password"
                          variant="outlined"
                          :error-messages="emailForm.errors.smtp_password"
                        ></v-text-field>
                      </v-col>
                      <v-col cols="12" md="6">
                        <v-select
                          v-model="emailForm.smtp_encryption"
                          :items="[
                            { value: 'tls', text: 'TLS' },
                            { value: 'ssl', text: 'SSL' }
                          ]"
                          item-title="text"
                          item-value="value"
                          label="Шифрование"
                          variant="outlined"
                          :error-messages="emailForm.errors.smtp_encryption"
                          required
                        ></v-select>
                      </v-col>
                      <v-col cols="12" md="6">
                        <v-text-field
                          v-model="testEmailForm.test_email"
                          label="Тестовый email"
                          type="email"
                          variant="outlined"
                          :error-messages="testEmailForm.errors.test_email"
                        ></v-text-field>
                      </v-col>
                    </v-row>
                    <v-card-actions>
                      <v-spacer></v-spacer>
                      <v-btn
                        color="secondary"
                        variant="outlined"
                        @click="testEmail"
                        :loading="testEmailForm.processing"
                      >
                        Тест Email
                      </v-btn>
                      <v-btn
                        color="primary"
                        type="submit"
                        :loading="emailForm.processing"
                      >
                        Сохранить
                      </v-btn>
                    </v-card-actions>
                  </v-form>
                </v-card-text>
              </v-window-item>

              <!-- Профиль -->
              <v-window-item value="profile">
                <v-card-text>
                  <v-form @submit.prevent="updateProfile">
                    <v-row>
                      <v-col cols="12" md="6">
                        <v-text-field
                          v-model="profileForm.name"
                          label="Имя"
                          variant="outlined"
                          :error-messages="profileForm.errors.name"
                          required
                        ></v-text-field>
                      </v-col>
                      <v-col cols="12" md="6">
                        <v-text-field
                          v-model="profileForm.email"
                          label="Email"
                          type="email"
                          variant="outlined"
                          :error-messages="profileForm.errors.email"
                          required
                        ></v-text-field>
                      </v-col>
                      <v-col cols="12">
                        <v-divider class="my-4"></v-divider>
                        <h3 class="text-h6 mb-4">Изменить пароль</h3>
                      </v-col>
                      <v-col cols="12" md="4">
                        <v-text-field
                          v-model="profileForm.current_password"
                          label="Текущий пароль"
                          type="password"
                          variant="outlined"
                          :error-messages="profileForm.errors.current_password"
                        ></v-text-field>
                      </v-col>
                      <v-col cols="12" md="4">
                        <v-text-field
                          v-model="profileForm.new_password"
                          label="Новый пароль"
                          type="password"
                          variant="outlined"
                          :error-messages="profileForm.errors.new_password"
                        ></v-text-field>
                      </v-col>
                      <v-col cols="12" md="4">
                        <v-text-field
                          v-model="profileForm.new_password_confirmation"
                          label="Подтвердите новый пароль"
                          type="password"
                          variant="outlined"
                          :error-messages="profileForm.errors.new_password_confirmation"
                        ></v-text-field>
                      </v-col>
                    </v-row>
                    <v-card-actions>
                      <v-spacer></v-spacer>
                      <v-btn
                        color="primary"
                        type="submit"
                        :loading="profileForm.processing"
                      >
                        Сохранить
                      </v-btn>
                    </v-card-actions>
                  </v-form>
                </v-card-text>
              </v-window-item>

              <!-- Инструменты -->
              <v-window-item value="tools">
                <v-card-text>
                  <v-row>
                    <v-col cols="12" md="6">
                      <v-card variant="outlined">
                        <v-card-title class="text-h6">
                          <v-icon start>mdi-cached</v-icon>
                          Очистка кэша
                        </v-card-title>
                        <v-card-text>
                          <p class="text-body-2">Очистить все кэши системы для улучшения производительности.</p>
                        </v-card-text>
                        <v-card-actions>
                          <v-btn
                            color="warning"
                            variant="outlined"
                            @click="clearCache"
                            :loading="cacheLoading"
                          >
                            Очистить кэш
                          </v-btn>
                        </v-card-actions>
                      </v-card>
                    </v-col>
                    <v-col cols="12" md="6">
                      <v-card variant="outlined">
                        <v-card-title class="text-h6">
                          <v-icon start>mdi-database</v-icon>
                          Резервное копирование
                        </v-card-title>
                        <v-card-text>
                          <p class="text-body-2">Создать резервную копию базы данных.</p>
                        </v-card-text>
                        <v-card-actions>
                          <v-btn
                            color="info"
                            variant="outlined"
                            @click="backupDatabase"
                            :loading="backupLoading"
                          >
                            Создать резервную копию
                          </v-btn>
                        </v-card-actions>
                      </v-card>
                    </v-col>
                  </v-row>
                </v-card-text>
              </v-window-item>
            </v-window>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </AdminApp>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import AdminApp from '../AdminApp.vue'

const page = usePage()

// Props из Inertia
const props = defineProps({
  settings: {
    type: Object,
    required: true
  },
  timezones: {
    type: Array,
    default: () => []
  },
  backupFrequencies: {
    type: Array,
    default: () => []
  },
  fileTypes: {
    type: Array,
    default: () => []
  }
})

// Состояние
const activeTab = ref('general')
const cacheLoading = ref(false)
const backupLoading = ref(false)

// Формы
const generalForm = useForm({
  site_name: props.settings.site_name,
  site_description: props.settings.site_description,
  admin_email: props.settings.admin_email,
  default_timezone: props.settings.default_timezone,
  session_timeout: props.settings.session_timeout,
})

const fileForm = useForm({
  max_file_size: props.settings.max_file_size,
  allowed_file_types: props.settings.allowed_file_types,
})

const systemForm = useForm({
  maintenance_mode: props.settings.maintenance_mode,
  registration_enabled: props.settings.registration_enabled,
  email_notifications: props.settings.email_notifications,
  backup_enabled: props.settings.backup_enabled,
  backup_frequency: props.settings.backup_frequency,
})

const emailForm = useForm({
  smtp_host: props.settings.smtp_host,
  smtp_port: props.settings.smtp_port,
  smtp_username: props.settings.smtp_username,
  smtp_password: '',
  smtp_encryption: props.settings.smtp_encryption,
})

const testEmailForm = useForm({
  test_email: '',
})

const profileForm = useForm({
  name: '',
  email: '',
  current_password: '',
  new_password: '',
  new_password_confirmation: '',
})

// Методы
const updateGeneral = () => {
  generalForm.post('/admin/settings/general', {
    onError: (errors) => {
      console.error('Ошибки валидации:', errors)
    }
  })
}

const updateFileSettings = () => {
  fileForm.post('/admin/settings/files', {
    onError: (errors) => {
      console.error('Ошибки валидации:', errors)
    }
  })
}

const updateSystemSettings = () => {
  systemForm.post('/admin/settings/system', {
    onError: (errors) => {
      console.error('Ошибки валидации:', errors)
    }
  })
}

const updateEmailSettings = () => {
  emailForm.post('/admin/settings/email', {
    onError: (errors) => {
      console.error('Ошибки валидации:', errors)
    }
  })
}

const updateProfile = () => {
  profileForm.post('/admin/settings/profile', {
    onError: (errors) => {
      console.error('Ошибки валидации:', errors)
    }
  })
}

const testEmail = () => {
  testEmailForm.post('/admin/settings/test-email', {
    onError: (errors) => {
      console.error('Ошибки валидации:', errors)
    }
  })
}

const clearCache = () => {
  cacheLoading.value = true
  const form = useForm({})
  form.post('/admin/settings/clear-cache', {
    onFinish: () => {
      cacheLoading.value = false
    }
  })
}

const backupDatabase = () => {
  backupLoading.value = true
  const form = useForm({})
  form.post('/admin/settings/backup-database', {
    onFinish: () => {
      backupLoading.value = false
    }
  })
}

const addFileType = (type) => {
  const currentTypes = fileForm.allowed_file_types.split(',').map(t => t.trim()).filter(t => t)
  if (!currentTypes.includes(type)) {
    currentTypes.push(type)
    fileForm.allowed_file_types = currentTypes.join(', ')
  }
}

// Инициализация профиля
onMounted(() => {
  // Загружаем данные профиля пользователя
  const user = page.props.auth?.user
  if (user) {
    profileForm.name = user.name
    profileForm.email = user.email
  }
})
</script>

<style scoped>
.v-card {
  border-radius: 12px;
}
</style>
