<template>
  <q-page class="bg-grey-2">
    <q-card>
      <form @submit.prevent.stop="onSubmitForm" autocorrect="off" autocapitalize="off" autocomplete="off"
        spellcheck="false">
        <q-card-section>
          <h3 v-if="!document.id">New document</h3>
          <h3 v-else>Document</h3>
        </q-card-section>
        <q-card-section>
          <q-input outlined v-model="document.title" type="text" name="title" label="Título del documento"
            :disable="loading" :autofocus="true">
          </q-input>
        </q-card-section>
        <q-card-section>
          <q-input class="q-mb-sm" outlined v-model="document.description" type="textarea" autogrow name="title"
            label="Descripción del documento" :disable="loading" :autofocus="true">
          </q-input>
        </q-card-section>
        <q-card-section>
          <q-select label="Tags" outlined v-model="document.tags" use-input use-chips multiple hide-dropdown-icon
            input-debounce="0" new-value-mode="add-unique" clearable />
        </q-card-section>
        <q-card-section>
          <q-uploader class="q-mb-md" label="Add new file" flat bordered auto-upload hide-upload-btn color="dark"
            field-name="file" :url="newUploadURL" @added="onFileAdded" @uploaded="onFileUploaded" method="post" multiple
            style="width: 100%;" :disable="loading" />
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
                  <q-btn-group spread>
                    <q-btn label="Open/Preview" icon="preview" @click.prevent="onPreviewFile(file)"
                      :disable="loading || !isImage(file.name)" />
                    <q-btn label="Download" icon="download" :href="'api2/file/' + file.id" :disable="loading" />
                    <q-btn label="Remove" icon="delete" :disable="loading"
                      @click.prevent="onShowFileRemoveConfirmationDialog(file, fileIndex)" />
                  </q-btn-group>
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
        </q-card-section>
      </form>
    </q-card>

  </q-page>
</template>

<script setup>

import { ref, computed } from "vue";
import { useRoute, useRouter } from "vue-router";
import { api } from 'boot/axios'
import { date } from 'quasar'
import { uid, format } from "quasar";


const { humanStorageSize } = format

const showPreviewDialog = ref(false);
const previewFile = ref(null);

const showConfirmDeleteFileDialog = ref(false);

const loading = ref(false);

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

function onLoadAvailableTags() {
  loading.value = true;
  api.tag.search().then((response) => {
    availableTags.value = response.data.tags;
    loading.value = false;
  }).catch((error) => {
    loading.value = false;
    switch (error.response.status) {
      case 401:
        this.$router.push({
          name: "signIn",
        });
        break;
      default:
        /*
        this.$emit("showAPIError", {
          httpCode: error.response.status,
          data: error.response.getApiErrorData(),
        });
        */
        break;
    }
  });
}

function onRefresh() {
  loading.value = true;
  api.document
    .get(document.value.id)
    .then((response) => {
      document.value = response.data.data;
      document.value.createdOn = date.formatDate(document.value.createdOnTimestamp, 'YYYY-MM-DD HH:mm:ss');
      document.value.files.map((file) => {
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
          /*
          this.$emit("showAPIError", {
            httpCode: error.response.status,
            data: error.response.getApiErrorData(),
          });
          */
          break;
      }
    });
}

function onSubmitForm() {
}

function onPreviewFile(file) {
  previewFile.value = file;
  showPreviewDialog.value = true;
}

function isImage(filename) {
  return (filename.match(/.(jpg|jpeg|png|gif)$/i));
}

const route = useRoute();
//const router = useRouter();

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
      humanSize: format.humanStorageSize(e.files[0].size)
    }
  );
  newFileId.value = uid();
}

document.value.id = route.params.id || null;

onLoadAvailableTags();
if (document.value.id) {
  onRefresh();
}
</script>
