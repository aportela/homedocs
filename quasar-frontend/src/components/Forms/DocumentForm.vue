<template>
  <div @dragover.prevent @dragenter.prevent @drop="handleDrop">
    <!-- hidden quasar native uploader component -->
    <q-uploader ref="uploaderRef" class="hidden" hide-upload-btn no-thumbnails auto-upload field-name="file"
      method="post" url="api2/file" :max-file-size="maxUploadFileSize" multiple @uploaded="onFileUploaded"
      @rejected="onUploadRejected" @failed="onUploadFailed" @start="onUploadsStart" @finish="onUploadsFinish" />
    <q-card flat class="bg-transparent">
      <form @submit.prevent.stop="onSubmitForm" autocorrect="off" autocapitalize="off" autocomplete="off"
        spellcheck="false">
        <q-btn-group class="q-ma-sm" spread>
          <q-btn type="submit" icon="save" size="md" color="primary" :title="t('Save')" :label="t('Save')" no-caps
            @click="onSubmitForm" :disable="loading || saving || uploading || !document.title" :loading="saving">
            <template v-slot:loading>
              <q-spinner-hourglass class="on-left" />
              {{ t("Saving...") }}
            </template>
          </q-btn>
          <q-btn type="button" icon="autorenew" size="md" color="secondary" :title="t('Reload')" :label="t('Reload')"
            no-caps v-if="!isNewDocument" @click="onRefresh" :loading="loading">
            <template v-slot:loading>
              <q-spinner-hourglass class="on-left" />
              {{ t("Loading...") }}
            </template>
          </q-btn>
          <q-btn type="button" icon="delete" size="md" color="red" :title="t('Delete')" :label="t('Delete')" no-caps
            v-if="!isNewDocument" @click="onShowDeleteDocumentConfirmationDialog">
          </q-btn>
        </q-btn-group>
        <q-tabs class="lt-lg q-mb-sm" v-model="smallScreensTopTab">
          <q-tab name="metadata" icon="description" :label="t('Document metadata')"
            class="cursor-default full-width"></q-tab>
          <q-tab name="details" icon="list_alt" :label="t('Document details')"
            class="cursor-default full-width"></q-tab>
        </q-tabs>
        <q-card-section class="row q-pa-none">
          <div class="col-12 col-lg-6 col-xl-6 q-px-sm flex"
            v-show="isScreenGreaterThanMD || smallScreensTopTab == 'metadata'">
            <q-card class="q-ma-xs q-mt-sm full-width">
              <q-card-section class="q-pa-none q-mb-sm gt-md">
                <q-tabs v-model="leftMetadataTab">
                  <q-tab name="metadata" icon="description" :label="t('Document metadata')"
                    class="cursor-default full-width"></q-tab>
                </q-tabs>
              </q-card-section>
              <q-card-section class="q-pa-md">
                <DocumentMetadataTopForm v-if="!isNewDocument" :created-on-timestamp="document.createdOn.timestamp"
                  :last-update-timestamp="document.lastUpdate.timestamp"></DocumentMetadataTopForm>
                <InteractiveTextFieldCustomInput ref="documentTitleFieldRef" dense class="q-mb-md" maxlength="128"
                  outlined v-model.trim="document.title" type="textarea" autogrow name="title"
                  :label="t('Document title')" :disable="loading || saving" :autofocus="true" clearable
                  :start-mode-editable="isNewDocument" :rules="requiredFieldRules" :error="!document.title"
                  :error-message="fieldIsRequiredLabel" :max-lines="1">
                </InteractiveTextFieldCustomInput>
                <InteractiveTextFieldCustomInput dense class="q-mb-md" outlined v-model.trim="document.description"
                  type="textarea" maxlength="4096" autogrow name="description" :label="t('Document description')"
                  :disable="loading || saving" clearable :start-mode-editable="isNewDocument" :max-lines="6">
                </InteractiveTextFieldCustomInput>
                <InteractiveTagsFieldCustomSelect dense v-model="document.tags" :disabled="loading || saving"
                  :start-mode-editable="isNewDocument" :deny-change-editable-mode="isNewDocument" clearable>
                </InteractiveTagsFieldCustomSelect>
              </q-card-section>
            </q-card>
          </div>
          <div class="col-12 col-lg-6 col-xl-6 q-px-sm flex"
            v-show="isScreenGreaterThanMD || smallScreensTopTab == 'details'">
            <q-card class="q-ma-xs q-mt-sm full-width">
              <q-card-section class="q-pa-none q-mb-sm" style="border-bottom: 2px solid rgba(0, 0, 0, 0.12);">
                <q-tabs v-model="rightDetailsTab" align="left">
                  <q-tab name="attachments" icon="attach_file" :disable="state.loading" :label="t('Attachments')">
                    <q-badge floating v-show="document.hasAttachments">{{ document.attachments.length }}</q-badge>
                  </q-tab>
                  <q-tab name="notes" icon="forum" :disable="state.loading" :label="t('Notes')">
                    <q-badge floating v-show="document.hasNotes">{{ document.notes.length }}</q-badge>
                  </q-tab>
                  <q-tab name="history" icon="view_timeline" :disable="state.loading" :label="t('History')"
                    v-if="document.id">
                    <q-badge floating v-show="document.hasHistoryOperations">{{ document.historyOperations.length
                      }}</q-badge>
                  </q-tab>
                </q-tabs>
              </q-card-section>
              <q-card-section class="q-pa-none">
                <q-tab-panels v-model="rightDetailsTab" animated class="bg-transparent">
                  <q-tab-panel name="attachments" class="q-pa-none">
                    <DocumentDetailsAttachments v-model="document.attachments"
                      :disable="loading || saving || state.loading" @add-attachment="onShowAttachmentsPicker"
                      @preview-attachment-at-index="(index) => document.previewAttachment(index)">
                    </DocumentDetailsAttachments>
                  </q-tab-panel>
                  <q-tab-panel name="notes" class="q-pa-none">
                    <DocumentDetailsNotes v-model="document.notes" :disable="loading || saving || state.loading">
                    </DocumentDetailsNotes>
                  </q-tab-panel>
                  <q-tab-panel name="history" class="q-pa-none" v-if="document.id">
                    <DocumentDetailsHistory v-model="document.historyOperations"
                      :disable="loading || saving || state.loading">
                    </DocumentDetailsHistory>
                  </q-tab-panel>
                </q-tab-panels>
              </q-card-section>
            </q-card>
          </div>
        </q-card-section>
        <q-card-section class="q-ma-xs q-mt-sm q-px-xs">
          <CustomBanner v-if="state.saveSuccess" class="q-mt-md" text="Document saved" success></CustomBanner>
          <CustomErrorBanner v-else-if="state.loadingError || state.saveError" class="q-mt-md"
            :api-error="state.apiError" :text="state.errorMessage">
          </CustomErrorBanner>
        </q-card-section>
        <q-btn-group class="q-ma-sm" spread>
          <q-btn type="submit" icon="save" size="md" color="primary" :title="t('Save')" :label="t('Save')" no-caps
            @click="onSubmitForm" :disable="loading || saving || uploading || !document.title" :loading="saving">
            <template v-slot:loading>
              <q-spinner-hourglass class="on-left" />
              {{ t("Saving...") }}
            </template>
          </q-btn>
          <q-btn type="button" icon="autorenew" size="md" color="secondary" :title="t('Reload')" :label="t('Reload')"
            no-caps v-if="!isNewDocument" @click="onRefresh" :loading="loading">
            <template v-slot:loading>
              <q-spinner-hourglass class="on-left" />
              {{ t("Loading...") }}
            </template>
          </q-btn>
          <q-btn type="button" icon="delete" size="md" color="red" :title="t('Delete')" :label="t('Delete')" no-caps
            v-if="!isNewDocument" @click="onShowDeleteDocumentConfirmationDialog">
          </q-btn>
        </q-btn-group>
      </form>
    </q-card>
  </div>
  <DeleteDocumentConfirmationDialog v-if="showDeleteDocumentConfirmationDialog" :document-id="document.id"
    @close="showDeleteDocumentConfirmationDialog = false"></DeleteDocumentConfirmationDialog>
