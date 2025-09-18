<template>
  <q-input :type="visiblePassword ? 'text' : 'password'" v-model="model" :dense="dense" :label="label"
    :outlined="outlined" :autofocus="autofocus" v-bind="attrs">
    <template v-slot:prepend>
      <q-icon name="key" />
    </template>
    <template v-slot:append v-if="model">
      <q-icon :name="visiblePassword ? 'visibility_off' : 'visibility'" class="cursor-pointer"
        @click="visiblePassword = !visiblePassword" />
      <q-tooltip anchor="bottom right" self="top end">{{ t(visiblePassword ? "Hide password"
        :
        "Show password")
      }}</q-tooltip>
    </template>
  </q-input>
</template>

<script setup>
import { ref, watch, useAttrs } from "vue";
import { useI18n } from 'vue-i18n'

const attrs = useAttrs();
console.log(attrs);

const emit = defineEmits(['update:modelValue'])

const { t } = useI18n();

const props = defineProps({
  modelValue: {
    type: String,
    default: "",
  },
  label: {
    type: String,
    default: "Password",
  },
  placeholder: {
    type: String,
    default: "Enter your password",
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
  }
});

const model = ref(props.modelValue)
const visiblePassword = ref(false);

watch(() => props.modelValue, val => model.value = val);
watch(model, val => emit('update:modelValue', val));

</script>