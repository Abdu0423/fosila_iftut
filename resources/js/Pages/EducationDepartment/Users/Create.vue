<template>
  <Layout role="education_department">
    <v-container fluid class="pa-6">
      <!-- Заголовок -->
      <div class="d-flex justify-space-between align-center mb-6">
        <div>
          <h1 class="text-h4 font-weight-bold mb-2">
            {{ translations.education_department?.create_user || 'Сохтани корбар' }}
          </h1>
          <p class="text-body-1 text-medium-emphasis">
            {{ translations.education_department?.create_user_subtitle || 'Илова кардани корбари нав' }}
          </p>
        </div>
        <v-btn
          color="secondary"
          variant="outlined"
          @click="navigateTo('/education/users')"
          prepend-icon="mdi-arrow-left"
        >
          {{ translations.messages?.back || 'Бозгашт' }}
        </v-btn>
      </div>

      <!-- Форма создания пользователя -->
      <v-row justify="center">
        <v-col cols="12" md="10" lg="8">
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-account-plus</v-icon>
              {{ translations.education_department?.user_info || 'Маълумоти корбар' }}
            </v-card-title>
            <v-card-text>
              <v-form @submit.prevent="submitForm">
                <!-- Основная информация -->
                <h3 class="text-h6 mb-4">{{ translations.messages?.basic_info || 'Маълумоти асосӣ' }}</h3>
                <v-row>
                  <v-col cols="12" md="4">
                    <v-text-field
                      v-model="form.name"
                      :label="(translations.messages?.name || 'Ном') + ' *'"
                      variant="outlined"
                      density="comfortable"
                      :error-messages="form.errors.name"
                      required
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="4">
                    <v-text-field
                      v-model="form.last_name"
                      :label="(translations.messages?.last_name || 'Насаб') + ' *'"
                      variant="outlined"
                      density="comfortable"
                      :error-messages="form.errors.last_name"
                      required
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="4">
                    <v-text-field
                      v-model="form.middle_name"
                      :label="translations.messages?.middle_name || 'Номи падар'"
                      variant="outlined"
                      density="comfortable"
                      :error-messages="form.errors.middle_name"
                    ></v-text-field>
                  </v-col>
                </v-row>

                <!-- Контактная информация -->
                <h3 class="text-h6 mb-4 mt-6">{{ translations.messages?.contact_info || 'Маълумоти тамос' }}</h3>
                <v-row>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="form.email"
                      label="Email"
                      type="email"
                      variant="outlined"
                      density="comfortable"
                      :error-messages="form.errors.email"
                      :hint="translations.messages?.email_hint || 'Ихтиёрӣ, ба таври худкор тавлид мешавад'"
                      persistent-hint
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="form.phone"
                      :label="translations.messages?.phone || 'Телефон'"
                      variant="outlined"
                      density="comfortable"
                      :error-messages="form.errors.phone"
                    ></v-text-field>
                  </v-col>
                </v-row>

                <!-- Адрес -->
                <v-row>
                  <v-col cols="12">
                    <v-text-field
                      v-model="form.address"
                      :label="translations.messages?.address || 'Суроға'"
                      variant="outlined"
                      density="comfortable"
                      :error-messages="form.errors.address"
                    ></v-text-field>
                  </v-col>
                </v-row>

                <!-- Телефоны родителей -->
                <v-row>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="form.dad_phone"
                      :label="translations.messages?.dad_phone || 'Телефони падар'"
                      variant="outlined"
                      density="comfortable"
                      :error-messages="form.errors.dad_phone"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="form.mom_phone"
                      :label="translations.messages?.mom_phone || 'Телефони модар'"
                      variant="outlined"
                      density="comfortable"
                      :error-messages="form.errors.mom_phone"
                    ></v-text-field>
                  </v-col>
                </v-row>

                <!-- Роль и группа -->
                <h3 class="text-h6 mb-4 mt-6">{{ translations.messages?.system_info || 'Маълумоти система' }}</h3>
                <v-row>
                  <v-col cols="12" md="6">
                    <v-select
                      v-model="form.role_id"
                      :items="roles"
                      item-title="display_name"
                      item-value="id"
                      :label="(translations.messages?.role || 'Нақш') + ' *'"
                      variant="outlined"
                      density="comfortable"
                      :error-messages="form.errors.role_id"
                      required
                    ></v-select>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-select
                      v-model="form.group_id"
                      :items="groups"
                      item-title="display_name"
                      item-value="id"
                      :label="translations.messages?.group || 'Гурӯҳ'"
                      variant="outlined"
                      density="comfortable"
                      :error-messages="form.errors.group_id"
                      clearable
                      :placeholder="translations.messages?.select_group || 'Гурӯҳро интихоб кунед'"
                    ></v-select>
                  </v-col>
                </v-row>

                <!-- Пароль -->
                <h3 class="text-h6 mb-4 mt-6">{{ translations.messages?.password || 'Парол' }}</h3>
                <v-row>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="form.password"
                      :label="translations.messages?.password || 'Парол'"
                      type="password"
                      variant="outlined"
                      density="comfortable"
                      :error-messages="form.errors.password"
                      :hint="translations.messages?.password_hint || 'Ихтиёрӣ, ба таври худкор тавлид мешавад (ақалан 4 аломат)'"
                      persistent-hint
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="form.password_confirmation"
                      :label="translations.messages?.password_confirmation || 'Такрори парол'"
                      type="password"
                      variant="outlined"
                      density="comfortable"
                      :error-messages="form.errors.password_confirmation"
                      :hint="translations.messages?.password_confirmation_hint || 'Танҳо агар паролро дар боло нишон дода бошед'"
                      persistent-hint
                    ></v-text-field>
                  </v-col>
                </v-row>

                <v-alert
                  v-if="Object.keys(form.errors).length > 0"
                  type="error"
                  variant="tonal"
                  class="mb-4"
                >
                  <div v-for="(error, field) in form.errors" :key="field">
                    <strong>{{ field }}:</strong> {{ Array.isArray(error) ? error[0] : error }}
                  </div>
                </v-alert>

                <div class="d-flex justify-end gap-3">
                  <v-btn
                    color="secondary"
                    variant="outlined"
                    @click="navigateTo('/education/users')"
                  >
                    {{ translations.messages?.cancel || 'Бекор кардан' }}
                  </v-btn>
                  <v-btn
                    color="primary"
                    type="submit"
                    :loading="form.processing"
                    :disabled="form.processing"
                  >
                    {{ translations.messages?.create || 'Сохтан' }}
                  </v-btn>
                </div>
              </v-form>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </Layout>
</template>

<script setup>
import { computed } from 'vue'
import { useForm, router, usePage } from '@inertiajs/vue3'
import Layout from '../../Layout.vue'

const page = usePage()
const translations = computed(() => page.props.translations || {})

// Props из Inertia
const props = defineProps({
  roles: {
    type: Array,
    default: () => []
  },
  groups: {
    type: Array,
    default: () => []
  }
})

// Форма
const form = useForm({
  name: '',
  last_name: '',
  middle_name: '',
  email: '',
  phone: '',
  address: '',
  dad_phone: '',
  mom_phone: '',
  role_id: '',
  group_id: '',
  password: '',
  password_confirmation: ''
})

// Методы
const navigateTo = (path) => {
  router.visit(path)
}

const submitForm = () => {
  form.post('/education/users', {
    onSuccess: () => {
      // Форма автоматически перенаправит на список пользователей
    },
    onError: (errors) => {
      console.error('Ошибки валидации:', errors)
    }
  })
}
</script>

<style scoped>
.v-card {
  border-radius: 12px;
}
</style>

