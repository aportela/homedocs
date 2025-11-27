<template>
  <BaseDialog v-model="visible" @show="onShow" @close="onClose" width="1280px" max-width="80vw">
    <template v-slot:header-left>
      {{ t('Search on HomeDocs...') }}
    </template>
    <template v-slot:header-right>
      <q-chip size="md" square class="gt-sm theme-default-q-chip shadow-1"
        v-if="!state.ajaxRunning && !state.ajaxErrors" v-show="totalResults > 0">
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
            :label="t('Search on')" @update:model-value="onSearch(text)" :disable="state.ajaxRunning">
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
            @update:model-value="onSearch(text)" clearable outlined ref="searchTextField">
            <template v-slot:prepend>
              <q-icon name="search" />
            </template>
          </q-input>
        </div>
      </div>
      <q-separator />
      <div v-if="state.ajaxErrors">
        <CustomErrorBanner :text="state.ajaxErrorMessage || 'Error loading data'"
          :api-error="state.ajaxAPIErrorDetails">
        </CustomErrorBanner>
      </div>
      <q-virtual-scroll v-else component="q-list" :items="searchResults" @virtual-scroll="onVirtualScroll"
        ref="virtualListRef" class="q-virtual-scroll-container">
        <template v-slot="{ item, index }">
          <div class="border-bottom-except-last-item"
            :class="{ 'current-keyboard-selected-item': currentSearchResultSelectedIndex === index }">
            <q-item :key="item.id" class="transparent-background text-color-primary q-pa-sm"
              :class="{ 'cursor-not-allowed is-current': item.id === currentDocumentId }"
              :clickable="item.id !== currentDocumentId" :to="{ name: 'document', params: { id: item.id } }">
              <q-item-section side class="gt-sm">
                <q-icon name="work" />
              </q-item-section>
              <q-item-section>
                <q-item-label>{{ item.title }}</q-item-label>
                <q-item-label caption v-if="item.matchedOnFragment">
                  <div v-html="item.matchedOnFragment"></div>
                </q-item-label>
              </q-item-section>
              <q-item-section side top>
                <q-item-label caption> ({{
                  item.updatedAt.timeAgo || item.createdAt.timeAgo }})
                  <span class="text-color-primary">{{ item.updatedAt.dateTime || item.createdAt.dateTime }}</span>
                </q-item-label>
                <ViewDocumentDetailsButton size="md" square class="min-width-9em" :count="item.attachmentCount"
                  :label="'Total attachments count'" :tool-tip="'View document attachments'"
                  :disable="state.ajaxRunning" @click.stop.prevent="onShowDocumentFiles(item.id, item.label)">
                </ViewDocumentDetailsButton>
                <ViewDocumentDetailsButton size="md" square class="min-width-9em" :count="item.noteCount"
                  :label="'Total notes'" :tool-tip="'View document notes'" :disable="state.ajaxRunning"
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
    <template v-slot:actions-prepend>
      <q-space />
      <q-select v-model="pager.resultsPage" filled @update:model-value="onChangeResultsPage"
        :options="resultsPageOptions" :label="t('Max results')" stack-label dense options-dense class="q-mr-md"
        style="min-width: 12em" />
    </template>
  </BaseDialog>
</template>

<script setup lang="ts">
import { ref, reactive, computed, watch, onMounted, onBeforeUnmount, nextTick } from "vue";
import { QInput, QVirtualScroll } from "quasar";
import { useI18n } from "vue-i18n";
import { useRoute, useRouter } from "vue-router";

import { bus, onShowDocumentFiles, onShowDocumentNotes } from "src/composables/useBus";
import { api } from "src/composables/api";
import { searchDialogResultsPage as localStorageSearchDialogResultsPage } from "src/composables/useLocalStorage"
import { type AjaxState as AjaxStateInterface, defaultAjaxState } from "src/types/ajax-state";
import { type SearchDocumentResponse as SearchDocumentResponseInterface, type SearchDocumentResponseItem as SearchDocumentResponseItemInterface } from "src/types/api-responses";
import { SearchDocumentItemClass } from "src/types/search-document-item";
import { type QuasarVirtualScrollEventDetails as QuasarVirtualScrollEventDetailsInterface } from "src/types/quasar-virtual-scroll-event-details";
import { DateTimeClass } from "src/types/date-time";
import { type Pager as PagerInterface } from "src/types/pager";
import { type Sort as SortInterface } from "src/types/sort";
import { type SearchFilter as SearchFilterInterface } from "src/types/search-filter";


import { default as BaseDialog } from "src/components/Dialogs/BaseDialog.vue"
import { default as CustomErrorBanner } from "src/components/Banners/CustomErrorBanner.vue";
import { default as CustomBanner } from "src/components/Banners/CustomBanner.vue";
import { default as ViewDocumentDetailsButton } from "src/components/Buttons/ViewDocumentDetailsButton.vue";

interface SearchDialogProps {
  modelValue: boolean;
};

const props = defineProps<SearchDialogProps>();

const router = useRouter();
const currentRoute = useRoute();

// if we are on document page, get current document id
const currentDocumentId = ref(currentRoute.name == "document" ? currentRoute.params?.id || null : null);

const { t } = useI18n();

const emit = defineEmits(['update:modelValue', 'close']);

const visible = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit('update:modelValue', value);
  }
});

const state: AjaxStateInterface = reactive({ ...defaultAjaxState });

const searchOnOptions = computed(() => [
  { label: 'Title', value: 'title' },
  { label: 'Description', value: 'description' },
  { label: 'Notes', value: 'notesBody' },
  { label: 'Attachment names', value: 'attachmentsFilename' },
]);

const sort: SortInterface = {
  field: "lastUpdateTimestamp",
  label: "",
  order: "DESC"
};

