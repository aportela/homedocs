<template>
  <q-page class="_bg-grey-2">
    <div class="q-pa-md">
      <div class="row items-center q-pb-md">
        <h3 class="q-mt-sm q-mb-sm" v-if="!document.id">{{ t("New document") }}</h3>
        <h3 class="q-mt-sm q-mb-sm" v-else>{{ t("Document") }}</h3>
        <q-space />
        <q-btn icon="save" flat round :title="t('Save document')" @click="onSubmitForm"
          :disable="loading || saving || uploading || !document.title" />
        <q-btn icon="delete" flat round :title="t('Remove document')" v-if="!isNew"
          @click="showConfirmDeleteDocumentDialog = true" />
      </div>
      <div class="q-gutter-y-md">
        <q-card>
          <form @submit.prevent.stop="onSubmitForm" autocorrect="off" autocapitalize="off" autocomplete="off"
            spellcheck="false">
            <q-card-section>
              <q-input class="q-mb-md" outlined mask="date" v-model="document.date" :label="t('Document date')"
                :disable="true" v-if="document.id"></q-input>
              <q-input class="q-mb-md" ref="titleRef" maxlength="128" outlined v-model="document.title" type="text"
                name="title" :label="t('Document title')" :disable="loading || saving" :autofocus="true">
              </q-input>
              <q-input class="q-mb-md" outlined v-model="document.description" type="textarea" maxlength="4096" autogrow
                name="description" :label="t('Document description')" :disable="loading || saving" clearble>
              </q-input>
              <TagSelector v-model="document.tags" :disabled="loading || saving">
              </TagSelector>
              <q-uploader ref="uploaderRef" class="q-mb-md" :label="t('Add new file (Drag & Drop supported)')" flat
                bordered auto-upload hide-upload-btn color="dark" field-name="file" url="api2/file"
                :max-file-size="maxFileSize" multiple @uploaded="onFileUploaded" @rejected="onUploadRejected"
                @failed="onUploadFailed" method="post" style="width: 100%;" :disable="loading || saving" no-thumbnails
                @start="onUploadsStart" @finish="onUploadsFinish" />
              <q-markup-table v-if="document.files.length > 0">
                <thead>
                  <tr>
                    <th class="text-left">{{ t('Created on') }}</th>
                    <th class="text-left">{{ t('Name') }}</th>
                    <th class="text-right">{{ t('Size') }}</th>
                    <th class="text-center">{{ t('Actions') }}</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="file, fileIndex in document.files" :key="file.id">
                    <td class="text-left">{{ file.uploadedOn }}</td>
                    <td class="text-left">{{ file.name }}</td>
                    <td class="text-right">{{ file.humanSize }}</td>
                    <td class="text-center">
                      <q-btn-group spread class="desktop-only" :disable="loading">
                        <q-btn :label="t('Open/Preview')" icon="preview" @click.prevent="onPreviewFile(fileIndex)"
                          :disable="loading || !allowPreview(file.name) || file.isNew" />
                        <q-btn :label="t('Download')" icon="download" :href="file.url"
                          :disable="loading || file.isNew" />
                        <q-btn :label="t('Remove')" icon="delete" :disable="loading"
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
                              <q-item-label>{{ t("Open/Preview") }}</q-item-label>
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
                            @click.prevent="onShowFileRemoveConfirmationDialog(file, fileIndex)" :disable="loading">
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
              </q-markup-table>
            </q-card-section>
            <q-card-section>
              <q-btn :label="t('Save changes')" type="submit" icon="save" class="full-width" color="dark"
                :disable="loading || saving || uploading || !document.title">
                <template v-slot:loading v-if="saving">
                  <q-spinner-hourglass class="on-left" />
                  {{ t("Saving...") }}
                </template>
              </q-btn>
            </q-card-section>
          </form>
        </q-card>
      </div>
    </div>
    <FilePreviewModal v-if="showPreviewFileDialog" :files="document.files" :index="selectedFileIndex"
      @close="showPreviewFileDialog = false">
    </FilePreviewModal>
    <ConfirmationModal v-if="showConfirmDeleteFileDialog || showConfirmDeleteDocumentDialog"
      @close="onCancelConfirmationModal" @cancel="onCancelConfirmationModal" @ok="onSuccessConfirmationModal">
      <template v-slot:header v-if="showConfirmDeleteFileDialog">
        <div class="text-h6">{{ t("Remove document file") }}</div>
        <div class="text-subtitle2">{{ document.files[selectedFileIndex].name }}</div>
      </template>
      <template v-slot:header v-else-if="showConfirmDeleteDocumentDialog">
        <div class="text-h6">{{ t("Delete document") }}</div>
        <div class="text-subtitle2">{{ t("Document title") + ": " + document.title }}</div>
      </template>
      <template v-slot:body v-if="showConfirmDeleteFileDialog">
        <strong>{{ t("Are you sure ? (You must save the document after deleting this file)") }}</strong>
      </template>
      <template v-slot:body v-else-if="showConfirmDeleteDocumentDialog">
        <strong>{{ t("This operation cannot be undone. Would you like to proceed ?") }}</strong>
      </template>
    </ConfirmationModal>
  </q-page>
