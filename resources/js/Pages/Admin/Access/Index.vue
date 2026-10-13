<template>
  <Layout role="admin">
    <v-container fluid class="pa-6">
      <div class="mb-6">
        <h1 class="text-h4 font-weight-bold mb-2">Доступ к страницам</h1>
        <p class="text-body-1 text-medium-emphasis">Студенту — только его страницы. Остальным ролям можно включить любую страницу сотрудников.</p>
      </div>

      <v-alert v-if="page.props.flash?.success" type="success" variant="tonal" class="mb-4">
        {{ page.props.flash.success }}
      </v-alert>

      <v-card>
        <v-card-text class="pa-6">
          <v-select
            v-model="roleId"
            :items="roles"
            item-title="description"
            item-value="id"
            label="Роль"
            variant="outlined"
          />

          <v-switch
            v-for="item in currentPages"
            :key="item.key"
            v-model="selected[item.key]"
            :label="item.title"
            color="primary"
            hide-details
            class="mb-1"
          />

          <div class="d-flex justify-end mt-6">
            <v-btn color="primary" :loading="saving" @click="save">Сохранить</v-btn>
          </div>
        </v-card-text>
      </v-card>
    </v-container>
  </Layout>
</template>

<script setup lang="ts">
import { computed, reactive, ref, watch } from 'vue'
import { usePage, router } from '@/compat/inertia'
import Layout from '../../Layout.vue'

const page = usePage()
const props = defineProps({
  roles: { type: Array, default: () => [] },
  pagesByRole: { type: Object, default: () => ({}) },
})

const roleId = ref(props.roles[0]?.id || null)
const selected = reactive<Record<string, boolean>>({})
const saving = ref(false)

const currentPages = computed(() => props.pagesByRole?.[roleId.value] || props.pagesByRole?.[String(roleId.value)] || [])

const fill = () => {
  Object.keys(selected).forEach((key) => delete selected[key])
  currentPages.value.forEach((item) => {
    selected[item.key] = !!item.enabled
  })
}

watch(roleId, fill, { immediate: true })

const save = () => {
  saving.value = true
  const keys = currentPages.value.filter((item) => selected[item.key]).map((item) => item.key)
  router.post('/admin/access', { role_id: roleId.value, keys }, {
    onFinish: () => {
      saving.value = false
    },
  })
}
</script>
