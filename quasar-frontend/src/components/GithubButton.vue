<template>
  <q-btn v-bind="attrs" :icon="fabGithub" no-caps target="_blank">
    <slot></slot>
  </q-btn>
</template>

<script setup>

import { useAttrs } from "vue";
import { fabGithub } from "@quasar/extras/fontawesome-v6";

const attrs = useAttrs();

</script>