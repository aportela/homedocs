<template>
  <q-chip class="theme-default-q-chip shadow-1" :class="{ 'cursor-not-allowed': disable || count < 1 }"
    :clickable="count > 0 && !disable" @click.stop.prevent="onClick">
    <q-avatar class="theme-default-q-avatar" :class="{ 'text-white bg-blue-6': count > 0 }">{{ count }}</q-avatar>
    {{ t(label, { count: count }) }}
    <DesktopToolTip v-if="toolTip && count > 0">{{ t(toolTip) }}</DesktopToolTip>
  </q-chip>
</template>

<script setup lang="ts">

import { useI18n } from "vue-i18n";

import { default as DesktopToolTip } from "src/components/DesktopToolTip.vue";

const { t } = useI18n();

const emit = defineEmits(['click'])

defineProps({
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
    validator(value: number) {
      return (value >= 0);
    }
  },
  toolTip: {
    type: String,
    required: false,
  }
});

const onClick = (e: MouseEvent) => {
  emit("click", e);
}

</script>
