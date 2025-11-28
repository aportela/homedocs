<template>
  <div @dragover.prevent @dragenter.prevent @drop="handleDrop">
    <!-- hidden quasar native uploader component -->
    <q-uploader ref="uploaderRef" class="hidden" hide-upload-btn no-thumbnails auto-upload field-name="file"
      method="POST" url="api3/attachment" :max-file-size="maxUploadFileSize" multiple @uploaded="onFileUploaded"
      @rejected="onUploadRejected" @failed="onUploadFailed" @start="onUploadsStart" @finish="onUploadsFinish" />
    <q-card flat class="bg-transparent">
      <form @submit.prevent.stop="onSubmitForm" autocorrect="off" autocapitalize="off" autocomplete="off"
        spellcheck="false">
        <q-btn-group class="q-ma-sm" spread v-if="exists">
          <q-btn type="submit" icon="save" size="md" color="primary" :title="t('Save')" :label="t('Save')" no-caps
            @click="onSubmitForm" :disable="state.ajaxRunning || isUploading || !document.title"
            :loading="state.ajaxRunning">
            <template v-slot:loading>
              <q-spinner-hourglass class="on-left" />
              {{ t("Saving...") }}
            </template>
          </q-btn>
          <q-btn type="button" icon="autorenew" size="md" color="secondary" :title="t('Reload')" :label="t('Reload')"
            no-caps v-if="!isNewDocument" @click="onRefresh" :loading="state.ajaxRunning">
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
        <q-card-section class="row q-pa-none" v-if="exists">
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
                <DocumentMetadataTopForm v-if="!isNewDocument" :created-at="document.createdAt"
                  :updated-at="document.updatedAt" />
                <InteractiveTextFieldCustomInput ref="documentTitleFieldRef" dense class="q-mb-md" maxlength="128"
                  outlined v-model.trim="document.title" type="textarea" autogrow name="title"
                  :label="t('Document title')" :disable="state.ajaxRunning" :autofocus="true" clearable
                  :start-mode-editable="isNewDocument" :rules="[requiredFieldRule]" :error="!document.title"
                  :error-message="fieldIsRequiredLabel" :max-lines="1">
                </InteractiveTextFieldCustomInput>
                <InteractiveTextFieldCustomInput dense class="q-mb-md" outlined v-model.trim="document.description"
                  type="textarea" maxlength="4096" autogrow name="description" :label="t('Document description')"
                  :disable="state.ajaxRunning" clearable :start-mode-editable="isNewDocument" :max-lines="6">
                </InteractiveTextFieldCustomInput>
                <InteractiveTagsFieldCustomSelect dense v-model="document.tags" :disabled="state.ajaxRunning"
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
                  <q-tab name="attachments" icon="attach_file" :disable="state.ajaxRunning" :label="t('Attachments')">
                    <q-badge floating v-show="document.hasAttachments">{{ document.attachments.length }}</q-badge>
                  </q-tab>
                  <q-tab name="notes" icon="forum" :disable="state.ajaxRunning" :label="t('Notes')">
                    <q-badge floating v-show="document.hasNotes">{{ document.notes.length }}</q-badge>
                  </q-tab>
                  <q-tab name="history" icon="view_timeline" :disable="state.ajaxRunning" :label="t('History')"
                    v-if="document.id">
                    <q-badge floating v-show="document.hasHistoryOperations">{{ document.historyOperations.length
                    }}</q-badge>
                  </q-tab>
                </q-tabs>
              </q-card-section>
              <q-card-section class="q-pa-none">
                <q-tab-panels v-model="rightDetailsTab" animated class="bg-transparent">
                  <q-tab-panel name="attachments" class="q-pa-none">
                    <DocumentDetailsAttachments :attachments="document.attachments" :disable="state.ajaxRunning"
                      @add-attachment="onShowAttachmentsPicker"
                      @preview-attachment-at-index="(index: number) => document.previewAttachment(index)"
                      @remove-attachment-at-index="(index: number) => onRemoveAttachmentAtIndex(index)" />
                  </q-tab-panel>
                  <q-tab-panel name="notes" class="q-pa-none">
                    <DocumentDetailsNotes :notes="document.notes" :disable="state.ajaxRunning" @add-note="onAddNote"
                      @remove-note-at-index="(index: number) => onRemoveNoteAtIndex(index)" />
                  </q-tab-panel>
                  <q-tab-panel name="history" class="q-pa-none" v-if="document.id">
                    <DocumentDetailsHistory v-model="document.historyOperations" :disable="state.ajaxRunning" />
                  </q-tab-panel>
                </q-tab-panels>
              </q-card-section>
            </q-card>
          </div>
        </q-card-section>
        <q-card-section class="q-ma-xs q-mt-sm q-px-xs">
          <CustomBanner v-if="saveSuccess" class="q-mt-md" text="Document saved" success></CustomBanner>
          <CustomErrorBanner v-else-if="state.ajaxErrors && state.ajaxErrorMessage" class="q-mt-md"
            :api-error="state.ajaxAPIErrorDetails" :text="state.ajaxErrorMessage">
          </CustomErrorBanner>
        </q-card-section>
        <q-btn-group class="q-ma-sm" spread v-if="exists">
          <q-btn type="submit" icon="save" size="md" color="primary" :title="t('Save')" :label="t('Save')" no-caps
            @click="onSubmitForm" :disable="state.ajaxRunning || isUploading || !document.title"
            :loading="state.ajaxRunning">
            <template v-slot:loading>
              <q-spinner-hourglass class="on-left" />
              {{ t("Saving...") }}
            </template>
          </q-btn>
          <q-btn type="button" icon="autorenew" size="md" color="secondary" :title="t('Reload')" :label="t('Reload')"
            no-caps v-if="!isNewDocument" @click="onRefresh" :loading="state.ajaxRunning">
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
  <DeleteDocumentConfirmationDialog v-if="showDeleteDocumentConfirmationDialog && document.id"
    :document-id="document.id" @close="showDeleteDocumentConfirmationDialog = false" />