</template>

<script setup>

import { ref, reactive, nextTick, computed, watch, onMounted, onBeforeUnmount } from "vue";
import { useRouter } from "vue-router";
import { useI18n } from "vue-i18n";
import { uid, useQuasar } from "quasar";

import { useBus } from "src/composables/useBus";
import { useAPI } from "src/composables/useAPI";
import { useFormUtils } from "src/composables/useFormUtils"
import { useDocument } from "src/composables/useDocument"
import { useInitialStateStore } from "src/stores/initialState";

import { default as InteractiveTagsFieldCustomSelect } from "src/components/Forms/Fields/InteractiveTagsFieldCustomSelect.vue"
import { default as DocumentMetadataTopForm } from "src/components/Forms/DocumentMetadataTopForm.vue"
import { default as DocumentDetailsAttachments } from "src/components/Forms/DocumentDetailsAttachments.vue"
import { default as DocumentDetailsNotes } from "src/components/Forms/DocumentDetailsNotes.vue"
import { default as DocumentDetailsHistory } from "src/components/Forms/DocumentDetailsHistory.vue"
import { default as InteractiveTextFieldCustomInput } from "src/components/Forms/Fields/InteractiveTextFieldCustomInput.vue"
import { default as CustomBanner } from "src/components/Banners/CustomBanner.vue"
import { default as CustomErrorBanner } from "src/components/Banners/CustomErrorBanner.vue"
import { default as DeleteDocumentConfirmationDialog } from "src/components/Dialogs/DeleteDocumentConfirmationDialog.vue"

