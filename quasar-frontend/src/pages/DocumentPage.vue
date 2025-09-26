<template>
  <q-page>
    <div @dragover.prevent @dragenter.prevent @dragleave="handleDragLeave" @drop="handleDrop">
      <q-card flat class="bg-transparent">
        <q-card-section>
          <div class="row items-center">
            <h3 class="q-mt-sm q-mb-sm" v-if="!document.id">{{ t("New document") }}</h3>
            <h3 class="q-mt-sm q-mb-sm" v-else>{{ t("Document card") }}</h3>
            <q-space />
            <q-btn-group flat square>
              <q-btn icon="autorenew" flat square size="xl" :title="t('Reload document')" v-if="!isNewDocument"
                @click="onRefresh" />
              <q-btn icon="delete" flat square size="xl" :title="t('Remove document')" v-if="!isNewDocument"
                @click="showConfirmDeleteDocumentDialog = true" />
              <q-btn icon="save" flat round size="xl" :title="t('Save document')" @click="onSubmitForm"
                :disable="loading || saving || uploading || !document.title" />
            </q-btn-group>
          </div>
          <q-separator />
        </q-card-section>
        <form @submit.prevent.stop="onSubmitForm" autocorrect="off" autocapitalize="off" autocomplete="off"
          spellcheck="false">
          <q-card-section class="row q-pa-none">
            <div class="col-12 col-lg-6 col-xl-6 q-px-sm">
              <q-card class="q-ma-xs q-mt-sm">
                <q-card-section class="q-pa-none q-mb-sm">
                  <q-tabs v-model="leftTab">
                    <q-tab name="metadata" icon="description" :label="t('Document metadata')"
                      class="cursor-default full-width"></q-tab>
                  </q-tabs>
                </q-card-section>
                <q-card-section class="q-pa-md">
                  <div class="row q-col-gutter-x-sm">
                    <div
                      :class="{ 'col-6': document.createdOnTimestamp != document.lastUpdateTimestamp, 'col-12': document.createdOnTimestamp == document.lastUpdateTimestamp }">
                      <q-input dense class="q-mb-md" outlined v-model="document.creationDate" :label="t('Created on')"
                        readonly v-if="document.id">
                        <template v-slot:append v-if="screengtxs">
                          <span style="font-size: 14px;">
                            {{ timeAgo(document.createdOnTimestamp) }}
                          </span>
                        </template>
                      </q-input>
                    </div>
                    <div class="col-6" v-if="document.createdOnTimestamp != document.lastUpdateTimestamp">
                      <q-input dense class="q-mb-md" outlined v-model="document.lastUpdate" :label="t('Last update')"
                        readonly v-if="document.id">
                        <template v-slot:append v-if="screengtxs">
                          <span style="font-size: 14px;">
                            {{ timeAgo(document.lastUpdateTimestamp) }}
                          </span>
                        </template>
                      </q-input>
                    </div>
                  </div>
                  <InteractiveTextFieldCustomInput ref="titleRef" dense class="q-mb-md" maxlength="128" outlined
                    v-model.trim="document.title" type="textarea" autogrow name="title" :label="t('Document title')"
                    :disable="loading || saving" :autofocus="true" clearable :start-mode-editable="isNewDocument"
                    :rules="requiredFieldRules" :error="!document.title" :error-message="fieldIsRequiredLabel"
                    :max-lines="1">
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
            <div class="col-12 col-lg-6 col-xl-6 scroll_ q-px-sm" style2="min-height: 64vh; max-height: 64vh;">
              <q-card class="q-ma-xs q-mt-sm">
                <q-card-section class="q-pa-none q-mb-sm" style="border-bottom: 2px solid rgba(0, 0, 0, 0.12);">
                  <q-tabs v-model="tab" align="left">
                    <q-tab name="attachments" icon="attach_file" :disable="state.loading" :label="t('Attachments')">
                      <q-badge floating v-show="document.files.length > 0">{{ document.files.length }}</q-badge>
                    </q-tab>
                    <q-tab name="notes" icon="forum" :disable="state.loading" :label="t('Notes')">
                      <q-badge floating v-show="document.notes.length > 0">{{ document.notes.length }}</q-badge>
                    </q-tab>
                    <q-tab name="history" icon="view_timeline" :disable="state.loading" :label="t('History')"
                      v-if="document.id">
                      <q-badge floating v-show="document.history.length > 0">{{ document.history.length }}</q-badge>
                    </q-tab>
                    <q-tab name="attachments" v-if="tab == 'attachments'" icon="add" :disable="state.loading"
                      :label="t('Add attachment')" class="bg-blue text-white"
                      @click.stop="onShowAttachmentsPicker"></q-tab>
                    <q-tab name="notes" v-if="tab == 'notes'" icon="add" :disable="state.loading" :label="t('Add note')"
                      class="bg-blue text-white" @click.stop="onShowAddNoteDialog"></q-tab>
                  </q-tabs>
                </q-card-section>
                <q-card-section class="q-pa-none">
                  <q-tab-panels v-model="tab" animated class="bg-transparent">
                    <q-tab-panel name="attachments" class="q-pa-none">
                      <q-uploader ref="uploaderRef" class="q-mb-md hidden"
                        :label="t('Add new file (Drag & Drop supported)')" flat bordered auto-upload hide-upload-btn
                        color="dark" field-name="file" url="api2/file" :max-file-size="maxFileSize" multiple
                        @uploaded="onFileUploaded" @rejected="onUploadRejected" @failed="onUploadFailed" method="post"
                        style="width: 100%;" :disable="loading || saving" no-thumbnails @start="onUploadsStart"
                        @finish="onUploadsFinish" />
                      <q-list class="scroll" style="min-height: 50vh; max-height: 50vh;">
                        <div v-for="file, index in document.files" :key="file.id">
                          <q-item class="transparent-background text-color-primary q-pa-sm" v-show="file.visible"
                            clickable="">
                            <q-item-section class="q-mx-md">
                              <q-item-label>
                                Filename: {{ file.name }}
                              </q-item-label>
                              <q-item-label caption>
                                Length: {{ format.humanStorageSize(file.size) }}
                              </q-item-label>
                              <q-item-label caption>
                                Uploaded on: {{ file.createdOn }}
                              </q-item-label>
                            </q-item-section>
                            <q-item-section side top>
                              <q-chip size="md" square class="theme-default-q-chip shadow-1 full-width"
                                :class="{ 'cursor-not-allowed': state.loading }" :clickable="!state.loading"
                                @click.stop.prevent="onRemoveFile(index)">
                                <q-avatar class="theme-default-q-avatar text-white bg-blue-6" icon="delete" />
                                {{ t("Remove") }}
                              </q-chip>
                              <q-chip size="md" square class="theme-default-q-chip shadow-1 full-width"
                                :class="{ 'cursor-not-allowed': state.loading || !allowPreview(file.name) }"
                                :clickable="!state.loading && allowPreview(file.name)"
                                @click.stop.prevent="onPreviewFile(index)">
                                <q-avatar class="theme-default-q-avatar text-white bg-blue-6" icon="preview" />
                                {{ t("Preview") }}
                              </q-chip>
                            </q-item-section>
                          </q-item>
                          <q-separator v-if="index !== document.files.length - 1" class="q-my-xs" />
                        </div>

                      </q-list>
                      <!--
                      <q-markup-table>
                        <thead>
                          <tr>
                            <th><q-btn size="sm" :label="t('Add attachment')" icon="add"
                                class="bg-blue text-white full-width" :disable="state.loading"
                                @click.stop="onShowAttachmentsPicker"></q-btn>
                            </th>
                            <th colspan="3"><q-input type="search" icon="search" outlined dense clearable
                                :disable="state.loading || !hasAttachments" v-model.trim="filterAttachmentByText"
                                :label="t('Filter by text on file name')" :placeholder="t('text condition')"></q-input>
                            </th>
                          </tr>
                          <tr>
                            <th class="text-left">{{ t('Uploaded on') }}</th>
                            <th class="text-left">{{ t('Name') }}</th>
                            <th class="text-right">{{ t('Size') }}</th>
                            <th class="text-center">{{ t('Actions') }}</th>
                          </tr>
                        </thead>
                        <tbody v-if="hasAttachments">
                          <tr v-for="file, fileIndex in document.files" :key="file.id" v-show="file.visible">
                            <td class="text-left">{{ file.createdOn }}</td>
                            <td class="text-left">{{ file.name }}</td>
                            <td class="text-right">{{ file.humanSize }}</td>
                            <td class="text-center">
                              <q-btn-group flat spread class="desktop-only" :disable="loading">
                                <q-btn size="md" no-caps :label="t('Preview')" icon="preview"
                                  @click.prevent="onPreviewFile(fileIndex)"
                                  :disable="loading || !allowPreview(file.name) || file.isNew" />
                                <q-btn size="md" no-caps :label="t('Download')" icon="download" :href="file.url"
                                  :disable="loading || file.isNew" />
                                <q-btn size="md" no-caps :label="t('Remove')" icon="delete" :disable="loading"
                                  @click.prevent="onShowFileRemoveConfirmationDialog(file, fileIndex)" />
                              </q-btn-group>
                              <q-btn-dropdown :label="t('Operations')" class="mobile-only" :disable="loading">
                                <q-list>
                                  <q-item clickable v-close-popup @click.prevent="onPreviewFile(fileIndex)"
                                    :disable="loading || !allowPreview(file.name) || file.isNew">
                                    <q-item-section avatar>
                                      <q-icon name="preview"></q-icon>
                                    </q-item-section>
                                    <q-item-section>
                                      <q-item-label>{{ t("Preview") }}</q-item-label>
                                    </q-item-section>
                                  </q-item>
                                  <q-item clickable v-close-popup :href="file.url" :disable="loading || file.isNew">
                                    <q-item-section avatar>
                                      <q-icon name="download"></q-icon>
                                    </q-item-section>
                                    <q-item-section>
                                      <q-item-label>{{ t("Download") }}</q-item-label>
                                    </q-item-section>
                                  </q-item>
                                  <q-item clickable v-close-popup
                                    @click.prevent="onShowFileRemoveConfirmationDialog(file, fileIndex)"
                                    :disable="loading">
                                    <q-item-section avatar>
                                      <q-icon name="delete"></q-icon>
                                    </q-item-section>
                                    <q-item-section>
                                      <q-item-label>{{ t("Remove") }}</q-item-label>
                                    </q-item-section>
                                  </q-item>
                                </q-list>
                              </q-btn-dropdown>
                            </td>
                          </tr>
                        </tbody>
                        <tfoot v-else>
                          <tr>
                            <th colspan="4">
                              <CustomBanner warning text="No attachments found"></CustomBanner>
                            </th>
                          </tr>
                        </tfoot>
                      </q-markup-table>
                      -->
                    </q-tab-panel>
                    <q-tab-panel name="notes" class="q-pa-none scroll" style="min-height: 64vh; max-height: 64vh;">
                      <q-list class="bg-transparent">
                        <q-item v-for="note, noteIndex in document.notes" :key="note.id"
                          class="q-pa-none bg-transparent">
                          <q-item-section>
                            <InteractiveTextFieldCustomInput v-model.trim="note.body" dense outlined type="textarea"
                              maxlength="4096" autogrow name="description"
                              :label="`${note.createdOn} (${timeAgo(note.createdOnTimestamp)})`"
                              :start-mode-editable="!!note.startOnEditMode" :disable="loading || saving" clearable
                              :max-lines="6" :rules="requiredFieldRules" :error="!note.body"
                              :error-message="fieldIsRequiredLabel" :autofocus="note.startOnEditMode">
                              <template v-slot:top-icon-append="{ showTopHoverIcons }">
                                <q-icon name="delete" size="sm" class="q-ml-sm q-mr-sm" clickable
                                  v-show="showTopHoverIcons"
                                  @click.prevent="onShowNoteRemoveConfirmationDialog(note, noteIndex)">
                                  <q-tooltip>{{ t("Click to remove note") }}</q-tooltip>
                                </q-icon>
                              </template>
                              <template v-slot:icon-append-on-edit>
                                <q-icon name="delete" size="sm" class="cursor-pointer" clickable
                                  @click.prevent="onShowNoteRemoveConfirmationDialog(note, noteIndex)">
                                </q-icon>
                              </template>
                              <!-- TODO: NOT FOCUSING ON TEXTAREA CHANGE TO EDIT MODE -->
                            </InteractiveTextFieldCustomInput>
                          </q-item-section>
                        </q-item>
                      </q-list>
                    </q-tab-panel>
                    <q-tab-panel name="history" class="q-pa-none scroll" v-if="document.id">
                      <q-markup-table>
                        <thead>
                          <tr>
                            <th class="text-left">{{ t("Date") }}</th>
                            <th class="text-left">{{ t("Operation") }}</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr v-for="operation in document.history" :key="operation.operationTimestamp">
                            <td>{{ operation.date }} ({{ timeAgo(operation.operationTimestamp) }})</td>
                            <td><q-icon size="md" :name="operation.icon" class="q-mr-sm"></q-icon>{{
                              t(operation.label) }}
                            </td>
                          </tr>
                        </tbody>
                      </q-markup-table>
                    </q-tab-panel>
                  </q-tab-panels>
                </q-card-section>
              </q-card>
            </div>
          </q-card-section>
          <q-card-section>
            <q-btn :label="t('Save changes')" type="submit" icon="save" class="full-width" color="dark"
              :disable="loading || saving || uploading || !document.title">
              <template v-slot:loading v-if="saving">
                <q-spinner-hourglass class="on-left" />
                {{ t("Saving...") }}
              </template>
            </q-btn>
            <CustomBanner v-if="state.saveSuccess" class="q-mt-md" text="Document saved" success></CustomBanner>
            <CustomErrorBanner v-else-if="state.loadingError || state.saveError" class="q-mt-md"
              :text="state.errorMessage">
            </CustomErrorBanner>
          </q-card-section>
        </form>
      </q-card>
    </div>
    <DocumentFilesPreviewDialog v-if="showPreviewFileDialog" :files="document.files" :index="selectedFileIndex"
      @close="showPreviewFileDialog = false">
    </DocumentFilesPreviewDialog>
    <NoteModal v-if="showNoteDialog" @close="showNoteDialog = false" @cancel="showNoteDialog = false" @add="onAddNote"
      @update="onUpdateNote" :note="currentNote">
    </NoteModal>
    <ConfirmationDialog
      v-if="showConfirmDeleteFileDialog || showConfirmDeleteDocumentDialog || showConfirmDeleteNoteDialog"
      @close="onCancelConfirmationModal" @cancel="onCancelConfirmationModal" @ok="onSuccessConfirmationModal">
      <template v-slot:header v-if="showConfirmDeleteFileDialog">
        <div class="text-h6">{{ t("Remove document file") }}</div>
        <div class="text-subtitle2">{{ document.files[selectedFileIndex].name }}</div>
      </template>
      <template v-slot:header v-else-if="showConfirmDeleteDocumentDialog">
        <div class="text-h6">{{ t("Delete document") }}</div>
        <div class="text-subtitle2">{{ t("Document title") + ": " + document.title }}</div>
      </template>
      <template v-slot:header v-else-if="showConfirmDeleteNoteDialog">
        <div class="text-h6">{{ t("Delete note") }}</div>
      </template>
      <template v-slot:body v-if="showConfirmDeleteFileDialog">
        <strong>{{ t("Are you sure ? (You must save the document after deleting this file)") }}</strong>
      </template>
      <template v-slot:body v-else-if="showConfirmDeleteNoteDialog">
        <q-card>
          <q-card-section class="white-space-pre-line">
            {{ document.notes[selectedNoteIndex].body }}
          </q-card-section>
        </q-card>
        <p class="q-mt-md">
          <strong>{{ t("Are you sure ? (You must save the document after deleting this note)") }}</strong>
        </p>
      </template>
      <template v-slot:body v-else-if="showConfirmDeleteDocumentDialog">
        <strong>{{ t("This operation cannot be undone. Would you like to proceed ?") }}</strong>
      </template>
    </ConfirmationDialog>
  </q-page>
