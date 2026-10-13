<template>
  <v-row>
    <v-col cols="12" md="8">
      <v-text-field
        v-model="form.name"
        label="Название *"
        variant="outlined"
        :error-messages="form.errors.name"
      />
    </v-col>
    <v-col cols="12" md="4">
      <v-text-field
        v-model="form.code"
        label="Код"
        variant="outlined"
        hint="Если пусто, код будет назначен автоматически"
        persistent-hint
        :error-messages="form.errors.code"
      />
    </v-col>
    <v-col cols="12">
      <v-select
        v-model="form.faculty_id"
        label="Факультет"
        :items="faculties"
        item-title="name"
        item-value="id"
        variant="outlined"
        clearable
        :error-messages="form.errors.faculty_id"
      />
    </v-col>
    <v-col cols="12">
      <v-select
        v-model="form.user_id"
        label="Пользователь"
        :items="userItems"
        item-title="label"
        item-value="id"
        variant="outlined"
        clearable
        :error-messages="form.errors.user_id"
      />
    </v-col>
    <v-col cols="12">
      <v-textarea
        v-model="form.description"
        label="Описание"
        variant="outlined"
        rows="3"
        :error-messages="form.errors.description"
      />
    </v-col>
    <v-col cols="12">
      <v-switch v-model="form.is_active" label="Активна" color="success" />
    </v-col>
  </v-row>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps({
  form: { type: Object, required: true },
  faculties: { type: Array, default: () => [] },
  users: { type: Array, default: () => [] },
})

const userItems = computed(() => (props.users || []).map((user) => ({
  id: user.id,
  label: [user.last_name, user.name, user.middle_name].filter(Boolean).join(' '),
})))
</script>
