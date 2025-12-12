<template>
  <Layout>
    <v-container fluid>
      <div class="settings-page">
        <v-row>
        <v-col cols="12">
          <h1 class="text-h4 mb-6">Настройки</h1>
        </v-col>
      </v-row>
      
      <v-row>
        <v-col cols="12" md="8">
          <!-- Настройки аккаунта -->
          <v-card class="mb-4">
            <v-card-title>
              <v-icon class="mr-3">mdi-account-cog</v-icon>
              Настройки аккаунта
            </v-card-title>
            <v-card-text>
              <v-form ref="accountForm" v-model="accountValid">
                <v-row>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="settings.email"
                      label="Email"
                      outlined
                      dense
                      :rules="[v => !!v, v => /.+@.+\..+/.test(v)]"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="settings.phone"
                      label="Телефон"
                      outlined
                      dense
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12">
                    <v-textarea
                      v-model="settings.bio"
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
              <v-btn color="primary" @click="saveAccountSettings" :disabled="!accountValid">
                Сохранить
              </v-btn>
            </v-card-actions>
          </v-card>
          
          <!-- Смена пароля -->
          <v-card class="mb-4">
            <v-card-title>
              <v-icon class="mr-3">mdi-lock</v-icon>
              Смена пароля
            </v-card-title>
            <v-card-text>
              <v-form ref="passwordForm" v-model="passwordValid">
                <v-row>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="passwordForm.currentPassword"
                      label="Текущий пароль"
                      type="password"
                      outlined
                      dense
                      :rules="[v => !!v]"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="passwordForm.newPassword"
                      label="Новый пароль"
                      type="password"
                      outlined
                      dense
                      :rules="[v => !!v, v => v.length >= 8]"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="passwordForm.confirmPassword"
                      label="Подтвердите новый пароль"
                      type="password"
                      outlined
                      dense
                      :rules="[v => !!v, v => v === passwordForm.newPassword]"
                    ></v-text-field>
                  </v-col>
                </v-row>
              </v-form>
            </v-card-text>
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn color="primary" @click="changePassword" :disabled="!passwordValid">
                Сменить пароль
              </v-btn>
            </v-card-actions>
          </v-card>
          
          <!-- Настройки уведомлений -->
          <v-card class="mb-4">
            <v-card-title>
              <v-icon class="mr-3">mdi-bell</v-icon>
              Настройки уведомлений
            </v-card-title>
            <v-card-text>
              <v-list>
                <v-list-item>
                  <v-list-item-content>
                    <v-list-item-title>Email уведомления</v-list-item-title>
                    <v-list-item-subtitle>Получать уведомления на email</v-list-item-subtitle>
                  </v-list-item-content>
                  <v-list-item-action>
                    <v-switch v-model="settings.notifications.email"></v-switch>
                  </v-list-item-action>
                </v-list-item>
                
                <v-list-item>
                  <v-list-item-content>
                    <v-list-item-title>Уведомления о новых заданиях</v-list-item-title>
                    <v-list-item-subtitle>Получать уведомления о новых заданиях</v-list-item-subtitle>
                  </v-list-item-content>
                  <v-list-item-action>
                    <v-switch v-model="settings.notifications.newAssignments"></v-switch>
                  </v-list-item-action>
                </v-list-item>
                
                <v-list-item>
                  <v-list-item-content>
                    <v-list-item-title>Уведомления об оценках</v-list-item-title>
                    <v-list-item-subtitle>Получать уведомления о новых оценках</v-list-item-subtitle>
                  </v-list-item-content>
                  <v-list-item-action>
                    <v-switch v-model="settings.notifications.grades"></v-switch>
                  </v-list-item-action>
                </v-list-item>
                
                <v-list-item>
                  <v-list-item-content>
                    <v-list-item-title>Напоминания о дедлайнах</v-list-item-title>
                    <v-list-item-subtitle>Получать напоминания о сроках сдачи</v-list-item-subtitle>
                  </v-list-item-content>
                  <v-list-item-action>
                    <v-switch v-model="settings.notifications.deadlines"></v-switch>
                  </v-list-item-action>
                </v-list-item>
              </v-list>
            </v-card-text>
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn color="primary" @click="saveNotificationSettings">
                Сохранить
              </v-btn>
            </v-card-actions>
          </v-card>
          
          <!-- Настройки приватности -->
          <v-card class="mb-4">
            <v-card-title>
              <v-icon class="mr-3">mdi-shield</v-icon>
              Настройки приватности
            </v-card-title>
            <v-card-text>
              <v-list>
                <v-list-item>
                  <v-list-item-content>
                    <v-list-item-title>Публичный профиль</v-list-item-title>
                    <v-list-item-subtitle>Разрешить другим пользователям видеть ваш профиль</v-list-item-subtitle>
                  </v-list-item-content>
                  <v-list-item-action>
                    <v-switch v-model="settings.privacy.publicProfile"></v-switch>
                  </v-list-item-action>
                </v-list-item>
                
                <v-list-item>
                  <v-list-item-content>
                    <v-list-item-title>Показывать оценки</v-list-item-title>
                    <v-list-item-subtitle>Разрешить другим видеть ваши оценки</v-list-item-subtitle>
                  </v-list-item-content>
                  <v-list-item-action>
                    <v-switch v-model="settings.privacy.showGrades"></v-switch>
                  </v-list-item-action>
                </v-list-item>
                
                <v-list-item>
                  <v-list-item-content>
                    <v-list-item-title>Показывать активность</v-list-item-title>
                    <v-list-item-subtitle>Показывать когда вы в сети</v-list-item-subtitle>
                  </v-list-item-content>
                  <v-list-item-action>
                    <v-switch v-model="settings.privacy.showActivity"></v-switch>
                  </v-list-item-action>
                </v-list-item>
              </v-list>
            </v-card-text>
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn color="primary" @click="savePrivacySettings">
                Сохранить
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-col>
        
        <!-- Боковая панель -->
        <v-col cols="12" md="4">
          <!-- Быстрые действия -->
          <v-card class="mb-4">
            <v-card-title>Быстрые действия</v-card-title>
            <v-card-text>
              <v-list dense>
                <v-list-item @click="exportData">
                  <v-list-item-icon>
                    <v-icon>mdi-download</v-icon>
                  </v-list-item-icon>
                  <v-list-item-content>
                    <v-list-item-title>Экспорт данных</v-list-item-title>
                  </v-list-item-content>
                </v-list-item>
                
                <v-list-item @click="deleteAccount = true">
                  <v-list-item-icon>
                    <v-icon color="error">mdi-delete</v-icon>
                  </v-list-item-icon>
                  <v-list-item-content>
                    <v-list-item-title class="error--text">Удалить аккаунт</v-list-item-title>
                  </v-list-item-content>
                </v-list-item>
              </v-list>
            </v-card-text>
          </v-card>
          
          <!-- Информация о системе -->
          <v-card>
            <v-card-title>Информация о системе</v-card-title>
            <v-card-text>
              <v-list dense>
                <v-list-item>
                  <v-list-item-title>Версия приложения</v-list-item-title>
                  <v-list-item-subtitle>1.0.0</v-list-item-subtitle>
                </v-list-item>
                <v-list-item>
                  <v-list-item-title>Последнее обновление</v-list-item-title>
                  <v-list-item-subtitle>2024-01-20</v-list-item-subtitle>
                </v-list-item>
                <v-list-item>
                  <v-list-item-title>Размер данных</v-list-item-title>
                  <v-list-item-subtitle>2.5 MB</v-list-item-subtitle>
                </v-list-item>
              </v-list>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
      </div>
    </v-container>
    
    <!-- Диалог удаления аккаунта -->
    <v-dialog v-model="deleteAccount" max-width="400">
      <v-card>
        <v-card-title class="error--text">
          <v-icon color="error" class="mr-2">mdi-alert</v-icon>
          Удаление аккаунта
        </v-card-title>
        <v-card-text>
          <p>Вы уверены, что хотите удалить свой аккаунт? Это действие нельзя отменить.</p>
          <v-text-field
            v-model="deleteConfirmation"
            label="Введите 'УДАЛИТЬ' для подтверждения"
            outlined
            dense
          ></v-text-field>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn text @click="deleteAccount = false">Отмена</v-btn>
          <v-btn
            color="error"
            :disabled="deleteConfirmation !== 'УДАЛИТЬ'"
            @click="confirmDeleteAccount"
          >
            Удалить аккаунт
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </Layout>
</template>