</template>

<script setup>

import { ref, reactive, nextTick, computed, watch, onMounted, onBeforeUnmount } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useI18n } from "vue-i18n";
import { uid, format, date, useQuasar } from "quasar";

import { bus } from "src/boot/bus";
import { api } from "src/boot/axios";
import { useFormatDates } from "src/composables/formatDate"
import { useFormUtils } from "src/composables/formUtils"
import { useFileUtils } from "src/composables/fileUtils"
import { useInitialStateStore } from "src/stores/initialState";

import { default as InteractiveTagsFieldCustomSelect } from "src/components/Forms/Fields/InteractiveTagsFieldCustomSelect.vue"
import { default as ConfirmationDialog } from "src/components/Dialogs/ConfirmationDialog.vue";
import { default as DocumentFilesPreviewDialog } from "src/components/Dialogs/DocumentFilesPreviewDialog.vue";
import { default as NoteModal } from "src/components/NoteModal.vue";
import { default as InteractiveTextFieldCustomInput } from "src/components/Forms/Fields/InteractiveTextFieldCustomInput.vue"
import { default as CustomBanner } from "src/components/Banners/CustomBanner.vue"
import { default as CustomErrorBanner } from "src/components/Banners/CustomErrorBanner.vue"

const tab = ref("attachments");