</template>

<script setup lang="ts">

import { ref, reactive, nextTick, computed, watch, onMounted, onBeforeUnmount } from "vue";
import { useRouter } from "vue-router";
import { useI18n } from "vue-i18n";
import { uid, useQuasar, QUploader, type QRejectedEntry } from "quasar";

import { bus } from "src/composables/bus";
import { api } from "src/composables/api";
import { useFormUtils } from "src/composables/useFormUtils"
import { DocumentClass } from "src/types/document";
import { useServerEnvironmentStore } from "src/stores/serverEnvironment";

import { type AjaxState as AjaxStateInterface, defaultAjaxState } from "src/types/ajax-state";
import { type GetDocumentResponse as GetDocumentResponseInterface } from "src/types/api-responses";
import { UploadTransferClass } from "src/types/upload-transfer";

import { default as InteractiveTagsFieldCustomSelect } from "src/components/Forms/Fields/InteractiveTagsFieldCustomSelect.vue"
import { default as DocumentMetadataTopForm } from "src/components/Forms/DocumentMetadataTopForm.vue"
import { default as DocumentDetailsAttachments } from "src/components/Forms/DocumentDetailsAttachments.vue"
import { default as DocumentDetailsNotes } from "src/components/Forms/DocumentDetailsNotes.vue"
import { default as DocumentDetailsHistory } from "src/components/Forms/DocumentDetailsHistory.vue"
import { default as InteractiveTextFieldCustomInput } from "src/components/Forms/Fields/InteractiveTextFieldCustomInput.vue"
import { default as CustomBanner } from "src/components/Banners/CustomBanner.vue"
import { default as CustomErrorBanner } from "src/components/Banners/CustomErrorBanner.vue"
import { default as DeleteDocumentConfirmationDialog } from "src/components/Dialogs/DeleteDocumentConfirmationDialog.vue"
import { AttachmentClass } from "src/types/attachment";
import { NoteClass } from "src/types/note";
import { DateTimeClass } from "src/types/date-time";
import { currentTimestamp } from "src/composables/dateUtils";

const { t } = useI18n();
const router = useRouter();
const { screen } = useQuasar();
const serverEnvironment = useServerEnvironmentStore();
const { requiredFieldRule, fieldIsRequiredLabel } = useFormUtils();

interface DocumentFormProps {
  documentId: string | null;
};

const props = defineProps<DocumentFormProps>();

