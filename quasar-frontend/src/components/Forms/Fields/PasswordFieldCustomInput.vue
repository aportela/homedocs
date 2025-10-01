<template>
  <q-input :type="visiblePassword ? 'text' : 'password'" v-model="internalModel" :dense="dense" :label="label"
    :rules="rules" :outlined="outlined" v-bind="attrs" ref="qInputPasswordRef" :error="error"
    :errorMessage="errorMessage">
    <template v-slot:prepend>
      <q-icon name="key" />
    </template>
    <template v-slot:append v-if="internalModel">
      <q-icon :name="visibilityIcon" class="cursor-pointer" @click="visiblePassword = !visiblePassword"
        :aria-label="t(tooltipLabel)" />
      <q-tooltip anchor="bottom right" self="top end" :aria-label="t(tooltipLabel)">{{ t(tooltipLabel)
        }}</q-tooltip>
    </template>
  </q-input>
</template>

<script setup>

import { ref, computed, useAttrs, onMounted, nextTick } from "vue";
import { useI18n } from "vue-i18n";

const attrs = useAttrs();

const { t } = useI18n();

const emit = defineEmits(['update:modelValue'])

const props = defineProps({
  modelValue: {
    type: String,
    default: "",
  },
  label: {
    type: String,
    default: "Password",
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
});

const qInputPasswordRef = ref(null);

const visiblePassword = ref(false);

const internalModel = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit('update:modelValue', value);
  }
});

const visibilityIcon = computed(() => visiblePassword.value ? "visibility_off" : "visibility");
const tooltipLabel = computed(() => visiblePassword.value ? "Hide password" : "Show password");

const focus = () => {
  nextTick(() => {
    qInputPasswordRef.value?.focus();
  });
}

const resetValidation = () => {
  qInputPasswordRef.value?.resetValidation();
}

const validate = () => {
  return (qInputPasswordRef.value?.validate() === true);
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