const $q = useQuasar();
const { t } = useI18n();

const { requiredFieldRules, fieldIsRequiredLabel } = useFormUtils();

const route = useRoute();
const router = useRouter();
const initialState = useInitialStateStore();

const { timeAgo } = useFormatDates();

const { allowPreview } = useFileUtils();

const maxFileSize = computed(() => initialState.maxUploadFileSize);
const uploaderRef = ref(null);
const selectedFileIndex = ref(null);
const selectedNoteIndex = ref(null);
const showPreviewFileDialog = ref(false);
const showConfirmDeleteFileDialog = ref(false);
const showConfirmDeleteDocumentDialog = ref(false);
const showConfirmDeleteNoteDialog = ref(false);
const showNoteDialog = ref(false);
const titleRef = ref(null);
const loading = ref(false);
const saving = ref(false);
const uploading = ref(false);
const currentNote = ref({ id: null, body: null });

const state = reactive({
  loading: false,
  loadingError: false,
  errorMessage: null,
  apiError: null,
  saveSuccess: false,
  saveError: false,
});

const isNewDocument = computed(() => router.currentRoute.value.name == 'newDocument');

const readOnlyTitle = ref(!isNewDocument.value);
const readOnlyDescription = ref(!isNewDocument.value);

const leftTab = ref("metadata");

