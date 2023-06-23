<template>
  <q-page class="bg-grey-2">
    <q-card>
      <form @submit.prevent.stop="onSubmitForm" autocorrect="off" autocapitalize="off" autocomplete="off"
        spellcheck="false">
        <q-card-section>
          <h3 v-if="!document.id">{{ t('New document') }}</h3>
          <h3 v-else>{{ t('Document') }}</h3>
        </q-card-section>
        <q-card-section>
          <q-input class="q-mb-md" ref="titleRef" outlined v-model="document.title" type="text" name="title"
            :label="t('Document title')" :disable="loading || saving" :autofocus="true">
          </q-input>
          <q-input class="q-mb-md" outlined v-model="document.description" type="textarea" autogrow name="description"
            :label="t('Document description')" :disable="loading || saving" clearble>
          </q-input>
          <TagSelector v-model="document.tags" :disabled="loading || saving">
          </TagSelector>
          <q-uploader class="q-mb-md" :label="t('Add new file (Drag & Drop supported)')" flat bordered auto-upload
            hide-upload-btn color="dark" field-name="file" :url="newUploadURL" :max-file-size="maxFileSize"
            @added="onFileAdded" @uploaded="onFileUploaded" @rejected="onUploadRejected" method="post" multiple
            style="width: 100%;" :disable="loading || saving" no-thumbnails batch />
          <q-markup-table v-if="document.files.length > 0">
            <thead>
              <tr>
                <th class="text-left">Created on</th>
                <th class="text-left">Name</th>
                <th class="text-right">Size</th>
                <th class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="file, fileIndex in document.files" :key="file.id">
                <td class="text-left">{{ file.uploadedOn }}</td>
                <td class="text-left">{{ file.name }}</td>
                <td class="text-right">{{ file.humanSize }}</td>
                <td class="text-center">
                  <q-btn-group spread class="desktop-only" :disable="loading">
                    <q-btn label="Open/Preview" icon="preview" @click.prevent="onPreviewFile(file)"
                      :disable="loading || !isImage(file.name)" />
                    <q-btn label="Download" icon="download" :href="'api2/file/' + file.id" />
                    <q-btn label="Remove" icon="delete" :disable="loading"
                      @click.prevent="onShowFileRemoveConfirmationDialog(file, fileIndex)" />
                  </q-btn-group>
                  <q-btn-dropdown label="Operations" class="mobile-only" :disable="loading">
                    <q-list>
                      <q-item clickable v-close-popup @click.prevent="onPreviewFile(file)">
                        <q-item-section avatar>
                          <q-icon name="preview"></q-icon>
                        </q-item-section>
                        <q-item-section>
                          <q-item-label>Open/Preview</q-item-label>
                        </q-item-section>
                      </q-item>

                      <q-item clickable v-close-popup :href="'api2/file/' + file.id">
                        <q-item-section avatar>
                          <q-icon name="download"></q-icon>
                        </q-item-section>
                        <q-item-section>
                          <q-item-label>Download</q-item-label>
                        </q-item-section>
                      </q-item>

                      <q-item clickable v-close-popup
                        @click.prevent="onShowFileRemoveConfirmationDialog(file, fileIndex)">
                        <q-item-section avatar>
                          <q-icon name="delete"></q-icon>
                        </q-item-section>
                        <q-item-section>
                          <q-item-label>Remove</q-item-label>
                        </q-item-section>
                      </q-item>
                    </q-list>
                  </q-btn-dropdown>
                </td>
              </tr>
            </tbody>
          </q-markup-table>
          <q-dialog>
            <q-card style="width: 700px; max-width: 80vw;">
              <q-card-section class="row items-center q-pb-none">
                <div class="text-h6">{{ previewFile.name }} ({{ previewFile.humanSize }})</div>
                <q-space />
                <q-btn icon="close" flat round dense v-close-popup />
              </q-card-section>
              <q-img :src="'api2/file/' + previewFile.id" />
            </q-card>
          </q-dialog>

        </q-card-section>
        <q-card-section>
          <q-btn label="Save changes" type="submit" icon="save" class="full-width" color="dark"
            :disable="loading || saving || !document.title">
            <template v-slot:loading v-if="saving">
              <q-spinner-hourglass class="on-left" />
              {{ t('Saving...') }}
            </template>
          </q-btn>
        </q-card-section>
      </form>
    </q-card>
    <q-dialog v-model="showPreviewDialog">
      <q-card style="width: 700px; max-width: 80vw;">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">File preview</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-card-section class="q-pt-none">
          <q-img :src="'api2/file/' + previewFile.id" loading="lazy" spinner-color="white">
            <div class="absolute-bottom text-subtitle1 text-center">
              {{ previewFile.name }} ({{ previewFile.humanSize }})
            </div>
          </q-img>
          <q-card-actions align="right">
            <q-btn outline :href="'api2/file/' + previewFile.id" label="Download" icon="download" />
            <q-btn outline v-close-popup label="Close" icon="close" />
          </q-card-actions>
        </q-card-section>
        <!--
            <q-card-actions align="right">
              <q-btn flat label="Close" color="primary" v-close-popup />
            </q-card-actions>
            -->
      </q-card>
    </q-dialog>
    <q-dialog v-model="showConfirmDeleteFileDialog">
      <q-card>
        <q-card-section class="row items-center q-pb-none">
          Remove file xxx
        </q-card-section>
        <q-card-actions align="right">
          <q-btn flat label="Cancel" color="primary" v-close-popup />
          <q-btn flat label="Delete" color="primary" @click.prevent="onFileRemove(0)" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>