const { t } = useI18n();
const router = useRouter();
const { screen } = useQuasar();
const { bus } = useBus();
const { api } = useAPI();
const initialState = useInitialStateStore();
const { requiredFieldRules, fieldIsRequiredLabel } = useFormUtils();

const props = defineProps({
  documentId: {
    type: String,
    required: false,
  }
});

watch(() => props.documentId, val => {
  if (val) {
    document.id = val;
    onRefresh();
  } else {
    document.reset();
    documentTitleFieldRef.value?.unsetReadOnly();
    nextTick(() => documentTitleFieldRef.value?.focus());
  }
});

const isScreenGreaterThanMD = computed(() => screen.gt.md);
const isNewDocument = computed(() => !props.documentId);

const document = useDocument().getNewDocument();

const maxUploadFileSize = computed(() => initialState.maxUploadFileSize);
const uploaderRef = ref(null);
const documentTitleFieldRef = ref(null);

const state = reactive({
  loading: false,
  loadingError: false,
  errorMessage: null,
  apiError: null,
  saveSuccess: false,
  saveError: false,
});

const loading = ref(false);
const saving = ref(false);
const deleting = ref(false);
const uploading = ref(false);

const showDeleteDocumentConfirmationDialog = ref(false);

const smallScreensTopTab = ref("metadata");
const leftMetadataTab = ref("metadata");
const rightDetailsTab = ref("attachments");

// handle drag&drop files into q-uploader component
const handleDrop = (event) => {
  event.preventDefault();
  const files = event.dataTransfer.files;
  if (files.length) {
    uploaderRef.value?.addFiles(files);
  };
};

// refresh document data from api
const onRefresh = () => {
  if (document.id) {
    loading.value = true;
    state.loading = true;
    state.loadingError = false;
    state.errorMessage = null;
    state.apiError = null;
    state.saveSuccess = false;
    state.saveError = false;
    smallScreensTopTab.value = "metadata";
    api.document
      .get(document.id)
      .then((successResponse) => {
        document.setFromAPIJSON(successResponse.data.document);
        loading.value = false;
        state.loading = false;
        nextTick(() => documentTitleFieldRef.value?.focus());
      })
      .catch((errorResponse) => {
        loading.value = false;
        state.apiError = errorResponse.customAPIErrorDetails;
        switch (errorResponse.response.status) {
          case 401:
            state.apiError = errorResponse.customAPIErrorDetails;
            state.errorMessage = "Auth session expired, requesting new...";
            bus.emit("reAuthRequired", { emitter: "DocumentPage.onRefresh" });
            break;
          default:
            // TODO: on this error (example 404 not found) do not use error validation fields on title (required, red border, this field is required)
            state.loadingError = true;
            state.errorMessage = "API Error: fatal error";
            break;
        }
        state.loading = false;
      });
  } else {
    // TODO
  }
}