const screengtxs = computed(() => $q.screen.gt.xs);

const document = reactive({
  id: null,
  title: null,
  description: null,
  created: null,
  createdOnTimestamp: null,
  createdBy: null,
  lastUpdate: null,
  files: [],
  tags: [],
  notes: [],
  history: [],
  reset() {
    this.id = null;
    this.title = null;
    this.description = null;
    this.created = null;
    this.createdOnTimestamp = null;
    this.createdBy = null;
    this.lastUpdate = null;
    this.files = [];
    this.tags = [];
    this.notes = [];
    this.history = [];
  },
  set(doc) {
    this.id = doc.id;
    this.title = doc.title;
    this.description = doc.description;
    this.created = doc.created;
    this.createdOnTimestamp = doc.createdOnTimestamp;
    this.createdBy = doc.createdBy;
    this.lastUpdate = doc.lastUpdate;
    this.files = JSON.parse(JSON.stringify(doc.files))
    this.tags = JSON.parse(JSON.stringify(doc.tags))
    this.notes = JSON.parse(JSON.stringify(doc.notes))
    this.history = JSON.parse(JSON.stringify(doc.history))
  }
});

//const hasAttachments = computed(() => document?.value.files?.length > 0);

router.beforeEach(async (to, from) => {
  if (to.name == "newDocument") {
    // new document, reset form fields
    document.reset();
  } else if (from.name == "newDocument" && to.name == "document" && to.params.id) {
    // existent document from creation
    document.reset();
    document.id = to.params.id
    onRefresh();
  } else if (from.name != "newDocument" && to.name == "document" && to.params.id) {
    // existent document
    document.reset();
    document.id = to.params.id
    onRefresh();
  }
});

