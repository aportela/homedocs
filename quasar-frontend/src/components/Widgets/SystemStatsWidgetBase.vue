<template>
  <q-card class="q-ma-sm">
    <q-card-section class="text-center text-h6 theme-default-q-card-section-header">
      <q-icon :name="loading ? 'settings' : error ? 'error' : icon"
        :class="{ 'text-red': error, 'animation-spin': loading }" />
      {{ t(headerLabel) }}
    </q-card-section>
    <q-card-section class="text-center text-h4" v-if="!error">{{ value }}</q-card-section>
    <q-card-section v-else>
      <CustomErrorBanner :text="errorMessage || 'Error loading data'" :api-error-details="apiErrorDetails">
      </CustomErrorBanner>
    </q-card-section>
  </q-card>
</template>

<script setup lang="ts">

import { useI18n } from "vue-i18n";

import { type APIErrorDetails as APIErrorDetailsInterface } from "src/types/apiErrorDetails";
import { default as CustomErrorBanner } from "src/components/Banners/CustomErrorBanner.vue";

const { t } = useI18n();

interface SystemStatsCountWidgetBaseProps {
  loading: boolean;
  error: boolean;
  icon: string;
  headerLabel: string;
  value: number | string;
  errorMessage: string | null;
  apiErrorDetails: APIErrorDetailsInterface | null;
};

defineProps<SystemStatsCountWidgetBaseProps>();

</script>
