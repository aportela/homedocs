<template>
  <div class="row q-col-gutter-xs">
    <div class="col">
      <q-select class="q-mb-md" dense options-dense outlined clearable v-model="dateFilter.filterType"
        :options="dateFilterTypeOptions" :label="t(label)" :disable="disable">
      </q-select>
    </div>
    <div class="col" v-if="dateFilter.state.hasFrom">
      <q-input dense outlined mask="date" v-model="dateFilter.formattedDate.from" :label="t('From date')"
        :disable="disable || dateFilter.state.denyChanges">
        <template v-slot:append>
          <q-icon name="event" class="cursor-pointer">
            <q-popup-proxy cover transition-show="scale" transition-hide="scale">
              <q-date v-model="dateFilter.formattedDate.from" today-btn
                :disable="disable || dateFilter.state.denyChanges">
                <div class="row items-center justify-end">
                  <q-btn v-close-popup label="Close" color="primary" flat />
                </div>
              </q-date>
            </q-popup-proxy>
          </q-icon>
        </template>
      </q-input>
    </div>
    <div class="col" v-if="dateFilter.state.hasTo">
      <q-input dense outlined mask="date" v-model="dateFilter.formattedDate.to" :label="t('To date')"
        :disable="disable || dateFilter.state.denyChanges">
        <template v-slot:append>
          <q-icon name="event" class="cursor-pointer">
            <q-popup-proxy cover transition-show="scale" transition-hide="scale">
              <q-date v-model="dateFilter.formattedDate.to" today-btn
                :disable="disable || dateFilter.state.denyChanges">
                <div class="row items-center justify-end">
                  <q-btn v-close-popup label="Close" color="primary" flat />
                </div>
              </q-date>
            </q-popup-proxy>
          </q-icon>
        </template>
      </q-input>
    </div>
    <div class="col" v-if="dateFilter.state.hasFixed">
      <q-input dense outlined mask="date" v-model="dateFilter.formattedDate.fixed" :label="t('Fixed date')"
        :disable="disable || dateFilter.state.denyChanges">
        <template v-slot:append>
          <q-icon name="event" class="cursor-pointer">
            <q-popup-proxy cover transition-show="scale" transition-hide="scale">
              <q-date v-model="dateFilter.formattedDate.fixed" today-btn
                :disable="disable || dateFilter.state.denyChanges">
                <div class="row items-center justify-end">
                  <q-btn v-close-popup label="Close" color="primary" flat />
                </div>
              </q-date>
            </q-popup-proxy>
          </q-icon>
        </template>
      </q-input>
    </div>
  </div>
</template>

<script setup>

import { ref, watch, onMounted } from "vue";

import { useI18n } from "vue-i18n";

import { useDateFilter } from "src/composables/dateFilter"

const { t } = useI18n();

const emit = defineEmits(['update:modelValue'])

const props = defineProps({
  modelValue: {
    type: String,
    default: "",
  },
  label: {
    type: String,
    default: "",
  },
  dense: {
    type: Boolean,
    default: false,
  },
  outlined: {
    type: Boolean,
    default: false,
  },
  rules: {
    type: Array,
    default: () => [],
  },
  autofocus: {
    type: Boolean,
    default: false,
  },
  error:
  {
    type: Boolean,
    required: false,
    default: false
  },
  errorMessage: {
    type: String,
    required: false,
    default: null
  },
  disable: {
    type: Boolean,
    required: false,
    default: false,
  },
});

const qInputDateRef = ref(null);

const model = ref(props.modelValue)

watch(() => props.modelValue, val => model.value = val);
watch(model, val => emit('update:modelValue', val));

const { dateFilter, dateFilterTypeOptions } = useDateFilter();

dateFilter.filterType = dateFilterTypeOptions[0];

// TODO: focus based on dateFilter.state.denyChanges
const focus = () => {
  nextTick(() => {
    qInputDateRef.value?.focus();
  });
}

const resetValidation = () => {
  qInputDateRef.value?.resetValidation();
}

const validate = () => {
  return (qInputDateRef.value?.validate() === true);
}

defineExpose({
  focus, resetValidation, validate
});

onMounted(() => {
  if (props.autofocus) {
    focus();
  }
});

</script>