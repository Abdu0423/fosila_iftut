<template>
  <Layout role="teacher">
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center mb-6">
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">{{ t('navigation.my_students') }}</h1>
              <p class="text-body-1 text-medium-emphasis">{{ t('teacher.students.subtitle', {}, { default: 'Управление студентами ваших групп' }) }}</p>
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

      <!-- Статистика -->
      <v-row>
        <v-col cols="12" md="4">
          <v-card variant="outlined">
            <v-card-text class="text-center">
              <div class="text-h4 font-weight-bold text-primary">{{ totalGroups }}</div>
              <div class="text-body-2 text-medium-emphasis">Групп</div>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" md="4">
          <v-card variant="outlined">
            <v-card-text class="text-center">
              <div class="text-h4 font-weight-bold text-success">{{ totalStudents }}</div>
              <div class="text-body-2 text-medium-emphasis">Студентов</div>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" md="4">
          <v-card variant="outlined">
            <v-card-text class="text-center">
              <div class="text-h4 font-weight-bold text-info">{{ averageStudentsPerGroup }}</div>
              <div class="text-body-2 text-medium-emphasis">Студентов в группе</div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Фильтры -->
      <v-row>
        <v-col cols="12">
          <v-card variant="outlined">
            <v-card-text>
              <v-row>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="search"
                    label="Поиск по имени студента или группе"
                    variant="outlined"
                    density="compact"
                    prepend-inner-icon="mdi-magnify"
                    clearable
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="3">
                  <v-select
                    v-model="selectedGroup"
                    :items="groups"
                    item-title="name"
                    item-value="id"
                    label="Группа"
                    variant="outlined"
                    density="compact"
                    clearable
                  ></v-select>
                </v-col>
                <v-col cols="12" md="3">
                  <v-select
                    v-model="sortBy"
                    :items="[
                      { title: 'По названию группы', value: 'group' },
                      { title: 'По количеству студентов', value: 'students' }
                    ]"
                    item-title="title"
                    item-value="value"
                    label="Сортировка"
                    variant="outlined"
                    density="compact"
                  ></v-select>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Список групп -->
      <v-row>
        <v-col cols="12">
          <div v-if="filteredGroups.length === 0" class="text-center py-8">
            <v-icon size="64" color="grey-lighten-1" class="mb-4">mdi-account-group-outline</v-icon>
            <h3 class="text-h6 text-grey">Нет групп</h3>
            <p class="text-body-2 text-grey">У вас пока нет групп со студентами</p>
          </div>
          
          <div v-else>
            <v-expansion-panels variant="accordion">
              <v-expansion-panel
                v-for="group in filteredGroups"
                :key="group.id"
                class="mb-4"
              >
                <v-expansion-panel-title>
                  <div class="d-flex align-center justify-space-between w-100">
                    <div class="d-flex align-center">
                      <v-avatar color="primary" size="40" class="mr-3">
                        <v-icon color="white">mdi-account-group</v-icon>
                      </v-avatar>
                      <div>
                        <div class="font-weight-medium">{{ group.name }}</div>
                        <div class="text-caption text-medium-emphasis">{{ group.full_name }}</div>
                      </div>
                    </div>
                    <div class="d-flex align-center gap-4">
                      <v-chip
                        color="info"
                        size="small"
                        variant="tonal"
                      >
                        {{ group.students_count }} студентов
                      </v-chip>
                      <v-btn
                        color="primary"
                        variant="text"
                        size="small"
                        @click.stop="viewGroup(group)"
                        prepend-icon="mdi-eye"
                      >
                        Просмотр группы
                      </v-btn>
                    </div>
                  </div>
                </v-expansion-panel-title>
                
                <v-expansion-panel-text>
                  <div v-if="group.students.length === 0" class="text-center py-4">
                    <v-icon size="48" color="grey-lighten-1" class="mb-2">mdi-account-outline</v-icon>
                    <p class="text-body-2 text-grey">В группе нет студентов</p>
                  </div>
                  
                  <div v-else>
                    <v-list>
                      <v-list-item
                        v-for="student in group.students"
                        :key="student.id"
                        class="mb-2"
                      >
                        <template v-slot:prepend>
                          <v-avatar size="32" color="secondary">
                            <v-icon color="white" size="16">mdi-account</v-icon>
                          </v-avatar>
                        </template>
                        
                        <v-list-item-title class="text-body-2 font-weight-medium">
                          {{ student.full_name }}
                        </v-list-item-title>
                        
                        <v-list-item-subtitle class="text-caption">
                          <div>{{ student.email }}</div>
                          <div v-if="student.phone">{{ student.phone }}</div>
                        </v-list-item-subtitle>
                        
                        <template v-slot:append>
                          <v-btn
                            icon="mdi-eye"
                            variant="text"
                            size="small"
                            @click="viewStudent(student)"
                            color="primary"
                          ></v-btn>
                        </template>
                      </v-list-item>
                    </v-list>
                  </div>
                </v-expansion-panel-text>
              </v-expansion-panel>
            </v-expansion-panels>
          </div>
        </v-col>
      </v-row>
    </v-container>
  </Layout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import Layout from '../../Layout.vue'

const { t } = useI18n()
const page = usePage()

// Props из Inertia
const props = defineProps({
  groups: {
    type: Array,
    default: () => []
  }
})

// Состояние
const search = ref('')
const selectedGroup = ref(null)
const sortBy = ref('group')

// Вычисляемые свойства
const filteredGroups = computed(() => {
  let filtered = props.groups

  // Фильтр по поиску
  if (search.value) {
    const searchLower = search.value.toLowerCase()
    filtered = filtered.filter(group =>
      group.name.toLowerCase().includes(searchLower) ||
      group.full_name.toLowerCase().includes(searchLower) ||
      group.students.some(student =>
        student.full_name.toLowerCase().includes(searchLower) ||
        student.email.toLowerCase().includes(searchLower)
      )
    )
  }

  // Фильтр по группе
  if (selectedGroup.value) {
    filtered = filtered.filter(group => group.id === selectedGroup.value)
  }

  // Сортировка
  if (sortBy.value === 'students') {
    filtered = [...filtered].sort((a, b) => b.students_count - a.students_count)
  } else {
    filtered = [...filtered].sort((a, b) => a.name.localeCompare(b.name))
  }

  return filtered
})

const totalGroups = computed(() => props.groups.length)

const totalStudents = computed(() => 
  props.groups.reduce((total, group) => total + group.students_count, 0)
)

const averageStudentsPerGroup = computed(() => 
  totalGroups.value > 0 ? Math.round(totalStudents.value / totalGroups.value) : 0
)

// Методы
const navigateTo = (path) => {
  router.visit(path)
}

const viewGroup = (group) => {
  navigateTo(`/teacher/students/group/${group.id}`)
}

const viewStudent = (student) => {
  navigateTo(`/teacher/students/student/${student.id}`)
}
</script>

<style scoped>
.v-expansion-panel {
  border-radius: 12px;
  border: 1px solid #e0e0e0;
}

.v-list-item {
  border-radius: 8px;
}
</style>
