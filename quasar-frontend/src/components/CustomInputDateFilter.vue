<template>
  <div class="row q-col-gutter-xs">
    <div class="col">
      <q-select class="q-mb-md" dense options-dense outlined clearable v-model="dateFilter.filterType"
        :options="dateFilterTypeOptions" :label="label" :disable="disable">
      </q-select>
    </div>
    <div class="col" v-if="dateFilter.state.hasFrom">
      <q-input dense outlined mask="date" v-model="dateFilter.formattedDate.from" :label="t('From date')"
        :disable="extraDateInputFieldsDisabled" ref="qInputFromDateRef">
        <template v-slot:append>
          <q-icon name="event" class="cursor-pointer">
            <q-popup-proxy cover transition-show="scale" transition-hide="scale">
              <q-date v-model="dateFilter.formattedDate.from" today-btn>
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
        :disable="extraDateInputFieldsDisabled" ref="qInputToDateRef">
        <template v-slot:append>
          <q-icon name="event" class="cursor-pointer">
            <q-popup-proxy cover transition-show="scale" transition-hide="scale">
              <q-date v-model="dateFilter.formattedDate.to" today-btn>
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
        :disable="extraDateInputFieldsDisabled" ref="qInputFixedDateRef" :error="!dateFilter.formattedDate.fixed"
        :error-message="t('Field is required')">
        <template v-slot:append>
          <q-icon name="event" class="cursor-pointer">
            <q-popup-proxy cover transition-show="scale" transition-hide="scale">
              <q-date v-model="dateFilter.formattedDate.fixed" today-btn>
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

import { ref, watch, computed, onMounted, nextTick } from "vue";

import { useI18n } from "vue-i18n";

import { useDateFilter } from "src/composables/dateFilter"

const { t } = useI18n();

const emit = defineEmits(['update:modelValue'])

const props = defineProps({
  modelValue: {
    type: Object,
    required: true,
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
  autofocus: {
    type: Boolean,
    default: false,
  },
  disable: {
    type: Boolean,
    required: false,
    default: false,
  },
});

const qInputFromDateRef = ref(null);
const qInputToDateRef = ref(null);
const qInputFixedDateRef = ref(null);

const dateFilter = ref(props.modelValue || {});

const { dateFilterTypeOptions } = useDateFilter();

const extraDateInputFieldsDisabled = computed(() => props.disable || dateFilter.value.state.denyChanges);

watch(() => props.modelValue, val => dateFilter.value = val);
watch(dateFilter.value, val => {
  focus();
  emit('update:modelValue', val);
});


// TODO: focus based on dateFilter.state.denyChanges
const focus = () => {
  if (!dateFilter.value.state.denyChanges) {
    nextTick(() => {
      switch (dateFilter.value.filterType.value) {
        case 7: // fixed date
          qInputFixedDateRef.value?.focus();
          break;
        case 8: // from date
          qInputFromDateRef.value?.focus();
          break;
        case 9: // to date
          qInputToDateRef.value?.focus();
          break;
        case 10: // between dates
          qInputFromDateRef.value?.focus();
          break;
      }
    });
  }
};

defineExpose({
  focus
});

onMounted(() => {
  if (props.autofocus) {
    focus();
  }
});

</script>