const parseDocumentJSONResponse = (documentData) => {
  document.set(documentData);
  document.creationDate = date.formatDate(document.createdOnTimestamp, 'YYYY/MM/DD HH:mm:ss');
  document.lastUpdate = date.formatDate(document.lastUpdateTimestamp, 'YYYY/MM/DD HH:mm:ss');
  document.files.map((file) => {
    file.isNew = false;
    file.createdOn = date.formatDate(file.createdOnTimestamp, 'YYYY-MM-DD HH:mm:ss');
    file.humanSize = format.humanStorageSize(file.size);
    file.url = "api2/file/" + file.id;
    file.visible = true;
    return (file);
  });
  document.notes.map((note) => {
    note.isNew = false;
    note.createdOn = date.formatDate(note.createdOnTimestamp, 'YYYY-MM-DD HH:mm:ss');
    note.expanded = false;
    return (note);
  });
  document.history.map((operation) => {
    operation.date = date.formatDate(operation.operationTimestamp, 'YYYY-MM-DD HH:mm:ss');
    switch (operation.operationType) {
      case 1:
        operation.label = "Document created";
        operation.icon = "post_add";
        break;
      case 2:
        operation.label = "Document updated";
        operation.icon = "edit_note";
        break;
      default:
        operation.label = "Unknown operation";
        operation.icon = "error";
        break;
    }
    return (operation);
  });
};

