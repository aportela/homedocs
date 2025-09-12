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
      <q-card-section>
        <q-virtual-scroll component="q-list" :items="searchResults" @virtual-scroll="onVirtualScroll"
          ref="virtualListRef" style="height: 50vh; max-height: 50vh">
          <template v-slot="{ item, index }">
            <q-item :key="item.id" class="cursor-pointer"
              :class="{ 'bg-grey-5': currentSearchResultSelectedIndex === index }"
              :to="{ name: 'document', params: { id: item.id } }">
              <q-item-section side>
                <q-icon name="collections_bookmark" />
              </q-item-section>
              <q-item-section>
                <q-item-label>{{ item.label }}</q-item-label>
                <q-item-label caption>{{ item.caption }}</q-item-label>
              </q-item-section>
            </q-item>
          </template>
        </q-virtual-scroll>
      </q-card-section>
      <q-separator />
    </q-card>
  </q-dialog>
</template>

<script setup>

import { ref } from "vue";
import { api } from "boot/axios";
import { useI18n } from "vue-i18n";
import { useRouter } from "vue-router";
import { date } from "quasar";

const visible = ref(true);

const router = useRouter();

const { t } = useI18n();

const emit = defineEmits(['close']);

const virtualListRef = ref(null);
const text = ref("");
const searchResults = ref([]);
const currentSearchResultSelectedIndex = ref(-1);
const virtualListIndex = ref(0);
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

const onVirtualScroll = (index) => {
  virtualListIndex.value = index
};

const scrollToItem = (index) => {
  if (virtualListRef.value) {
    virtualListRef.value.scrollTo(index, "start-force");
  }
};

function onKeyDown(event) {
  if (event.key === "ArrowUp") {
    if (searchResults.value.length > 0) {
      if (currentSearchResultSelectedIndex.value > 0) {
        currentSearchResultSelectedIndex.value--;
        scrollToItem(currentSearchResultSelectedIndex.value);
        event.preventDefault();
        event.stopPropagation();
      }
    }
  } else if (event.key === "ArrowDown") {
    if (searchResults.value.length > 0) {
      if (currentSearchResultSelectedIndex.value < searchResults.value.length - 1) {
        currentSearchResultSelectedIndex.value++;
        scrollToItem(currentSearchResultSelectedIndex.value);
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