// submit (add/update) document data to api
const onSubmitForm = () => {
  loading.value = true;
  state.loading = true;
  state.loadingError = false;
  state.errorMessage = null;
  state.apiError = null;
  state.saveSuccess = false;
  state.saveError = false;
  saving.value = true;
  if (!isNewDocument.value) {
    api.document
      .update(document)
      .then((successResponse) => {
        if (successResponse.data.document) {
          document.setFromAPIJSON(successResponse.data.document);
          loading.value = false;
          state.loading = false;
          saving.value = false;
          state.saveSuccess = true;
          if (smallScreensTopTab.value == "metadata") {
            nextTick(() => {
              documentTitleFieldRef.value?.focus();
            });
          }
        } else {
          // TODO
          loading.value = false;
        }
      })
      .catch((errorResponse) => {
        loading.value = false;
        state.apiError = errorResponse.customAPIErrorDetails;
        state.saveError = true;
        saving.value = false;
        switch (errorResponse.response.status) {
          case 400:
            if (
              errorResponse.response.data.invalidOrMissingParams.find(function (e) {
                return e === "title";
              })
            ) {
              state.errorMessage = t("API Error: missing document title param");
              smallScreensTopTab.value = "metadata";
              nextTick(() => documentTitleFieldRef.value?.focus());
            } else if (
              errorResponse.response.data.invalidOrMissingParams.find(function (e) {
                return e === "note_body";
              })
            ) {
              state.errorMessage = t("API Error: missing document note body");
              smallScreensTopTab.value = "details";
              rightDetailsTab.value = "notes";
              // TODO: focus note without body ???
            } else {
              state.errorMessage = "API Error: fatal error";
            }
            break;
          case 401:
            state.errorMessage = "Auth session expired, requesting new...";
            bus.emit("reAuthRequired", { emitter: "AdvancedSearchPage.onSubmitForm" });
            break;
          default:
            state.errorMessage = "API Error: fatal error";
            break;
        }
        state.loading = false;
      });
  } else {
    if (!document.id) {
      document.id = uid();
    }
    api.document
      .add(document)
      .then((successResponse) => {
        loading.value = false;
        saving.value = false;
        router.push({
          name: "document",
          params: { id: document.id }
        });
      })
      .catch((errorResponse) => {
        document.id = null;
        loading.value = false;
        state.apiError = errorResponse.customAPIErrorDetails;
        state.saveError = true;
        saving.value = false;
        switch (errorResponse.response.status) {
          case 400:
            if (
              errorResponse.response.data.invalidOrMissingParams.find(function (e) {
                return e === "title";
              })
            ) {
              state.errorMessage = t("API Error: missing document title param");
              smallScreensTopTab.value = "metadata";
              nextTick(() => documentTitleFieldRef.value?.focus());
            } else if (
              errorResponse.response.data.invalidOrMissingParams.find(function (e) {
                return e === "note_body";
              })
            ) {
              state.errorMessage = t("API Error: missing document note body");
              smallScreensTopTab.value = "details";
              rightDetailsTab.value = "notes";
              // TODO: focus note without body ???
            } else {
              state.errorMessage = "API Error: fatal error";
            }
            break;
          case 401:
            state.errorMessage = "Auth session expired, requesting new...";
            bus.emit("reAuthRequired", { emitter: "AdvancedSearchPage.onSubmitForm" });
            break;
          default:
            state.errorMessage = "API Error: fatal error";
            break;
        }
        state.loading = false;
      });
  }
}

const onShowDeleteDocumentConfirmationDialog = () => {
  if (document.id) {
    showDeleteDocumentConfirmationDialog.value = true;
  }
};

const onShowAttachmentsPicker = () => {
  rightDetailsTab.value = 'attachments';
  nextTick(() => {
    uploaderRef.value.pickFiles();
  });
}

// q-uploader component event => file upload starts
const onUploadsStart = (e) => {
  uploading.value = true;
  bus.emit("showUploadingDialog", { transfers: uploaderRef.value?.files.map((file) => { return { name: file.name, size: file.size } }) });
}

// q-uploader component event => file was uploaded
const onFileUploaded = (e) => {
  bus.emit("refreshUploadingDialog.fileUploaded", { transfers: e.files.map((file) => { return { name: file.name, size: file.size } }) });
  document.addFile((JSON.parse(e.xhr.response).data).id, e.files[0].name, e.files[0].size);
}

// q-uploader component event => file upload is rejected
const onUploadRejected = (e) => {
  const transfers =
    e.map((error) => {
      return ({
        name: error.file.name,
        size: error.file.size,
        error: {
          status: error.failedPropValidation == "max-file-size" ? 413 : 500,
          statusText: error.failedPropValidation == "max-file-size" ? "Content Too Large" : "Internal Server Error"
        }
      });
    });
  bus.emit("refreshUploadingDialog.fileUploadRejected", { transfers: transfers });
}

// q-uploader component event => file upload failed
const onUploadFailed = (e) => {
  const transfers =
    e.map((error) => {
      return ({
        name: error.file.name,
        size: error.file.size,
        error: {
          status: xhr.status,
          statusText: xhr.statusText
        }
      });
    });
  bus.emit("refreshUploadingDialog.fileUploadFailed", { transfers: transfers });
}


// q-uploader component event => file upload finish (all files)
const onUploadsFinish = () => {
  uploading.value = false;
  uploaderRef.value?.reset();
}

onMounted(() => {
  if (!isNewDocument.value) { // existent document
    if (props.documentId) {
      document.id = props.documentId;
      onRefresh();
    } else {
      // TODO: ROUTE ERROR (MISSING PARAM)
    }
  } else { // new document
  }
  bus.on("reAuthSucess", (msg) => {
    if (msg.to?.includes("DocumentPage.onRefresh")) {
      onRefresh();
    }
    if (msg.to?.includes("DocumentPage.onSubmitForm")) {
      onSubmitForm();
    }
  });
});

onBeforeUnmount(() => {
  bus.off("reAuthSucess");
});

</script>