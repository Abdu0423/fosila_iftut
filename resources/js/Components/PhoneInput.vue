<template>
  <div class="phone-input-wrapper">
    <v-row no-gutters>
      <v-col cols="auto" class="phone-prefix-col">
        <v-select
          v-model="selectedPrefix"
          :items="countryOptions"
          item-title="label"
          item-value="prefix"
          variant="outlined"
          :density="density"
          hide-details
          class="phone-prefix-select"
          @update:model-value="onPrefixChange"
        >
          <template v-slot:selection="{ item }">
            <span class="text-body-2">{{ item.raw.flag }} {{ item.raw.prefix }}</span>
          </template>
          <template v-slot:item="{ props, item }">
            <v-list-item v-bind="props" :title="null">
              <template v-slot:prepend>
                <span class="text-h6 mr-2">{{ item.raw.flag }}</span>
              </template>
              <v-list-item-title>{{ item.raw.label }}</v-list-item-title>
              <v-list-item-subtitle>{{ item.raw.prefix }}</v-list-item-subtitle>
            </v-list-item>
          </template>
        </v-select>
      </v-col>
      <v-col>
        <v-text-field
          :model-value="phoneNumber"
          @update:model-value="updatePhoneNumber"
          :label="label"
          :error-messages="errorMessages"
          :disabled="disabled"
          :required="required"
          :hint="hint"
          :persistent-hint="persistentHint"
          variant="outlined"
          :density="density"
          prepend-inner-icon="mdi-phone"
          class="phone-number-field"
          type="tel"
          autocomplete="tel"
          inputmode="numeric"
        />
      </v-col>
    </v-row>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  label: {
    type: String,
    default: 'Телефон'
  },
  errorMessages: {
    type: [String, Array],
    default: () => []
  },
  disabled: {
    type: Boolean,
    default: false
  },
  required: {
    type: Boolean,
    default: false
  },
  hint: {
    type: String,
    default: ''
  },
  persistentHint: {
    type: Boolean,
    default: false
  },
  density: {
    type: String,
    default: 'comfortable'
  }
})

const emit = defineEmits(['update:modelValue'])

const countryOptions = [
  { prefix: '+992', label: 'Таджикистан', flag: '🇹🇯' },
  { prefix: '+7', label: 'Россия / Казахстан', flag: '🇷🇺' },
  { prefix: '+996', label: 'Киргизстан', flag: '🇰🇬' },
  { prefix: '+998', label: 'Узбекистан', flag: '🇺🇿' },
]

const selectedPrefix = ref('+992')

// Извлекаем префикс и номер из полного значения
const parsePhone = (phone) => {
  if (!phone) return { prefix: '+992', number: '' }
  
  for (const country of countryOptions) {
    if (phone.startsWith(country.prefix)) {
      return {
        prefix: country.prefix,
        number: phone.substring(country.prefix.length)
      }
    }
  }
  
  // Если префикс не найден, предполагаем что это только номер
  return { prefix: '+992', number: phone }
}

const phoneNumber = ref('')

// Инициализация при монтировании
const init = () => {
  const parsed = parsePhone(props.modelValue)
  selectedPrefix.value = parsed.prefix
  phoneNumber.value = parsed.number
}

init()

// Отслеживаем изменения внешнего значения
watch(() => props.modelValue, (newValue) => {
  const parsed = parsePhone(newValue)
  if (parsed.prefix !== selectedPrefix.value || parsed.number !== phoneNumber.value) {
    selectedPrefix.value = parsed.prefix
    phoneNumber.value = parsed.number
  }
})

const onPrefixChange = () => {
  emit('update:modelValue', selectedPrefix.value + phoneNumber.value)
}

const updatePhoneNumber = (value) => {
  // Удаляем все нецифровые символы
  phoneNumber.value = value.replace(/\D/g, '')
  emit('update:modelValue', selectedPrefix.value + phoneNumber.value)
}
</script>

<style scoped>
.phone-input-wrapper {
  width: 100%;
}

.phone-prefix-col {
  max-width: 180px;
  padding-right: 0 !important;
  flex: 0 0 auto;
}

.phone-prefix-select {
  border-top-right-radius: 0;
  border-bottom-right-radius: 0;
}

.phone-prefix-select :deep(.v-field) {
  border-right: none;
  height: 100%;
}

.phone-prefix-select :deep(.v-field__input) {
  min-height: inherit;
  padding-top: 0 !important;
  padding-bottom: 0 !important;
}

.phone-prefix-select :deep(.v-field__input__control) {
  min-height: auto !important;
}

/* Скрываем стандартное отображение label в выбранном элементе */
.phone-prefix-select :deep(.v-select__selection-text) {
  display: none;
}

.phone-number-field {
  margin-left: 0;
}

.phone-number-field :deep(.v-field) {
  border-top-left-radius: 0;
  border-bottom-left-radius: 0;
  height: 100%;
}

.phone-number-field :deep(.v-field__input) {
  min-height: inherit;
}

.phone-number-field :deep(.v-field__prepend-inner) {
  padding-left: 12px;
}

/* Выравнивание высоты обоих полей */
.phone-input-wrapper :deep(.v-row) {
  align-items: flex-start;
}

.phone-input-wrapper :deep(.v-col) {
  display: flex;
}

/* Синхронизация высоты полей - одинаковые значения для обоих */
.phone-prefix-select :deep(.v-field),
.phone-number-field :deep(.v-field) {
  height: 56px;
}

.phone-prefix-select :deep(.v-field__input),
.phone-number-field :deep(.v-field__input) {
  min-height: 56px;
  padding-top: 0;
  padding-bottom: 0;
}
</style>