watch(() => props.documentId, val => {
  if (val) {
    document.id = val;
    onRefresh();
  } else {
    document.reset();
    documentTitleFieldRef.value?.unsetReadOnly();
    nextTick()
      .then(() => {
        documentTitleFieldRef.value?.focus()
      }).catch((e) => {
        console.error(e);
      });
  }
});

const isScreenGreaterThanMD = computed(() => screen.gt.md);
const isNewDocument = computed(() => !props.documentId);

const document = reactive<DocumentClass>(
  new DocumentClass()
);

const maxUploadFileSize = computed(() => serverEnvironment.maxUploadFileSize);
const uploaderRef = ref<QUploader | null>(null);
const documentTitleFieldRef = ref<InstanceType<typeof InteractiveTextFieldCustomInput> | null>(null);

const exists = ref<boolean>(true);
const saveSuccess = ref<boolean>(false);

const isUploading = ref<boolean>(false);

const state: AjaxStateInterface = reactive({ ...defaultAjaxState });

const showDeleteDocumentConfirmationDialog = ref(false);

const smallScreensTopTab = ref("metadata");
const leftMetadataTab = ref("metadata");
const rightDetailsTab = ref("attachments");

// handle drag&drop files into q-uploader component
const handleDrop = (evt: DragEvent) => {
  evt.preventDefault();
  const files = evt.dataTransfer?.files || [];
  if (files.length) {
    uploaderRef.value?.addFiles(files);
  };
};


// refresh document data from api
const onRefresh = () => {
  if (document.id) {
    if (!state.ajaxRunning) {
      exists.value = true;
      Object.assign(state, defaultAjaxState);
      state.ajaxRunning = true;
      smallScreensTopTab.value = "metadata";
      api.document
        .get(document.id)
        .then((successResponse: GetDocumentResponseInterface) => {
          document.parseJSONResponse(t, successResponse);
        })
        .catch((errorResponse) => {
          state.ajaxErrors = true;
          if (errorResponse.isAPIError) {
            state.ajaxAPIErrorDetails = errorResponse.customAPIErrorDetails;
            switch (errorResponse.response.status) {
              case 401:
                state.ajaxErrorMessage = "Auth session expired, requesting new...";
                bus.emit("reAuthRequired", { emitter: "DocumentPage.onRefresh" });
                break;
              case 404:
                state.ajaxErrorMessage = "Document not found";
                exists.value = false;
                break;
              default:
                // TODO: on this error (example 404 not found) do not use error validation fields on title (required, red border, this field is required)
                state.ajaxErrorMessage = "API Error: fatal error";
                break;
            }
          } else {
            state.ajaxErrorMessage = `Uncaught exception: ${errorResponse}`;
            console.error(errorResponse);
          }
        }).finally(() => {
          state.ajaxRunning = false;
          if (!state.ajaxErrors) {
            if (smallScreensTopTab.value == "metadata") {
              nextTick()
                .then(() => {
                  documentTitleFieldRef.value?.focus();
                }).catch((e) => {
                  console.error(e);
                });
            }
          }
        });
    }
  } else {
    // TODO
  }

}