const onRefresh = () => {
  loading.value = true;
  state.loading = true;
  state.loadingError = false;
  state.errorMessage = null;
  state.apiError = null;
  state.saveSuccess = false;
  state.saveError = false;
  api.document
    .get(document.id)
    .then((successResponse) => {
      parseDocumentJSONResponse(successResponse.data.document);
      loading.value = false;
      state.loading = false;
      if (titleRef.value) {
        nextTick(() => titleRef.value?.focus());
      }
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
}


const handleDragLeave = () => {

};

const handleDrop = (event) => {
  event.preventDefault();
  // Aquí puedes obtener los archivos arrastrados
  const files = event.dataTransfer.files;
  if (files.length) {
    // Si ya estás usando el q-uploader, puedes agregar los archivos directamente
    uploaderRef.value?.addFiles(files);
  };
};

const filterAttachmentByText = ref(null);

watch(() => filterAttachmentByText.value, val => {
  onFilterAttachments(val);
});

const escapeRegExp = (string) => {
  return string.replace(/[.*+?^=!:${}()|\[\]\/\\]/g, '\\$&');
};

const onFilterAttachments = (text) => {
  if (text) {
    const regex = new RegExp(escapeRegExp(text), 'i');
    document.files.forEach((file) => { file.visible = !!file.name.match(regex); });
  } else {
    document.files.forEach((file) => { file.visible = true; });
  }
};

function onSubmitForm() {
  loading.value = true;
  state.loading = true;
  state.loadingError = false;
  state.errorMessage = null;
  state.apiError = null;
  state.saveSuccess = false;
  state.saveError = false;
  if (!isNewDocument.value) {
    api.document
      .update(document)
      .then((successResponse) => {
        // TODO: refactor api response to document
        if (successResponse.data.document) {
          readOnlyTitle.value = true;
          readOnlyDescription.value = true;
          parseDocumentJSONResponse(successResponse.data.document);
          loading.value = false;
          state.loading = false;
          // TODO: translate "Document saved" label
          state.saveSuccess = true;
          nextTick(() => {
            titleRef.value?.focus();
          });
        } else {
          // TODO
          loading.value = false;
        }
      })
      .catch((errorResponse) => {
        loading.value = false;
        state.apiError = errorResponse.customAPIErrorDetails;
        state.saveError = true;
        switch (errorResponse.response.status) {
          case 400:
            if (
              errorResponse.response.data.invalidOrMissingParams.find(function (e) {
                return e === "title";
              })
            ) {
              state.errorMessage = t("API Error: missing document title param");
              nextTick(() => titleRef.value?.focus());
            } else if (
              errorResponse.response.data.invalidOrMissingParams.find(function (e) {
                return e === "note_body";
              })
            ) {
              state.errorMessage = t("API Error: missing document note body");
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
        switch (errorResponse.response.status) {
          case 400:
            if (
              errorResponse.response.data.invalidOrMissingParams.find(function (e) {
                return e === "title";
              })
            ) {
              state.errorMessage = t("API Error: missing document title param");
              nextTick(() => titleRef.value?.focus());
            } else if (
              errorResponse.response.data.invalidOrMissingParams.find(function (e) {
                return e === "note_body";
              })
            ) {
              state.errorMessage = t("API Error: missing document note body");
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

function onPreviewFile(index) {
  bus.emit("showDocumentFilePreviewDialog", { document: { id: document.id, title: document.title, attachments: document.files }, currentIndex: index });
};


function onCancelConfirmationModal() {
  showConfirmDeleteFileDialog.value = false;
  showConfirmDeleteDocumentDialog.value = false;
  showConfirmDeleteNoteDialog.value = false;
}

function onSuccessConfirmationModal() {
  if (showConfirmDeleteFileDialog.value) {
    onRemoveSelectedFile();
  } else if (showConfirmDeleteNoteDialog.value) {
    onRemoveSelectedNote();
  } else if (showConfirmDeleteDocumentDialog.value) {
    onDeleteDocument();
  }
}

function onShowAttachmentsPicker() {
  tab.value = 'attachments';
  nextTick(() => {
    uploaderRef.value.pickFiles();
  });
}

const onRemoveFile = (index) => {
  document.files.splice(index, 1);
};

function onRemoveSelectedFile() {
  if (selectedFileIndex.value > -1) {
    if (document.files[selectedFileIndex.value].isNew) {
      loading.value = true;
      api.document.
        removeFile(document.files[selectedFileIndex.value].id)
        .then((response) => {
          document.files.splice(selectedFileIndex.value, 1);
          selectedFileIndex.value = null;
          showConfirmDeleteFileDialog.value = false;
          loading.value = false;
        })
        .catch((error) => {
          loading.value = false;
          $q.notify({
            type: "negative",
            message: t("API Error: error removing file"),
            caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
          });
        });
    } else {
      document.files.splice(selectedFileIndex.value, 1);
      selectedFileIndex.value = null;
      showConfirmDeleteFileDialog.value = false;
    }
  }
}

function onFileUploaded(e) {
  document.files.push(
    {
      id: (JSON.parse(e.xhr.response).data).id,
      createdOnTimestamp: date.formatDate(new Date(), 'X'),
      createdOn: date.formatDate(new Date(), 'YYYY-MM-DD HH:mm:ss'),
      name: e.files[0].name,
      size: e.files[0].size,
      hash: null,
      humanSize: format.humanStorageSize(e.files[0].size),
      isNew: true
    });
}

function onUploadRejected(e) {
  if (e[0].failedPropValidation == "max-file-size") {
    $q.notify({
      type: "negative",
      message: "Can not upload file " + e[0].file.name + ' (max upload filesize: ' + format.humanStorageSize(maxFileSize.value) + ', current file size: ' + format.humanStorageSize(e[0].file.size) + ')',
    });
  } else {
    $q.notify({
      type: "negative",
      message: t("Can not upload file", { filename: e[0].file.name })
    });
  }
}

function onUploadFailed(e) {
  $q.notify({
    type: "negative",
    message: "Can not upload file " + e.files[0].name + ', API error: ' + e.xhr.status + ' - ' + e.xhr.statusText,
  });
}

function onUploadsStart(e) {
  uploading.value = true;
}

function onUploadsFinish(e) {
  uploading.value = false;
}

function onShowNoteRemoveConfirmationDialog(note, noteIndex) {
  selectedNoteIndex.value = noteIndex;
  //showConfirmDeleteNoteDialog.value = true;
  onRemoveSelectedNote();
}

function onShowAddNoteDialog() {
  document.notes.unshift({
    id: uid(),
    body: null,
    createdOnTimestamp: date.formatDate(new Date(), 'X'),
    createdOn: date.formatDate(new Date(), 'YYYY-MM-DD HH:mm:ss'),
    startOnEditMode: true
  });
}

function onAddNote(newNote) {
  document.notes.unshift(newNote);
}

function onUpdateNote(updatedNote) {
  const idx = document.notes.findIndex((note) => note.id == updatedNote.id)
  if (idx >= 0) {
    document.notes[idx].body = updatedNote.body;
  }
}

function onRemoveSelectedNote() {
  if (selectedNoteIndex.value > -1) {
    document.notes.splice(selectedNoteIndex.value, 1);
    selectedNoteIndex.value = null;
    showConfirmDeleteNoteDialog.value = false;
  }
}

function onDeleteDocument() {
  loading.value = true;
  api.document.
    remove(document.id)
    .then((response) => {
      loading.value = false;
      $q.notify({
        type: "positive",
        message: t("Document has been removed"),
      });
      router.push({
        name: "index",
      });
    })
    .catch((error) => {
      showConfirmDeleteDocumentDialog.value = false;
      loading.value = false;
      switch (error.response.status) {
        case 401:
          this.$router.push({
            name: "signIn",
          });
          break;
        case 403:
          $q.notify({
            type: "negative",
            message: t("Access denied"),
          });
          break;
        case 404:
          $q.notify({
            type: "negative",
            message: t("Document not found"),
          });
          break;
        default:
          $q.notify({
            type: "negative",
            message: t("API Error: error deleting document"),
            caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
          });
          break;
      }
    });
}

onMounted(() => {
  if (!isNewDocument.value) {
    if (route.params.id) {
      document.id = route.params.id;
      onRefresh();
    } else {
      // TODO: ERROR
    }
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