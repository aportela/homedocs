<template>
  <BaseDialog v-model="visible" @show="onShow" @close="onClose" width="1280px" max-width="80vw">
    <template v-slot:header-left>
      {{ t('Search on HomeDocs...') }}
    </template>
    <template v-slot:header-right>
      <q-chip size="md" square class="gt-sm theme-default-q-chip shadow-1" v-if="!state.loading && !state.loadingError"
        v-show="totalResults > 0">
        <q-avatar class="theme-default-q-avatar">{{ totalResults }}</q-avatar>
        {{ t("Results count",
          {
            count:
              totalResults
          }) }}
      </q-chip>
    </template>
    <template v-slot:body>
      <div class="q-pa-sm row items-center q-gutter-sm">
        <div class="col-auto">
          <q-select v-model="searchOn" :options="searchOnOptions"
            :display-value="`${searchOn ? t(searchOn.label) : ''}`" dense options-dense outlined style="min-width: 8em;"
            :label="t('Search on')" @update:model-value="onSearch(text)" :disable="state.loading">
            <template v-slot:option="scope">
              <q-item v-bind="scope.itemProps">
                <q-item-section>
                  <q-item-label>
                    {{ t(scope.label) }}
                  </q-item-label>
                </q-item-section>
              </q-item>
            </template>
          </q-select>
        </div>
        <div style="flex-grow: 1;">
          <q-input type="text" dense color="grey-3" label-color="grey-7" :label="t('Search text...')" v-model="text"
            @update:model-value="onSearch" autofocus="" clearable outlined>
            <template v-slot:prepend>
              <q-icon name="search" />
            </template>
          </q-input>
        </div>
      </div>
      <q-separator />
      <div v-if="state.loadingError">
        <CustomErrorBanner :text="state.errorMessage || 'Error loading data'" :api-error="state.apiError">
        </CustomErrorBanner>
      </div>
      <q-virtual-scroll v-else component="q-list" :items="searchResults" @virtual-scroll="onVirtualScroll"
        ref="virtualListRef" class="q-virtual-scroll-container">
        <template v-slot="{ item, index }">
          <div class="border-bottom-except-last-item"
            :class="{ 'current-keyboard-selected-item': currentSearchResultSelectedIndex === index }">
            <q-item :key="item.id" class="transparent-background text-color-primary q-pa-sm"
              :class="{ 'cursor-not-allowed': item.id === currentDocumentId }"
              :clickable="item.id !== currentDocumentId" :to="{ name: 'document', params: { id: item.id } }">
              <q-item-section side>
                <q-icon name="collections_bookmark" />
              </q-item-section>
              <q-item-section>
                <q-item-label>{{ item.label }}</q-item-label>
                <q-item-label caption v-if="item.matchedOnFragment">
                  <div v-html="item.matchedOnFragment"></div>
                </q-item-label>
              </q-item-section>
              <q-item-section side top>
                <q-item-label caption>{{ item.lastUpdate }}
                </q-item-label>
                <ViewDocumentDetailsButton size="md" square class="min-width-9em" :count="item.attachmentCount"
                  :label="'Total attachments count'" :tool-tip="'View document attachments'" :disable="state.loading"
                  @click.stop.prevent="onShowDocumentFiles(item.id, item.label)">
                </ViewDocumentDetailsButton>
                <ViewDocumentDetailsButton size="md" square class="min-width-9em" :count="item.noteCount"
                  :label="'Total notes'" :tool-tip="'View document notes'" :disable="state.loading"
                  @click.stop.prevent="onShowDocumentNotes(item.id, item.label)">
                </ViewDocumentDetailsButton>
              </q-item-section>
            </q-item>
          </div>
        </template>
        <template v-slot:before v-if="showNoSearchResults">
          <CustomBanner warning class="q-ma-md"
            text="Unfortunately, your search didn't return any results. You might want to modify your filters or search terms">
          </CustomBanner>
        </template>
      </q-virtual-scroll>
    </template>
  </BaseDialog>
</template>

<script setup>

import { ref, reactive, computed, onMounted, onBeforeUnmount } from "vue";
import { useI18n } from "vue-i18n";
import { useRoute, useRouter } from "vue-router";

import { useBus } from "src/composables/useBus";
import { useAPI } from "src/composables/useAPI";
import { useFormatDates } from "src/composables/useFormatDates"

import { default as BaseDialog } from "src/components/Dialogs/BaseDialog.vue"
import { default as CustomErrorBanner } from "src/components/Banners/CustomErrorBanner.vue";
import { default as CustomBanner } from "src/components/Banners/CustomBanner.vue";
import { default as ViewDocumentDetailsButton } from "src/components/Buttons/ViewDocumentDetailsButton.vue";

const props = defineProps({
  modelValue: {
    type: Boolean,
    required: true
  }
});

const router = useRouter();
const currentRoute = useRoute();

// if we are on document page, get current document id
const currentDocumentId = ref(currentRoute.name == "document" ? currentRoute.params?.id || null : null);

const { t } = useI18n();

const { api } = useAPI();
const { fullDateTimeHuman } = useFormatDates();
const { bus, onShowDocumentFiles, onShowDocumentNotes } = useBus();

const emit = defineEmits(['update:modelValue', 'close']);

const visible = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit('update:modelValue', value);
  }
});

const state = reactive({
  loading: false,
  loadingError: false,
  errorMessage: null,
  apiError: null
});

const searchOnOptions = computed(() => [
  { label: 'Title', value: 'title' },
  { label: 'Description', value: 'description' },
  { label: 'Notes', value: 'notesBody' },
  { label: 'Attachment names', value: 'attachmentsFilename' },
]);