</template>

<script setup>

import { ref, nextTick, computed } from "vue";
import { useRoute, useRouter } from "vue-router";
import { uid, format, date, useQuasar } from "quasar";
import { useI18n } from 'vue-i18n'
import { api } from 'boot/axios'
import { default as TagSelector } from "components/TagSelector.vue";
import { default as ConfirmationModal } from "components/ConfirmationModal.vue";
import { default as FilePreviewModal } from "components/FilePreviewModal.vue";
import { useInitialStateStore } from "stores/initialState";

const $q = useQuasar();
const { t } = useI18n();
const route = useRoute();
const router = useRouter();
const initialState = useInitialStateStore();
const maxFileSize = computed(() => initialState.maxUploadFileSize);
const uploaderRef = ref(null);
const selectedFileIndex = ref(null);
const showPreviewFileDialog = ref(false);
const showConfirmDeleteFileDialog = ref(false);
const showConfirmDeleteDocumentDialog = ref(false);
const titleRef = ref(null);
const isNew = ref(false);
const loading = ref(false);
const saving = ref(false);
const uploading = ref(false);

const document = ref({
  id: null,
  title: null,
  description: null,
  created: null,
  createdOnTimestamp: null,
  createdBy: null,
  files: [],
  tags: []
});

router.beforeEach(async (to, from) => {
  if (to.name == "newDocument") {
    // new document, reset form fields
    document.value = {
      id: null,
      title: null,
      description: null,
      created: null,
      createdOnTimestamp: null,
      date: null,
      createdBy: null,
      files: [],
      tags: []
    }
    isNew.value = true;
  } else if (from.name == "newDocument" && to.name == "document" && to.params.id) {
    // existent document from creation
    isNew.value = false;
    document.value.id = to.params.id
    onRefresh();
  } else if (from.name != "newDocument" && to.name == "document" && to.params.id) {
    // existent document
    isNew.value = false;
    document.value.id = to.params.id
    onRefresh();
  }
});

function onRefresh() {
  loading.value = true;
  api.document
    .get(document.value.id)
    .then((response) => {
      document.value = response.data.data;
      document.value.createdOn = date.formatDate(document.value.createdOnTimestamp * 1000, 'YYYY-MM-DD HH:mm:ss');
      document.value.date = date.formatDate(document.value.createdOnTimestamp * 1000, 'YYYY/MM/DD');
      document.value.files.map((file) => {
        file.isNew = false;
        file.uploadedOn = date.formatDate(file.uploadedOnTimestamp * 1000, 'YYYY-MM-DD HH:mm:ss');
        file.humanSize = format.humanStorageSize(file.size);
        file.url = "api2/file/" + file.id;
        return (file);
      });
      loading.value = false;
      if (titleRef.value) {
        nextTick(() => titleRef.value.focus());
      }
    })
    .catch((error) => {
      loading.value = false;
      switch (error.response.status) {
        case 401:
          this.$router.push({
            name: "signIn",
          });
          break;
        default:
          $q.notify({
            type: "negative",
            message: t("API Error: error loading document"),
            caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
          });
          break;
      }
    });
}

