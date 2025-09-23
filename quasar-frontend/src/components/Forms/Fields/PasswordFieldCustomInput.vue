<template>
  <q-input :type="visiblePassword ? 'text' : 'password'" v-model="model" :dense="dense" :label="label" :rules="rules"
    :outlined="outlined" v-bind="attrs" ref="qInputPasswordRef" :error="error" :errorMessage="errorMessage">
    <template v-slot:prepend>
      <q-icon name="key" />
    </template>
    <template v-slot:append v-if="model">
      <q-icon :name="visiblePassword ? 'visibility_off' : 'visibility'" class="cursor-pointer"
        @click="visiblePassword = !visiblePassword" />
      <q-tooltip anchor="bottom right" self="top end">{{ t(visiblePassword ? "Hide password" : "Show password")
        }}</q-tooltip>
    </template>
  </q-input>
</template>

<script setup>

import { ref, watch, useAttrs, onMounted, nextTick } from "vue";
import { useI18n } from 'vue-i18n'

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

const model = ref(props.modelValue)
const visiblePassword = ref(false);

watch(() => props.modelValue, val => model.value = val);
watch(model, val => emit('update:modelValue', val));

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