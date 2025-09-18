<template>
  <q-banner dense rounded class="q-my-md theme-default-q-banner"
    :class="{ 'theme-default-q-banner-success': success, 'theme-default-q-banner-error': error }">
    <template v-slot:avatar v-if="success || error">
      <q-icon v-if="success" name="check" class="text-green" />
      <q-icon v-else-if="error" name="error" class="text-red" />
    </template>
    {{ t(text) }}
  </q-banner>
</template>

<script setup>

import { useI18n } from "vue-i18n";

const { t } = useI18n();

const props = defineProps({
  text: String,
  success: Boolean,
  error: Boolean,
});

</script>