function onSubmitForm() {
  loading.value = true;
  if (!isNew.value) {
    api.document
      .update(document.value)
      .then((response) => {
        document.value = response.data.data;
        document.value.createdOn = date.formatDate(document.value.createdOnTimestamp * 1000, 'YYYY-MM-DD HH:mm:ss');
        document.value.date = date.formatDate(document.value.createdOnTimestamp * 1000, 'YYYY/MM/DD');
        document.value.files.map((file) => {
          file.isNew = false;
          file.url = "api2/file/" + file.id;
          file.uploadedOn = date.formatDate(file.uploadedOnTimestamp * 1000, 'YYYY-MM-DD HH:mm:ss');
          file.humanSize = format.humanStorageSize(file.size);
          return (file);
        });
        loading.value = false;
        nextTick(() => {
          uploaderRef.value.reset();
          titleRef.value.focus();
        });
      })
      .catch((error) => {
        loading.value = false;
        switch (error.response.status) {
          case 400:
            if (
              error.response.data.invalidOrMissingParams.find(function (e) {
                return e === "title";
              })
            ) {
              $q.notify({
                type: "negative",
                message: t("API Error: missing document title param"),
              });
              nextTick(() => titleRef.value.focus());
            }
            break;
          case 401:
            this.$router.push({
              name: "signIn",
            });
            break;
          default:
            $q.notify({
              type: "negative",
              message: t("API Error: error updating document"),
              caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
            });
            break;
        }
      });
  } else {
    if (!document.value.id) {
      document.value.id = uid();
    }
    api.document
      .add(document.value)
      .then((response) => {
        loading.value = false;
        nextTick(() => {
          uploaderRef.value.reset();
          titleRef.value.focus();
        });
        router.push({
          name: "document",
          params: { id: document.value.id }
        });
      })
      .catch((error) => {
        document.value.id = null;
        loading.value = false;
        switch (error.response.status) {
          case 400:
            if (
              error.response.data.invalidOrMissingParams.find(function (e) {
                return e === "title";
              })
            ) {
              $q.notify({
                type: "negative",
                message: t("API Error: missing document title param"),
              });
              nextTick(() => titleRef.value.focus());
            }
            break;
          case 401:
            this.$router.push({
              name: "signIn",
            });
            break;
          default:
            $q.notify({
              type: "negative",
              message: t("API Error: error adding document"),
              caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
            });
            break;
        }
      });
  }
}

function allowPreview(filename) {
  return (filename.match(/.(jpg|jpeg|png|gif|mp3)$/i));
}

function onPreviewFile(index) {
  selectedFileIndex.value = index;
  showPreviewFileDialog.value = true;
}

function onShowFileRemoveConfirmationDialog(file, fileIndex) {
  selectedFileIndex.value = fileIndex;
  showConfirmDeleteFileDialog.value = true;
}

function onCancelConfirmationModal() {
  showConfirmDeleteFileDialog.value = false;
  showConfirmDeleteDocumentDialog.value = false;
}

function onSuccessConfirmationModal() {
  if (showConfirmDeleteFileDialog.value) {
    onRemoveSelectedFile();
  } else if (showConfirmDeleteDocumentDialog.value) {
    onDeleteDocument();
  }
}

function onRemoveSelectedFile() {
  if (selectedFileIndex.value > -1) {
    if (document.value.files[selectedFileIndex.value].isNew) {
      loading.value = true;
      api.document.
        removeFile(document.value.files[selectedFileIndex.value].id)
        .then((response) => {
          document.value.files.splice(selectedFileIndex.value, 1);
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
      document.value.files.splice(selectedFileIndex.value, 1);
      selectedFileIndex.value = null;
      showConfirmDeleteFileDialog.value = false;
    }
  }
}

function onFileUploaded(e) {
  document.value.files.push(
    {
      id: (JSON.parse(e.xhr.response).data).id,
      uploadedOn: date.formatDate(new Date(), 'YYYY-MM-DD HH:mm:ss'),
      name: e.files[0].name,
      size: e.files[0].size,
      hash: null,
      humanSize: format.humanStorageSize(e.files[0].size),
      isNew: true
    }
  );
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

function onDeleteDocument() {
  loading.value = true;
  api.document.
    remove(document.value.id)
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

document.value.id = route.params.id || null;
if (document.value.id) {
  isNew.value = false;
  onRefresh();
} else {
  isNew.value = true;
}

</script>