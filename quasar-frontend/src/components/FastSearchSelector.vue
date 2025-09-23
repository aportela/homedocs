<template>
  <q-select v-bind="attrs" ref="search" standout use-input hide-selected :placeholder="t('Search...')" v-model="text"
    :options="filteredOptions" @filter="onFilter">
    <template v-slot:prepend>
      <q-icon name="search" />
    </template>
    <template v-slot:no-option v-if="searching">
      <q-item>
        <q-item-section>
          <div class="text-center">
            <q-spinner-pie color="grey-5" size="24px" />
          </div>
        </q-item-section>
      </q-item>
    </template>
    <template v-slot:option="scope">
      <q-list>
        <q-item v-bind="scope.itemProps" :to="{ name: 'document', params: { id: scope.opt.id } }">
          <q-item-section side>
            <q-icon name="collections_bookmark" />
          </q-item-section>
          <q-item-section>
            <q-item-label>{{ scope.opt.label }}</q-item-label>
            <q-item-label caption>{{ scope.opt.caption }}</q-item-label>
          </q-item-section>
        </q-item>
      </q-list>
    </template>
  </q-select>

</template>

<script setup>

import { ref, useAttrs } from "vue";
import { api } from "src/boot/axios";
import { useI18n } from "vue-i18n";
import { date } from "quasar";

const attrs = useAttrs();
const { t } = useI18n();
const text = ref("");
const filteredOptions = ref([]);
const searching = ref(false);

function onFilter(val, update) {
  if (val && val.trim().length > 0) {
    filteredOptions.value = [];
    searching.value = true;
    update(() => {
      api.document.search(1, 8, { title: val }, "title", "ASC")
        .then((success) => {
          filteredOptions.value = success.data.results.documents.map((document) => {
            return ({ id: document.id, label: document.title, caption: t("Fast search caption", { creation: date.formatDate(document.createdOnTimestamp * 1000, 'YYYY-MM-DD HH:mm:ss'), attachmentCount: document.fileCount }) });
          });
          searching.value = false;
          return;
        })
        .catch((error) => {
          searching.value = false;
          $q.notify({
            type: "negative",
            message: t("API Error: fatal error"),
            caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
          });
          return;
        });
    });
  } else {
    update(() => {
      filteredOptions.value = [];
    });
    return;
  }
}

</script>