<template>
  <q-page>
    <DocumentForm :document-id="documentId"></DocumentForm>
  </q-page>
</template>

<script setup>

import { ref, computed } from "vue";
import { useRouter } from "vue-router";

import { default as DocumentForm } from "src/components/Forms/DocumentForm.vue"

const router = useRouter();

const documentId = computed(() => currentDocumentIdRouteParam.value || null);

const currentDocumentIdRouteParam = ref(null);

router.beforeEach(async (to, from) => {
  if (to.name == "newDocument") {
    currentDocumentIdRouteParam.value = null;
  } else if (to.name == "document" && to.params.id) {
    currentDocumentIdRouteParam.value = to.params.id;
  } else {
    // TODO: error
  }
});

</script>