<script>
import Layout from '../Layout.vue'

export default {
  name: 'SettingsIndex',
  components: {
    Layout
  },
  data() {
    return {
      accountValid: false,
      passwordValid: false,
      deleteAccount: false,
      deleteConfirmation: '',
      settings: {
        email: 'ivan.ivanov@example.com',
        phone: '+7 (999) 123-45-67',
        bio: 'Студент факультета информационных систем.',
        notifications: {
          email: true,
          newAssignments: true,
          grades: true,
          deadlines: true
        },
        privacy: {
          publicProfile: false,
          showGrades: false,
          showActivity: true
        }
      },
      passwordForm: {
        currentPassword: '',
        newPassword: '',
        confirmPassword: ''
      }
    }
  },
  methods: {
    saveAccountSettings() {
      if (this.$refs.accountForm.validate()) {
        // Здесь будет логика сохранения настроек аккаунта
        console.log('Сохранение настроек аккаунта:', this.settings)
      }
    },
    
    changePassword() {
      if (this.$refs.passwordForm.validate()) {
        // Здесь будет логика смены пароля
        console.log('Смена пароля')
        this.passwordForm = {
          currentPassword: '',
          newPassword: '',
          confirmPassword: ''
        }
      }
    },
    
    saveNotificationSettings() {
      // Здесь будет логика сохранения настроек уведомлений
      console.log('Сохранение настроек уведомлений:', this.settings.notifications)
    },
    
    savePrivacySettings() {
      // Здесь будет логика сохранения настроек приватности
      console.log('Сохранение настроек приватности:', this.settings.privacy)
    },
    
    exportData() {
      // Здесь будет логика экспорта данных
      console.log('Экспорт данных')
    },
    
    confirmDeleteAccount() {
      // Здесь будет логика удаления аккаунта
      console.log('Удаление аккаунта')
      this.deleteAccount = false
      this.deleteConfirmation = ''
    }
  }
}
</script>