// submit (add/update) document data to api
const onSubmitForm = () => {
  if (!state.ajaxRunning) {
    saveSuccess.value = false;
    Object.assign(state, defaultAjaxState);
    state.ajaxRunning = true;
    if (!isNewDocument.value) {
      api.document
        .update(document)
        .then((successResponse: GetDocumentResponseInterface) => {
          document.parseJSONResponse(t, successResponse);
        })
        .catch((errorResponse) => {
          state.ajaxErrors = true;
          if (errorResponse.isAPIError) {
            state.ajaxAPIErrorDetails = errorResponse.customAPIErrorDetails;
            switch (errorResponse.response.status) {
              case 400:
                if (
                  errorResponse.response.data.invalidOrMissingParams.find(function (e: string) {
                    return e === "title";
                  })
                ) {
                  state.ajaxErrorMessage = t("API Error: missing document title param");
                  smallScreensTopTab.value = "metadata";
                  nextTick()
                    .then(() => {
                      documentTitleFieldRef.value?.focus()
                    }).catch((e) => {
                      console.error(e);
                    });
                } else if (
                  errorResponse.response.data.invalidOrMissingParams.find(function (e: string) {
                    return e === "noteBody";
                  })
                ) {
                  state.ajaxErrorMessage = t("API Error: missing document note body");
                  smallScreensTopTab.value = "details";
                  rightDetailsTab.value = "notes";
                  // TODO: focus note without body ???
                } else {
                  state.ajaxErrorMessage = "API Error: fatal error";
                }
                break;
              case 401:
                state.ajaxErrorMessage = "Auth session expired, requesting new...";
                bus.emit("reAuthRequired", { emitter: "AdvancedSearchPage.onSubmitForm" });
                break;
              default:
                state.ajaxErrorMessage = "API Error: fatal error";
                break;
            }
          } else {
            state.ajaxErrorMessage = `Uncaught exception: ${errorResponse}`;
            console.error(errorResponse);
          }
        }).finally(() => {
          state.ajaxRunning = false;
          if (!state.ajaxErrors) {
            saveSuccess.value = true;
            if (smallScreensTopTab.value == "metadata") {
              nextTick()
                .then(() => {
                  documentTitleFieldRef.value?.focus();
                }).catch((e) => {
                  console.error(e);
                });
            }
          }
        });
    } else {
      if (!document.id) {
        document.id = uid();
      }
      api.document
        .add(document)
        .then(() => {
          router.push({
            name: "document",
            params: { id: document.id }
          }).catch((e) => {
            console.error(e);
          });
        })
        .catch((errorResponse) => {
          state.ajaxErrors = true;
          if (errorResponse.isAPIError) {
            state.ajaxAPIErrorDetails = errorResponse.customAPIErrorDetails;
            switch (errorResponse.response.status) {
              case 400:
                if (
                  errorResponse.response.data.invalidOrMissingParams.find(function (e: string) {
                    return e === "title";
                  })
                ) {
                  state.ajaxErrorMessage = t("API Error: missing document title param");
                  smallScreensTopTab.value = "metadata";
                  nextTick()
                    .then(() => {
                      documentTitleFieldRef.value?.focus()
                    }).catch((e) => {
                      console.error(e);
                    });
                } else if (
                  errorResponse.response.data.invalidOrMissingParams.find(function (e: string) {
                    return e === "noteBody";
                  })
                ) {
                  state.ajaxErrorMessage = t("API Error: missing document note body");
                  smallScreensTopTab.value = "details";
                  rightDetailsTab.value = "notes";
                  // TODO: focus note without body ???
                } else {
                  state.ajaxErrorMessage = "API Error: fatal error";
                }
                break;
              case 401:
                state.ajaxErrorMessage = "Auth session expired, requesting new...";
                bus.emit("reAuthRequired", { emitter: "AdvancedSearchPage.onSubmitForm" });
                break;
              default:
                state.ajaxErrorMessage = "API Error: fatal error";
                break;
            }
          } else {
            state.ajaxErrorMessage = `Uncaught exception: ${errorResponse}`;
            console.error(errorResponse);
          }
        }).finally(() => {
          state.ajaxRunning = false;
          if (!state.ajaxErrors) {
            saveSuccess.value = true;
            if (smallScreensTopTab.value == "metadata") {
              nextTick()
                .then(() => {
                  documentTitleFieldRef.value?.focus();
                }).catch((e) => {
                  console.error(e);
                });
            }
          }
        });
    }
  }
}

const onShowDeleteDocumentConfirmationDialog = () => {
  if (document.id) {
    showDeleteDocumentConfirmationDialog.value = true;
  }
};

const onShowAttachmentsPicker = (evt: Event) => {
  rightDetailsTab.value = 'attachments';
  nextTick()
    .then(() => {
      uploaderRef.value?.pickFiles(evt);
    }).catch((e) => {
      console.error(e);
    });
}

