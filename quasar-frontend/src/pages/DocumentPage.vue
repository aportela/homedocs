<template>
  <q-page>
    <DocumentForm :document-id="documentId" :key="documentId ?? 'new'" />
  </q-page>
</template>

<script setup lang="ts">
  import { computed } from "vue";
  import { useRoute } from "vue-router";
  import { default as DocumentForm } from "src/components/Forms/DocumentForm.vue"


  const route = useRoute();

  const documentId = computed(() => {
    return route.name === "document" && route.params.id
      ? String(route.params.id)
      : null;
  });
</script>