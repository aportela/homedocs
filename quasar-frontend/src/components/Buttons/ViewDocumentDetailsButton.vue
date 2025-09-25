<template>
  <q-chip class="full-width theme-default-q-chip shadow-1" :class="qChipClass" :clickable="count > 0 && !disable"
    @click.stop.prevent="onClick">
    <q-avatar class="theme-default-q-avatar" :class="{ 'text-white bg-blue-6': count > 0 }">{{ count }}</q-avatar>
    {{ t(label, { count: count }) }}
    <q-tooltip v-if="toolTip && count > 0">{{ t(toolTip) }}</q-tooltip>
  </q-chip>
</template>

<script setup>

import { useI18n } from "vue-i18n";

const { t } = useI18n();

const emit = defineEmits(['click'])

const props = defineProps({
  disable: {
    type: Boolean,
    required: false,
    default: false
  },
  label: {
    type: String,
    required: true
  },
  count: {
    type: Number,
    required: false,
    default: 0,
  },
  toolTip: {
    type: String,
    required: false,
  },
  qChipClass: {
    type: String,
    required: false
  }
});

const onClick = (e) => {
  emit("click", e);
}

</script>