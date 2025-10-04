<template>
  <q-dialog v-model="visible" @hide="onClose">
    <q-card class="q-card-delete-document-dialog">
      <q-card-section class="row items-center q-pb-none">
        <div class="text-h6">{{ t('Delete document') }}</div>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup aria-label="Close modal" />
      </q-card-section>
      <q-separator class="q-mb-md"></q-separator>
      <q-card-section class="q-p-none">
        {{ t("You are about to delete the current document. Are you sure? (this action cannot be undone).") }}
        <custom-banner class="q-mt-lg" v-if="state.deleted" success
          text="The document has been deleted. Upon closing this dialog, you will be redirected to the home screen."></custom-banner>
        <CustomErrorBanner v-else-if="state.loadingError" :text="state.errorMessage" :api-error="state.apiError"
          class="q-mt-lg">
        </CustomErrorBanner>
      </q-card-section>
      <q-separator class="q-my-sm"></q-separator>
      <q-card-actions align="right">
        <q-btn class="action-secondary" @click.stop="onClose" icon="close" :label="t('Cancel')" :disable="state.loading"
          v-if="!state.deleted" />
        <q-btn color="primary" @click.stop="onDelete" icon="done" :label="t('Ok')" :disable="state.loading"
          v-if="!state.deleted" />
        <q-btn color="primary" @click.stop="onClose" icon="done" :label="t('Close')" v-if="state.deleted" />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref, reactive, onMounted, onBeforeUnmount } from "vue";
import { useRouter } from "vue-router";
import { useI18n } from "vue-i18n";

import { useBus } from "src/composables/useBus";
import { useAPI } from "src/composables/useAPI";

import { default as CustomBanner } from "src/components/Banners/CustomBanner.vue"
import { default as CustomErrorBanner } from "src/components/Banners/CustomErrorBanner.vue"

const { t } = useI18n();

const emit = defineEmits(['close']);

const { bus } = useBus();
const { api } = useAPI();

const router = useRouter();

const props = defineProps({
  documentId: {
    type: String,
    required: true,
  }
});

const visible = ref(true);

const state = reactive({
  loading: false,
  loadingError: false,
  errorMessage: null,
  apiError: null,
  deleted: false
});


const onDelete = () => {
  state.loading = true;
  state.loadingError = false;
  state.errorMessage = null;
  state.apiError = null;
  api.document.
    remove(props.documentId)
    .then((successResponse) => {
      state.loading = false;
      state.deleted = true;
    })
    .catch((errorResponse) => {
      state.loadingError = true;
      state.apiError = errorResponse.customAPIErrorDetails;
      switch (errorResponse.response.status) {
        case 401:
          state.errorMessage = "Auth session expired, requesting new...";
          bus.emit("reAuthRequired", { emitter: "DeleteDocumentConfirmationDialog.onDelete" });
          break;
        case 403: // access denied
          state.errorMessage = "Error removing (non existent) document"; // TODO
          break;
        case 404: // document not found
          state.errorMessage = "Error removing document: access denied"; // TODO
          break;
        default:
          state.errorMessage = "API Error: fatal error";
          break;
      }
      state.loading = false;
    });
}

const onClose = () => {
  visible.value = false;
  if (!state.deleted) {
    emit('close');
  } else {
    router.push({
      name: "index",
    });
  }
}

onMounted(() => {
  bus.on("reAuthSucess", (msg) => {
    if (msg.to?.includes("DeleteDocumentConfirmationDialog.onDelete")) {
      onDelete();
    }
  });
});

onBeforeUnmount(() => {
  bus.off("reAuthSucess");
});

</script>

<style lang="css" scoped>
.q-card-delete-document-dialog {
  width: 1280px;
  max-width: 80vw;
}
</style>
