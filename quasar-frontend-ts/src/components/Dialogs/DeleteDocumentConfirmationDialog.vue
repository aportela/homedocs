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
        <custom-banner class="q-mt-lg" v-if="deleted" success
          text="The document has been deleted. Upon closing this dialog, you will be redirected to the home screen."></custom-banner>
        <CustomErrorBanner v-else-if="state.ajaxErrors && state.ajaxErrorMessage" :text="state.ajaxErrorMessage"
          :api-error="state.ajaxAPIErrorDetails" class="q-mt-lg">
        </CustomErrorBanner>
      </div>
    </template>
    <template v-slot:actions>
      <q-btn class="action-secondary" @click.stop="onClose" icon="close" :label="t('Cancel')"
        :disable="state.ajaxRunning" v-if="!deleted" />
      <q-btn color="primary" @click.stop="onDelete" icon="done" :label="t('Ok')" :disable="state.ajaxRunning"
        v-if="!deleted" />
      <q-btn color="primary" @click.stop="onClose" icon="done" :label="t('Close')" v-if="deleted" />
    </template>
  </BaseDialog>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, onBeforeUnmount } from "vue";
import { useRouter } from "vue-router";
import { useI18n } from "vue-i18n";

import { useBus } from "src/composables/useBus";
import { useAPI } from "src/composables/useAPI";

import { type AjaxState as AjaxStateInterface, defaultAjaxState } from "src/types/ajax-state";

import { default as BaseDialog } from "src/components/Dialogs/BaseDialog.vue";
import { default as CustomBanner } from "src/components/Banners/CustomBanner.vue"
import { default as CustomErrorBanner } from "src/components/Banners/CustomErrorBanner.vue"

const { t } = useI18n();

const emit = defineEmits(['close']);

const { bus } = useBus();
const { api } = useAPI();

const router = useRouter();

interface DeleteDocumentConfirmationDialogProps {
  documentId: string;
};

const props = defineProps<DeleteDocumentConfirmationDialogProps>();

const visible = ref(true);

const state: AjaxStateInterface = reactive({ ...defaultAjaxState });

const deleted = ref(false);

const onDelete = () => {
  Object.assign(state, defaultAjaxState);
  state.ajaxRunning = true;
  api.document.
    remove(props.documentId)
    .then(() => {
      state.ajaxRunning = false;
      deleted.value = true;
    })
    .catch((errorResponse) => {
      state.ajaxErrors = true;
      if (errorResponse.isAPIError) {
        state.ajaxAPIErrorDetails = errorResponse.customAPIErrorDetails;
        switch (errorResponse.response.status) {
          case 401:
            state.ajaxErrorMessage = "Auth session expired, requesting new...";
            bus.emit("reAuthRequired", { emitter: "DeleteDocumentConfirmationDialog.onDelete" });
            break;
          case 403: // access denied
            state.ajaxErrorMessage = "Error removing (non existent) document"; // TODO
            break;
          case 404: // document not found
            state.ajaxErrorMessage = "Error removing document: access denied"; // TODO
            break;
          default:
            state.ajaxErrorMessage = "API Error: fatal error";
            break;
        }
      } else {
        state.ajaxErrorMessage = `Uncaught exception: ${errorResponse}`;
        console.error(errorResponse);
      }
      state.ajaxRunning = false;
    });
}

const onClose = () => {
  visible.value = false;
  if (!deleted.value) {
    emit('close');
  } else {
    router.push({
      name: "index",
    }).catch((e) => {
      console.error(e);
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