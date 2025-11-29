<template>
  <q-page>
    <DocumentForm :document-id="documentId" />
  </q-page>
</template>

<script setup lang="ts">
import { ref } from "vue";
import { useRoute, useRouter } from "vue-router";

import { default as DocumentForm } from "src/components/Forms/DocumentForm.vue"

const router = useRouter();
const currentRoute = useRoute();

const documentId = ref<string | null>(currentRoute.name == "document" ? String(currentRoute.params?.id) || null : null);

router.beforeEach((to) => {
  if (to.name == "newDocument") {
    documentId.value = null;
  } else if (to.name == "document" && to.params.id) {
    documentId.value = String(to.params.id);
  } else {
    // TODO: error
  }
});
</script>