<template>
  <q-dialog v-model="visible" @show="onShow" @hide="onClose">
    <q-card style="width: 60%; max-width: 80vw;">
      <q-card-section class="row items-center q-pb-none">
        <div class="text-h6">{{ t('Search') }}</div>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup />
      </q-card-section>
      <q-card-section>
        <q-input type="text" standout dense :label="t('Search text on title, description & notes')" v-model="text"
          @update:model-value="onFilter" autofocus="">
          <template v-slot:prepend>
            <q-icon name="search" />
          </template>
        </q-input>
      </q-card-section>
      <q-separator />
      <q-card-section style="height: 50vh; max-height: 50vh" class="scroll">
        <q-list>
          <q-item v-for="opt, index in searchResults" :key="opt.id" :to="{ name: 'document', params: { id: opt.id } }"
            :class="{ 'bg-grey-5': currentSearchResultSelectedIndex == index }">
            <q-item-section side>
              <q-icon name="collections_bookmark" />
            </q-item-section>
            <q-item-section>
              <q-item-label>{{ opt.label }}</q-item-label>
              <q-item-label caption>{{ opt.caption }}</q-item-label>
            </q-item-section>
          </q-item>
        </q-list>
      </q-card-section>
      <q-separator />
    </q-card>
  </q-dialog>
</template>

<script setup>

import { ref, useAttrs } from "vue";
import { api } from "boot/axios";
import { useI18n } from "vue-i18n";
import { useRouter } from "vue-router";
import { date } from "quasar";

const visible = ref(true);

const router = useRouter();

const attrs = useAttrs();

const { t } = useI18n();

const emit = defineEmits(['close']);

const text = ref("");
const searchResults = ref([]);
const currentSearchResultSelectedIndex = ref(-1);
const searching = ref(false);

function onFilter(val) {
  if (val && val.trim().length > 0) {
    searchResults.value = [];
    currentSearchResultSelectedIndex.value = -1;
    searching.value = true;
    api.document.search(1, 16, { title: val }, "title", "ASC")
      .then((success) => {
        searchResults.value = success.data.results.documents.map((document) => {
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
  } else {
    searchResults.value = [];
  }
}

function onKeyDown(event) {
  if (event.key === "ArrowUp") {
    if (searchResults.value.length > 0) {
      if (currentSearchResultSelectedIndex.value > 0) {
        currentSearchResultSelectedIndex.value--;
        event.preventDefault();
        event.stopPropagation();
      }
    }
  } else if (event.key === "ArrowDown") {
    if (searchResults.value.length > 0) {
      if (currentSearchResultSelectedIndex.value < searchResults.value.length - 1) {
        currentSearchResultSelectedIndex.value++;
        event.preventDefault();
        event.stopPropagation();
      }
    }
  } else if (event.key === "Enter") {
    if (searchResults.value.length > 0) {
      if (currentSearchResultSelectedIndex.value >= 0 && currentSearchResultSelectedIndex.value < searchResults.value.length) {
        router.push({
          name: "document",
          params: {
            id: searchResults.value[currentSearchResultSelectedIndex.value].id
          }
        });
      }
    }
  }
};
function onShow() {
  window.addEventListener('keydown', onKeyDown);
}

function onClose() {
  visible.value = false;
  searchResults.value = [];
  text.value = null;
  window.removeEventListener('keydown', onKeyDown);
  emit('close');
}

</script>