<template>
  <q-btn v-bind="attrs" :icon="fabGithub" no-caps target="_blank">
    <slot></slot>
    <q-tooltip>{{ tooltip }}</q-tooltip>
  </q-btn>
</template>

<script setup>

import { useI18n } from "vue-i18n";

import { useAttrs, computed } from "vue";
import { fabGithub } from "@quasar/extras/fontawesome-v6";

const attrs = useAttrs();

const { t } = useI18n();

const tooltip = computed(() => t("Browse project page"));

</script>