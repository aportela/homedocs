<template>
  <q-chip class="theme-default-q-chip shadow-1" :class="{ 'cursor-not-allowed': disable || count < 1 }"
    :clickable="count > 0 && !disable" @click.stop.prevent="onClick">
    <q-avatar class="theme-default-q-avatar" :class="{ 'text-white bg-blue-6': count > 0 }">{{ count }}</q-avatar>
    {{ t(label, { count: count }) }}
    <DesktopToolTip v-if="localStorageShowToolTips && toolTip && count > 0">{{ t(toolTip) }}</DesktopToolTip>
  </q-chip>
</template>

<script setup lang="ts">
import { useI18n } from "vue-i18n";

import { default as DesktopToolTip } from "src/components/DesktopToolTip.vue";
import { showToolTips as localStorageShowToolTips } from "src/composables/localStorage";

const { t } = useI18n();

const emit = defineEmits(['click'])

interface ViewDocumentDetailsButtonProps {
  disable?: boolean;
  label: string;
  count: number;
  toolTip?: string | null;
};

withDefaults(defineProps<ViewDocumentDetailsButtonProps>(), {
  disable: false
});

const onClick = (evt: Event) => {
  emit("click", evt);
}
</script>