<template>
  <BaseDialog v-model="visible" @close="onClose" width="1280px" max-width="80vw">
    <template v-slot:header-left>
      {{ t('Delete document') }}
    </template>
    <template v-slot:body>
      <div class="q-ma-md">
        <p>
          {{ t("You are about to delete the current document. Are you sure? (this action cannot be undone).") }}
        </p>
        <custom-banner class="q-mt-lg" v-if="state.deleted" success
          text="The document has been deleted. Upon closing this dialog, you will be redirected to the home screen."></custom-banner>
        <CustomErrorBanner v-else-if="state.loadingError" :text="state.errorMessage" :api-error="state.apiError"
          class="q-mt-lg">
        </CustomErrorBanner>
      </div>
    </template>
    <template v-slot:actions>
      <q-btn class="action-secondary" @click.stop="onClose" icon="close" :label="t('Cancel')" :disable="state.loading"
        v-if="!state.deleted" />
      <q-btn color="primary" @click.stop="onDelete" icon="done" :label="t('Ok')" :disable="state.loading"
        v-if="!state.deleted" />
      <q-btn color="primary" @click.stop="onClose" icon="done" :label="t('Close')" v-if="state.deleted" />
    </template>
  </BaseDialog>
</template>

<script setup>
import { ref, reactive, onMounted, onBeforeUnmount } from "vue";
import { useRouter } from "vue-router";
import { useI18n } from "vue-i18n";

import { useBus } from "src/composables/useBus";
import { useAPI } from "src/composables/useAPI";

import { default as BaseDialog } from "src/components/Dialogs/BaseDialog.vue";
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