import { ref, computed } from "vue";
import { useRoute, useRouter } from "vue-router";
import { api } from 'boot/axios'
import { date, useQuasar } from 'quasar'
import { uid, format } from "quasar";
import { useI18n } from 'vue-i18n'
import { default as TagSelector } from "components/TagSelector.vue";

const $q = useQuasar();

const { t } = useI18n();

const { humanStorageSize } = format

const maxFileSize = 2097152;
const showPreviewDialog = ref(false);
const previewFile = ref(null);

const showConfirmDeleteFileDialog = ref(false);

const titleRef = ref(null);

const isNew = ref(false);
const loading = ref(false);
const saving = ref(false);

const newFileId = ref(uid());

const newUploadURL = computed(() => {
  return ('api2/file/' + newFileId.value);
});

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

const availableTags = ref([]);

function onRefresh() {
  loading.value = true;
  api.document
    .get(document.value.id)
    .then((response) => {
      document.value = response.data.data;
      document.value.createdOn = date.formatDate(document.value.createdOnTimestamp, 'YYYY-MM-DD HH:mm:ss');
      document.value.files.map((file) => {
        file.isNew = false;
        file.uploadedOn = date.formatDate(file.uploadedOnTimestamp, 'YYYY-MM-DD HH:mm:ss');
        file.humanSize = format.humanStorageSize(file.size);
        return (file);
      });
      loading.value = false;
      //this.$nextTick(() => this.$refs.title.focus());
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
            color: "negative",
            icon: "error",
            message: t("API Error: error loading document"),
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
        document.value.createdOn = date.formatDate(document.value.createdOnTimestamp, 'YYYY-MM-DD HH:mm:ss');
        document.value.files.map((file) => {
          file.isNew = false;
          file.uploadedOn = date.formatDate(file.uploadedOnTimestamp, 'YYYY-MM-DD HH:mm:ss');
          file.humanSize = format.humanStorageSize(file.size);
          return (file);
        });
        loading.value = false;
        //this.$nextTick(() => this.$refs.title.focus());
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
                color: "negative",
                icon: "error",
                message: t("API Error: missing document title param"),
              });
              // TODO: focus not working
              titleRef.value.focus();
            }
            break;
          case 401:
            this.$router.push({
              name: "signIn",
            });
            break;
          default:
            $q.notify({
              color: "negative",
              icon: "error",
              message: t("API Error: error updating document"),
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
                color: "negative",
                icon: "error",
                message: t("API Error: missing document title param"),
              });
              // TODO: focus not working
              titleRef.value.focus();
            }
            break;
          case 401:
            this.$router.push({
              name: "signIn",
            });
            break;
          default:
            $q.notify({
              color: "negative",
              icon: "error",
              message: t("API Error: error adding document"),
            });
            break;
        }
      });
  }

}

function onPreviewFile(file) {
  previewFile.value = file;
  showPreviewDialog.value = true;
}

function isImage(filename) {
  return (filename.match(/.(jpg|jpeg|png|gif)$/i));
}

const route = useRoute();
const router = useRouter();

router.beforeEach(async (to, from) => {
  if (to.name == "newDocument") {
    document.value = {
      id: null,
      title: null,
      description: null,
      created: null,
      createdOnTimestamp: null,
      createdBy: null,
      files: [],
      tags: []
    }
    isNew.value = true;
  } else if (to.name == "document") {
    isNew.value = false;
  }
})

function onFileAdded(e) {
  newFileId.value = uid();
}

function onShowFileRemoveConfirmationDialog(file, fileIndex) {
  showConfirmDeleteFileDialog.value = true;
}

function onFileRemove(fileIndex) {
  if (fileIndex > -1) {
    document.value.files.splice(fileIndex, 1);
    showConfirmDeleteFileDialog.value = false;
  }
}

function onFileUploaded(e) {
  document.value.files.push(
    {
      id: newFileId.value,
      uploadedOn: date.formatDate(new Date(), 'YYYY-MM-DD HH:mm:ss'),
      name: e.files[0].name,
      size: e.files[0].size,
      hash: null,
      humanSize: format.humanStorageSize(e.files[0].size),
      isNew: true
    }
  );
  console.log(e.files[0].name);
  newFileId.value = uid();
}

function onUploadRejected(e) {
  if (e[0].failedPropValidation == "max-file-size") {
    $q.notify({
      color: "negative",
      icon: "error",
      message: "Can not upload file " + e[0].file.name + ' (max upload filesize: ' + format.humanStorageSize(maxFileSize) + ', current file size: ' + format.humanStorageSize(e[0].file.size) + ')',
    });
  }
  console.log(e);
}

function onDeleteDocument() {
  // TODO:
}

document.value.id = route.params.id || null;
if (document.value.id) {
  isNew.value = false;
  onRefresh();
} else {
  isNew.value = true;
}

</script>
