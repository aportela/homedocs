<template>
  <q-dialog v-model="visible" @show="onShow" @hide="onClose">
    <q-card style="width: 60%; max-width: 80vw;">
      <q-card-section class="row items-center q-pb-none">
        <div class="text-h6">{{ t('Search') }}</div>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup />
      </q-card-section>
      <q-card-section>
        <div class="row items-center q-gutter-sm">
          <q-col class="col-auto">
            <q-select v-model="searchOn" :options="options" :display-value="`${searchOn ? t(searchOn.label) : ''}`"
              dense outlined style="min-width: 8em;" :label="t('Search on')" @update:model-value="onFilter(text)" />
          </q-col>
          <q-col style="flex-grow: 1;">
            <q-input type="text" dense color="grey-3" label-color="grey-7" :label="t('Search text...')" v-model="text"
              @update:model-value="onFilter" autofocus="" clearable outlined>
              <template v-slot:prepend>
                <q-icon name="search" />
              </template>
            </q-input>
          </q-col>
        </div>
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
                <!--
                <q-item-label caption>{{ item.caption }}</q-item-label>
                -->
                <q-item-label caption v-if="item.fragment">
                  <div v-html="item.fragment"></div>
                </q-item-label>
              </q-item-section>
              <q-item-section side top>
                <q-item-label caption>{{ item.createdOn }}
                </q-item-label>
                <q-chip size="sm" square text-color="dark" class="full-width">
                  <q-avatar color="grey-9" text-color="white">{{ item.fileCount }}</q-avatar>
                  {{ t("Files") }}
                </q-chip>
                <q-chip size="sm" square text-color="dark" class="full-width">
                  <q-avatar color="grey-9" text-color="white">{{ item.noteCount }}</q-avatar>
                  {{ t("Notes") }}
                </q-chip>
              </q-item-section>
            </q-item>
            <!--
            <q-separator v-if="index !== searchResults.length - 1" class="q-my-md" />
            -->
          </template>
        </q-virtual-scroll>
      </q-card-section>
      <q-separator />
    </q-card>
  </q-dialog>
</template>

<script setup>

import { ref, computed } from "vue";
import { useI18n } from "vue-i18n";
import { useRouter } from "vue-router";
import { useQuasar, date } from "quasar";
import { api } from "boot/axios";

const visible = ref(true);

const router = useRouter();

const $q = useQuasar();

const { t } = useI18n();

const emit = defineEmits(['close']);

const virtualListRef = ref(null);
const text = ref("");
const searchResults = ref([]);
const currentSearchResultSelectedIndex = ref(-1);
const virtualListIndex = ref(0);
const searching = ref(false);
const options = computed(() => [
  { label: 'Title', value: 'title' },
  { label: 'Description', value: 'description' },
  { label: 'Notes', value: 'notes' },
]);

const searchOn = ref(options.value[0]);

const boldStringMatch = (str, matchWord) => {
  return str.replace(
    new RegExp(matchWord, "gi"),
    (match) => `<strong>${match}</strong>`
  );
};

function onFilter(val) {
  if (val && val.trim().length > 0) {
    searchResults.value = [];
    currentSearchResultSelectedIndex.value = -1;
    searching.value = true;
    let params = {};
    switch (searchOn.value.value) {
      case "title":
        params.title = val;
        break;
      case "description":
        params.description = val;
        break;
      case "notes":
        params.notesBody = val;
        break;
    }
    api.document.search(1, 16, params, "title", "ASC")
      .then((success) => {
        searchResults.value = success.data.results.documents.map((document) => {
          document.createdOn = date.formatDate(document.createdOnTimestamp * 1000, 'YYYY-MM-DD HH:mm:ss');
          document.lastUpdate = null; //date.formatDate(document.lastUpdateTimestamp * 1000, 'YYYY-MM-DD HH:mm:ss');
          return (
            {
              id: document.id,
              label: document.title,
              caption: t(
                "Fast search caption", {
                creation: date.formatDate(document.createdOnTimestamp * 1000, 'YYYY-MM-DD HH:mm:ss'),
                attachmentCount: document.fileCount,
                noteCount: document.noteCount
              }
              ),
              createdOn: document.createdOn,
              fileCount: document.fileCount,
              noteCount: document.noteCount,
              fragment: t(
                "Fast search match fragment", {
                fragment: document.fragment ? `${boldStringMatch(document.fragment, val)}` : ''
              }
              )
            });
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