const searchOn = ref(searchOnOptions.value[0]);

const virtualListRef = ref(null);

const totalResults = ref(0);

const text = ref("");
const searchResults = reactive([]);
const currentSearchResultSelectedIndex = ref(-1);
const virtualListIndex = ref(0);

const showNoSearchResults = ref(false);

const boldStringMatch = (str, matchWord) => {
  return str.replace(
    new RegExp(matchWord, "gi"),
    (match) => `<strong>${match}</strong>`
  );
};

const onSearch = (val) => {
  showNoSearchResults.value = false;
  totalResults.value = 0;
  if (val && val.trim().length > 0) {
    currentSearchResultSelectedIndex.value = -1;
    state.loading = true;
    state.loadingError = false;
    state.errorMessage = null;
    state.apiError = null;
    const params = {
      text: {
        title: searchOn.value.value == "title" ? val.trim() : null,
        description: searchOn.value.value == "description" ? val.trim() : null,
        notesBody: searchOn.value.value == "notesBody" ? val.trim() : null,
        attachmentsFilename: searchOn.value.value == "attachmentsFilename" ? val.trim() : null,
      }
    };
    api.document.search(1, 16, params, "lastUpdateTimestamp", "DESC")
      .then((successResponse) => {
        searchResults.length = 0;
        totalResults.value = successResponse.data.results.pagination.totalResults;
        searchResults.push(...successResponse.data.results.documents.map((document) => {
          document.matchedOnFragment = null;
          if (Array.isArray(document.matchedFragments) && document.matchedFragments.length > 0) {
            document.matchedOnFragment = t("Fast search match fragment",
              {
                fragment: document.matchedFragments[0].fragment ? `${boldStringMatch(document.matchedFragments[0].fragment, val)}` : '',
                matchedOn: t(document.matchedFragments[0].matchedOn)
              }
            );
          }
          return (
            {
              id: document.id,
              label: document.title,
              createdOn: fullDateTimeHuman(document.createdOnTimestamp),
              lastUpdate: fullDateTimeHuman(document.lastUpdateTimestamp),
              attachmentCount: document.attachmentCount,
              noteCount: document.noteCount,
              matchedOnFragment: document.matchedOnFragment
            });
        }));
        state.loading = false;
        showNoSearchResults.value = successResponse.data.results.documents.length <= 0; // REQUIRED ?
      })
      .catch((errorResponse) => {
        state.loadingError = true;
        switch (errorResponse.response.status) {
          case 401:
            state.apiError = errorResponse.customAPIErrorDetails;
            state.errorMessage = "Auth session expired, requesting new...";
            bus.emit("reAuthRequired", { emitter: "SearchDialog.onSearch" });
            break;
          default:
            state.apiError = errorResponse.customAPIErrorDetails;
            state.errorMessage = "API Error: fatal error";
            break;
        }
        state.loading = false;
      });
  } else {
    searchResults.length = 0;
  }
};

const onVirtualScroll = (index) => {
  virtualListIndex.value = index
};

const scrollToItem = (index) => {
  if (virtualListRef.value) {
    virtualListRef.value.scrollTo(index, "start-force");
  }
};

const onKeyDown = (event) => {
  if (event.key === "ArrowUp") {
    if (searchResults.length > 0) {
      if (currentSearchResultSelectedIndex.value > 0) {
        currentSearchResultSelectedIndex.value--;
        scrollToItem(currentSearchResultSelectedIndex.value);
        event.preventDefault();
        event.stopPropagation();
      }
    }
  } else if (event.key === "ArrowDown") {
    if (searchResults.length > 0) {
      if (currentSearchResultSelectedIndex.value < searchResults.length - 1) {
        currentSearchResultSelectedIndex.value++;
        scrollToItem(currentSearchResultSelectedIndex.value);
        event.preventDefault();
        event.stopPropagation();
      }
    }
  } else if (event.key === "Enter") {
    if (searchResults.length > 0) {
      if (currentSearchResultSelectedIndex.value >= 0 && currentSearchResultSelectedIndex.value < searchResults.length) {
        router.push({
          name: "document",
          params: {
            id: searchResults[currentSearchResultSelectedIndex.value].id
          }
        });
      }
    }
  }
};

const onShow = () => {
  // this is required here because this dialog v-model is controller from MainLayout.vue
  // DOES NOT WORK with onMounted/onBeforeUnmount. WE ONLY WANT CAPTURE KEY EVENTS WHEN DIALOG IS VISIBLE
  window.addEventListener('keydown', onKeyDown);
}

const onClose = () => {
  searchResults.length = 0;
  text.value = null;
  showNoSearchResults.value = false;
  // this is required here because this modal dialog is persistent (from MainLayout.vue).
  // DOES NOT WORK with onMounted/onBeforeUnmount. WE ONLY WANT CAPTURE KEY EVENTS WHEN DIALOG IS VISIBLE
  window.removeEventListener('keydown', onKeyDown);
  emit('close');
}

onMounted(() => {
  bus.on("reAuthSucess", (msg) => {
    if (msg.to?.includes("SearchDialog.onSearch")) {
      onSearch(text.value);
    }
  });
});

onBeforeUnmount(() => {
  bus.off("reAuthSucess");
});

</script>

<style lang="css" scoped>
.q-virtual-scroll-container {
  height: 50vh;
}

.body--light {
  .current-keyboard-selected-item {
    background: var(--color-zinc-300);
  }
}

.body--dark {
  .current-keyboard-selected-item {
    background: var(--color-zinc-600);
  }
}
</style>