const onRemoveAttachmentAtIndex = (index: number) => {
  // orphaned elements are uploaded to server, but not associated (until document saved)
  // so we must remove them
  if (document.attachments[index]!.orphaned) {
    Object.assign(state, defaultAjaxState);
    state.ajaxRunning = true;
    api.document.
      removeFile(document.attachments[index]!.id)
      .then(() => {
        document.attachments = document.attachments.filter((_, i) => i !== index);
      })
      .catch((errorResponse) => {
        state.ajaxErrors = true;
        if (errorResponse.isAPIError) {
          state.ajaxAPIErrorDetails = errorResponse.customAPIErrorDetails;
          switch (errorResponse.response.status) {
            case 401:
              state.ajaxErrorMessage = "Auth session expired, requesting new...";
              bus.emit("reAuthRequired", { emitter: "DocumentDetailsAttachments.onRemoveAttachmentAtIndex" });
              break;
            default:
              state.ajaxErrorMessage = "API Error: fatal error";
              break;
          }
        } else {
          state.ajaxErrorMessage = `Uncaught exception: ${errorResponse}`;
          console.error(errorResponse);
        }
      }).finally(() => {
        state.ajaxRunning = false;
      });
  } else {
    document.attachments = document.attachments.filter((_, i) => i !== index);
  }
};

const onAddNote = () => {
  //document.addNote(t);
  document.notes.unshift(
    new NoteClass(
      uid(),
      "",
      new DateTimeClass(t, currentTimestamp()),
      false,
      true, // new notes start with view mode = "edit" (for allowing input body text)
    )
  );
};

const onRemoveNoteAtIndex = (index: number) => {
  document.notes = document.notes.filter((_, i) => i !== index);
};

// q-uploader component event => file upload starts
const onUploadsStart = () => {
  isUploading.value = true;
  bus.emit("showUploadingDialog", {
    transfers: uploaderRef.value?.files.map((file) =>
      new UploadTransferClass(
        uid(),
        file.name,
        file.size,
        currentTimestamp(),
        null,
        false,
        false,
        false,
        null,
        null,
        false
      )
    )
  });
};

// q-uploader component event => file was uploaded
const onFileUploaded = (info: { files: readonly any[]; xhr: any; }): void => {
  let jsonResponse = null;
  try {
    jsonResponse = JSON.parse(info.xhr.response);
  } catch (e) { console.error(e); }
  if (jsonResponse != null) {
    document.attachments.unshift(
      new AttachmentClass(
        (jsonResponse.data).id,
        info.files[0]!.name,
        "",
        info.files[0]!.size,
        new DateTimeClass(t, currentTimestamp()),
        true // this property is used for checking if file was uploaded but not associated to document (while not saving document)
      )
    );
    bus.emit("refreshUploadingDialog.fileUploaded", {
      transfers: info.files.map((file) =>
        new UploadTransferClass(
          uid(),
          file.name,
          file.size,
          0,
          null,
          false,
          false,
          false,
          null,
          null,
          false
        )
      )
    });
  } else {
    bus.emit("refreshUploadingDialog.fileUploadRejected", {
      transfers: info.files.map((file) =>
        new UploadTransferClass(
          uid(),
          file.name,
          file.size,
          currentTimestamp(),
          currentTimestamp(),
          false,
          false,
          true,
          500,
          "Transfer rejected",
          true
        )
      )
    });
  }
}

// q-uploader component event => file upload is rejected
const onUploadRejected = (rejectedEntries: QRejectedEntry[]): void => {
  console.log("rejected");
  const transfers =
    rejectedEntries.map((error) =>
      new UploadTransferClass(
        uid(),
        error.file.name,
        error.file.size,
        0,
        null,
        false,
        false,
        true,
        error.failedPropValidation == "max-file-size" ? 413 : 500,
        error.failedPropValidation == "max-file-size" ? "Content Too Large" : "Internal Server Error",
        false
      )
    );
  bus.emit("refreshUploadingDialog.fileUploadRejected", { transfers: transfers });
}

// q-uploader component event => file upload failed
const onUploadFailed = (info: { files: readonly any[]; xhr: any; }) => {
  bus.emit("refreshUploadingDialog.fileUploadFailed", {
    transfers: info.files.map((file) =>
      new UploadTransferClass(
        uid(),
        file.name,
        file.size,
        currentTimestamp(),
        currentTimestamp(),
        false,
        false,
        true,
        info.xhr.status,
        "Transfer failed",
        true
      )
    )
  });
}

// q-uploader component event => file upload finish (all files)
const onUploadsFinish = (): void => {
  isUploading.value = false;
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
