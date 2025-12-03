<template>
  <div class="row q-col-gutter-xs">
    <div class="col">
      <q-select class="q-mb-md" dense options-dense outlined :clearable="dateFilter.currentType !== 0"
        v-model="currentFilterTypeSelectorModel" :options="dateFilterTypeOptions" :label="label" :disable="disable">
        <template v-slot:prepend>
          <slot name="prepend">
            <q-icon name="calendar_month" />
          </slot>
        </template>
      </q-select>
    </div>
    <div class="col" v-if="dateFilter.hasFrom()">
      <q-input dense outlined mask="date" v-model="dateFilter.formattedDate.from" :label="t('From date')"
        :disable="extraDateInputFieldsDisabled" ref="qInputFromDateRef" :error="!dateFilter.formattedDate.from"
        :error-message="t('Field is required')">
        <template v-slot:append>
          <q-icon name="event" class="cursor-pointer">
            <q-popup-proxy cover transition-show="scale" transition-hide="scale" ref="qInputFromDatePopupProfyRef">
              <q-date v-model="dateFilter.formattedDate.from" minimal @update:model-value="hideFromDatePopupProxy">
                <div class="row items-center justify-end">
                  <q-btn v-close-popup :label="t('Close')" no-caps color="primary" flat />
                </div>
              </q-date>
            </q-popup-proxy>
          </q-icon>
        </template>
      </q-input>
    </div>
    <div class="col" v-if="dateFilter.hasTo()">
      <q-input dense outlined mask="date" v-model="dateFilter.formattedDate.to" :label="t('To date')"
        :disable="extraDateInputFieldsDisabled" ref="qInputToDateRef" :error="!dateFilter.formattedDate.to"
        :error-message="t('Field is required')">
        <template v-slot:append>
          <q-icon name="event" class="cursor-pointer">
            <q-popup-proxy cover transition-show="scale" transition-hide="scale" ref="qInputToDatePopupProfyRef">
              <q-date v-model="dateFilter.formattedDate.to" minimal @update:model-value="hideToDatePopupProxy">
                <div class="row items-center justify-end">
                  <q-btn v-close-popup :label="t('Close')" no-caps color="primary" flat />
                </div>
              </q-date>
            </q-popup-proxy>
          </q-icon>
        </template>
      </q-input>
    </div>
    <div class="col" v-if="dateFilter.hasFixed()">
      <q-input dense outlined mask="date" v-model="dateFilter.formattedDate.fixed" :label="t('Fixed date')"
        :disable="extraDateInputFieldsDisabled" ref="qInputFixedDateRef" :error="!dateFilter.formattedDate.fixed"
        :error-message="t('Field is required')">
        <template v-slot:append>
          <q-icon name="event" class="cursor-pointer">
            <q-popup-proxy cover transition-show="scale" transition-hide="scale" ref="qInputFixedDatePopupProfyRef">
              <q-date v-model="dateFilter.formattedDate.fixed" minimal @update:model-value="hideFixedDatePopupProxy">
                <div class="row items-center justify-end">
                  <q-btn v-close-popup :label="t('Close')" no-caps color="primary" flat />
                </div>
              </q-date>
            </q-popup-proxy>
          </q-icon>
        </template>
      </q-input>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, nextTick } from "vue";
import { useI18n } from "vue-i18n";
import { QInput, QPopupProxy } from 'quasar';
import { type DateFilterClass } from "src/types/date-filters";
import { selectorAvailableOptions, type SelectorOption as SelectorOptionInterface } from "src/types/date-filters";

const { t } = useI18n();

const emit = defineEmits(['update:modelValue'])

interface DateFieldCustomInput {
  modelValue: DateFilterClass;
  label?: string;
  dense?: boolean;
  outlined?: boolean;
  autofocus?: boolean;
  disable?: boolean;
  autoOpenPopUps?: boolean;
};

const props = withDefaults(defineProps<DateFieldCustomInput>(), {
  label: "",
  dense: false,
  outlined: false,
  autofocus: false,
  disable: false,
  autoOpenPopUps: true
});

const qInputFromDateRef = ref<QInput | null>(null);
const qInputToDateRef = ref<QInput | null>(null);
const qInputFixedDateRef = ref<QInput | null>(null);

const qInputFromDatePopupProfyRef = ref<QPopupProxy | null>(null);
const qInputToDatePopupProfyRef = ref<QPopupProxy | null>(null);
const qInputFixedDatePopupProfyRef = ref<QPopupProxy | null>(null);

const dateFilter = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit('update:modelValue', value);
  }
});

const dateFilterTypeOptions = computed(() =>
  selectorAvailableOptions.map((option: SelectorOptionInterface) => ({
    ...option,
    label: t(option.labelKey),
  })),
);

const currentFilterTypeSelectorModel = computed({
  get() {
    if (props.modelValue.currentType === 0) {
      return (dateFilterTypeOptions.value[0]);
    } else {
      return (dateFilterTypeOptions.value.find((option) => option.value === props.modelValue.currentType));
    }
  },
  set(option?: SelectorOptionInterface) {
    dateFilter.value.setType(option?.value || 0);
    emit('update:modelValue', dateFilter.value);
  }
});

const extraDateInputFieldsDisabled = computed(() => props.disable || dateFilter.value.state.denyChanges);

/*
watch(() => props.modelValue, (newValue: DateFilterClass) => dateFilter.value = newValue);

watch(dateFilter.value, (val: DateFilterClass) => {
  focus();
  emit('update:modelValue', val);
  if (props.autoOpenPopUps) {
    nextTick()
      .then(() => {
        //switch (val.filterType.value) {
        switch (val.currentType) {
          case 7: // fixed date
            if (!val.formattedDate.fixed) {
              qInputFixedDatePopupProfyRef.value?.show();
            }
            break;
          case 8: // from date
            if (!val.formattedDate.from) {
              qInputFromDatePopupProfyRef.value?.show();
            }
            break;
          case 9: // to date
            if (!val.formattedDate.to) {
              qInputToDatePopupProfyRef.value?.show();
            }
            break;
          case 10: // between dates
            if (!val.formattedDate.from) {
              qInputFromDatePopupProfyRef.value?.show();
            }
            break;
        }
      }).catch((e) => {
        console.error(e);
      });
  }
});

*/
const hideFromDatePopupProxy = () => {
  dateFilter.value.recalcTimestamps();
  qInputFromDatePopupProfyRef.value?.hide();
}

const hideToDatePopupProxy = () => {
  dateFilter.value.recalcTimestamps();
  qInputToDatePopupProfyRef.value?.hide();
}

const hideFixedDatePopupProxy = () => {
  dateFilter.value.recalcTimestamps();
  qInputFixedDatePopupProfyRef.value?.hide();
}

// TODO: focus based on dateFilter.state.denyChanges
const focus = () => {
  if (!dateFilter.value.state.denyChanges) {
    nextTick()
      .then(() => {
        switch (dateFilter.value.currentType) {
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
      }).catch((e) => {
        console.error(e);
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