const searchOn = ref(searchOnOptions.value[0]!);

watch(() => searchOn.value, () => {
  nextTick()
    .then(() => {
      searchTextField.value?.focus();
    }).catch((e) => {
      console.error(e);
    });
});

const virtualListRef = ref<QVirtualScroll | null>(null);

const pager = reactive<PagerInterface>({
  currentPageIndex: 1,
  resultsPage: localStorageSearchDialogResultsPage.get(),
  totalResults: 0,
  totalPages: 0,
});

const totalResults = ref<number>(0);

const resultsPageOptions = [1, 2, 3, 4, 6, 8, 12, 16, 24, 32, 64, 128];

const onChangeResultsPage = (value: number) => {
  localStorageSearchDialogResultsPage.set(value);
  onSearch(text.value);
};

const text = ref<string>("");
const searchResults = reactive<Array<SearchDocumentItemClass>>([]);
const currentSearchResultSelectedIndex = ref<number>(-1);
const virtualListIndex = ref<number>(0);

const showNoSearchResults = ref<boolean>(false);

const searchTextField = ref<QInput | null>(null);

const onSearch = (val: string) => {
  showNoSearchResults.value = false;
  totalResults.value = 0;
  if (val && val.trim().length > 0) {
    currentSearchResultSelectedIndex.value = -1;
    Object.assign(state, defaultAjaxState);
    state.ajaxRunning = true;
    const params: SearchFilterInterface = {
      text: {
        title: searchOn.value.value == "title" ? val.trim() : null,
        description: searchOn.value.value == "description" ? val.trim() : null,
        notesBody: searchOn.value.value == "notesBody" ? val.trim() : null,
        attachmentsFilename: searchOn.value.value == "attachmentsFilename" ? val.trim() : null,
      },
      tags: [],
      dates: {
        createdAt: null,
        lastUpdateAt: null,
        updatedAt: null,
      },
    };
    api.document.search(pager, params, sort, true)
      .then((successResponse: SearchDocumentResponseInterface) => {
        searchResults.length = 0;
        totalResults.value = successResponse.data.results.pagination.totalResults;
        searchResults.push(...successResponse.data.results.documents.map((document: SearchDocumentResponseItemInterface) =>
          new SearchDocumentItemClass(
            t,
            document.id,
            new DateTimeClass(t, document.createdAtTimestamp),
            new DateTimeClass(t, document.updatedAtTimestamp),
            document.title,
            document.description,
            document.tags,
            document.attachmentCount,
            document.noteCount,
            document.matchedFragments,
            val.trim(),
          )
        ));
        showNoSearchResults.value = successResponse.data.results.documents.length <= 0; // REQUIRED ?
      })
      .catch((errorResponse) => {
        state.ajaxErrors = true;
        if (errorResponse.isAPIError) {
          state.ajaxAPIErrorDetails = errorResponse.customAPIErrorDetails;
          switch (errorResponse.response.status) {
            case 401:
              state.ajaxErrorMessage = "Auth session expired, requesting new...";
              bus.emit("reAuthRequired", { emitter: "SearchDialog.onSearch" });
              break;
            default:
              state.ajaxErrorMessage = "API Error: fatal error";
              break;
          }
        } else {
          state.ajaxErrorMessage = `Uncaught exception: ${errorResponse}`;
          console.error(errorResponse);
        }

      }).finally(() => {
        state.ajaxRunning = false;
      });
  } else {
    searchResults.length = 0;
  }
};

const onVirtualScroll = (details: QuasarVirtualScrollEventDetailsInterface) => {
  virtualListIndex.value = details.index;
};

const scrollToItem = (index: number) => {
  if (virtualListRef.value) {
    virtualListRef.value.scrollTo(index, "start-force");
  }
};

const onKeyDown = (evt: KeyboardEvent) => {
  if (evt.key === "ArrowUp") {
    if (searchResults.length > 0) {
      if (currentSearchResultSelectedIndex.value > 0) {
        currentSearchResultSelectedIndex.value--;
        scrollToItem(currentSearchResultSelectedIndex.value);
        evt.preventDefault();
        evt.stopPropagation();
      }
    }
  } else if (evt.key === "ArrowDown") {
    if (searchResults.length > 0) {
      if (currentSearchResultSelectedIndex.value < searchResults.length - 1) {
        currentSearchResultSelectedIndex.value++;
        scrollToItem(currentSearchResultSelectedIndex.value);
        evt.preventDefault();
        evt.stopPropagation();
      }
    }
  } else if (evt.key === "Enter") {
    if (searchResults.length > 0) {
      if (currentSearchResultSelectedIndex.value >= 0 && currentSearchResultSelectedIndex.value < searchResults.length) {
        router.push({
          name: "document",
          params: {
            id: searchResults[currentSearchResultSelectedIndex.value]!.id
          }
        }).catch((e) => {
          console.error(e);
        });
      }
    }
  }
};

const onShow = () => {
  totalResults.value = 0;
  pager.resultsPage = localStorageSearchDialogResultsPage.get();
  // this is required here because this dialog v-model is controller from MainLayout.vue
  // DOES NOT WORK with onMounted/onBeforeUnmount. WE ONLY WANT CAPTURE KEY EVENTS WHEN DIALOG IS VISIBLE
  window.addEventListener('keydown', onKeyDown);
  nextTick()
    .then(() => {
      searchTextField.value?.focus();
    }).catch((e) => {
      console.error(e);
    });
}

const onClose = () => {
  searchResults.length = 0;
  text.value = "";
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

.is-current {
  background-color: #f5e4e4 !important;
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

  .is-current .q-item__label {
    color: var(--color-zinc-950) !important;
  }
}
</style>