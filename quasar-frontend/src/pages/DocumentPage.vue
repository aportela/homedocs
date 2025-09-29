<template>
  <q-page>
    <DocumentForm :document-id="documentId"></DocumentForm>
  </q-page>
</template>

<script setup>

import { ref } from "vue";
import { useRouter } from "vue-router";
import { useI18n } from "vue-i18n";

import { default as DocumentForm } from "src/components/Forms/DocumentForm.vue"

const { t } = useI18n();
const router = useRouter();

const documentId = ref(null);

router.beforeEach(async (to, from) => {
  if (to.name == "newDocument") {
    // new document, reset form fields
    //document.reset();
  } else if (from.name == "newDocument" && to.name == "document" && to.params.id) {
    // existent document from creation
    //document.reset();
    //document.id = to.params.id
    //onRefresh();
  } else if (from.name != "newDocument" && to.name == "document" && to.params.id) {
    // existent document
    //document.reset();
    //document.id = to.params.id
    //onRefresh();
  }
});
</script>