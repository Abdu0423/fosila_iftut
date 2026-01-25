<template>
  <Layout role="teacher">
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center mb-6">
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">{{ t('coursework.title', {}, { default: 'Курсовые работы' }) }}</h1>
              <p class="text-body-1 text-medium-emphasis">{{ t('coursework.teacher_subtitle', {}, { default: 'Управление курсовыми работами студентов' }) }}</p>
            </div>
          </div>
        </v-col>
      </v-row>

      <!-- Уведомления -->
      <v-row v-if="$page.props.flash?.success">
        <v-col cols="12">
          <v-alert type="success" variant="tonal" closable>
            {{ $page.props.flash.success }}
          </v-alert>
        </v-col>
      </v-row>

      <!-- Список расписаний с курсовыми -->
      <v-row v-if="schedules.length > 0">
        <v-col v-for="item in schedules" :key="item.id" cols="12" md="6" lg="4">
          <v-card class="h-100">
            <v-card-title class="d-flex align-center">
              <v-icon start color="warning">mdi-file-document-edit</v-icon>
              <span class="text-truncate">{{ item.subject }}</span>
            </v-card-title>
            
            <v-card-subtitle>
              {{ item.group }} | {{ t('teacher.semester', {}, { default: 'Семестр' }) }} {{ item.semester }}
            </v-card-subtitle>

            <v-card-text>
              <template v-if="item.has_task">
                <div class="d-flex flex-wrap gap-2 mb-3">
                  <v-chip size="small" color="info" variant="tonal">
                    <v-icon start size="small">mdi-format-list-bulleted</v-icon>
                    {{ t('coursework.topics', {}, { default: 'Тем' }) }}: {{ item.task.topics_count }}
                  </v-chip>
                  <v-chip size="small" color="success" variant="tonal">
                    <v-icon start size="small">mdi-check</v-icon>
                    {{ t('coursework.taken', {}, { default: 'Выбрано' }) }}: {{ item.task.taken_topics_count }}
                  </v-chip>
                  <v-chip size="small" color="primary" variant="tonal">
                    <v-icon start size="small">mdi-file-check</v-icon>
                    {{ t('coursework.checked', {}, { default: 'Проверено' }) }}: {{ item.task.checked_count }}
                  </v-chip>
                </div>

                <div v-if="item.task.deadline" class="d-flex align-center text-body-2 mb-2">
                  <v-icon size="small" class="mr-1">mdi-calendar</v-icon>
                  {{ t('coursework.deadline', {}, { default: 'Срок' }) }}: {{ item.task.deadline }}
                </div>

                <v-chip v-if="!item.task.is_active" color="warning" size="small">
                  {{ t('coursework.inactive', {}, { default: 'Неактивно' }) }}
                </v-chip>
              </template>
              <template v-else>
                <v-alert type="info" variant="tonal" density="compact">
                  {{ t('coursework.not_configured', {}, { default: 'Курсовая работа не настроена' }) }}
                </v-alert>
              </template>
            </v-card-text>

            <v-card-actions>
              <v-btn
                color="primary"
                :variant="item.has_task ? 'outlined' : 'flat'"
                @click="$inertia.visit(`/teacher/coursework/${item.id}`)"
              >
                {{ item.has_task 
                  ? t('coursework.manage', {}, { default: 'Управление' })
                  : t('coursework.setup', {}, { default: 'Настроить' }) 
                }}
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-col>
      </v-row>

      <!-- Пустое состояние -->
      <v-row v-else>
        <v-col cols="12">
          <v-card>
            <v-card-text class="text-center py-12">
              <v-icon size="64" color="grey-lighten-1" class="mb-4">mdi-file-document-outline</v-icon>
              <h3 class="text-h6 mb-2">{{ t('coursework.no_schedules', {}, { default: 'Нет расписаний с курсовой работой' }) }}</h3>
              <p class="text-body-2 text-grey">{{ t('coursework.no_schedules_desc', {}, { default: 'Курсовая работа активируется при создании расписания' }) }}</p>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </Layout>
</template>

<script setup>
import { useI18n } from 'vue-i18n'
import Layout from '../../Layout.vue'

const { t } = useI18n()

defineProps({
  schedules: {
    type: Array,
    default: () => []
  }
})
</script>
