<template>
  <q-banner dense rounded class="theme-default-q-banner"
    :class="{ 'theme-default-q-banner-success': success, 'theme-default-q-banner-error': error }">
    <template v-slot:avatar v-if="success || error">
      <q-icon size="sm" v-if="success" name="check" class="text-green" />
      <q-icon size="sm" v-else-if="error" name="error" class="text-red" />
      <q-icon size="sm" v-else-if="warning" name="warning" class="text-warning" />
    </template>
    {{ t(text) }}
    <slot name="details"></slot>
  </q-banner>
</template>

<script setup>

import { useI18n } from "vue-i18n";

const { t } = useI18n();

const props = defineProps({
  text: String,
  success: Boolean,
  error: Boolean,
  warning: Boolean
});

</script>