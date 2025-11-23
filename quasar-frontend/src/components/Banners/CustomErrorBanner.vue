<template>
  <CustomBanner :text="text" error>
    <template v-slot:details v-if="serverEnvironment.isCurrentEnvironmentDevelopment && apiError">
      <APIErrorDetails class="q-mt-md" :api-error="apiError"></APIErrorDetails>
    </template>
  </CustomBanner>
</template>

<script setup>

import { useServerEnvironmentStore } from "src/stores/serverEnvironment";

import { default as CustomBanner } from "src/components/Banners/CustomBanner.vue";
import { default as APIErrorDetails } from "src/components/APIErrorDetails.vue";

const serverEnvironment = useServerEnvironmentStore();

const props = defineProps({
  text: {
    type: String,
    required: true
  },
  apiError: {
    type: Object,
    required: false,
    default: null
  }
});

</script>