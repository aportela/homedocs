<template>
  <q-page class="bg-grey-2">
    <h3 v-if="!document.id">New document</h3>
    <h3 v-else>Document</h3>
    <form @submit.prevent.stop="onSubmitForm" autocorrect="off" autocapitalize="off" autocomplete="off"
      spellcheck="false">
      <q-card-section class="text-left">
        <q-input outlined v-model="document.title" type="text" name="title" label="Título del documento"
          :disable="loading" :autofocus="true">
        </q-input>
      </q-card-section>
      <q-card-section class="text-left">
        <q-input class="q-mb-sm" outlined v-model="document.description" type="textarea" autogrow name="title"
          label="Descripción del documento" :disable="loading" :autofocus="true">
        </q-input>
      </q-card-section>
      <q-card-section class="text-left">
        <h5>Tags</h5>
        <q-chip square color="dark" text-color="white" v-for="tag in document.tags" :key="tag" removable>
          {{ tag }}
        </q-chip>
      </q-card-section>
      <q-card-section class="text-left">
        <h5>Files</h5>
        <q-markup-table>
          <thead>
            <tr>
              <th class="text-left">Created on</th>
              <th class="text-left">Name</th>
              <th class="text-right">Size</th>
              <th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="file in document.files" :key="file.id">
              <td class="text-left">{{ file.uploadedOn }}</td>
              <td class="text-left">{{ file.name }}</td>
              <td class="text-right">{{ file.humanSize }}</td>
              <td class="text-center">
                <q-btn-group>
                  <q-btn label="Open/Preview" icon="preview" />
                  <q-btn label="Download" icon="download" />
                  <q-btn label="Remove" icon="delete" />
                </q-btn-group>
              </td>
            </tr>
          </tbody>
        </q-markup-table>
      </q-card-section>
    </form>

  </q-page>
</template>

<script setup>

import { ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import { api } from 'boot/axios'
import { date } from 'quasar'
import { format } from "quasar";
const { humanStorageSize } = format

const loading = ref(false);

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

const route = useRoute();
//const router = useRouter();


document.value.id = route.params.id || null;

if (document.value.id) {
  onRefresh();
}
</script>
