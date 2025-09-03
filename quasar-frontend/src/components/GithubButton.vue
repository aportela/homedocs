<template>
  <q-btn :square="square" :round="round" :dense="dense" :flat="flat" :ripple="ripple" :label="label" :icon="fabGithub"
    size="md" :color="color" class="q-ml-sm" no-caps :href="href" target="_blank" />
</template>

<script setup>

import { defineProps } from "vue";
import { fabGithub } from "@quasar/extras/fontawesome-v6";

const props = defineProps({ 'round': Boolean, 'square': Boolean, 'dense': Boolean, 'flat': Boolean, 'ripple': Boolean, 'color': String, 'label': String, 'href': String });

</script>