<template>
  <q-dialog v-model="isVisible" @show="onShow" @hide="onClose">
    <q-card style="width: 60%; max-width: 80vw;">
      <q-card-section class="row items-center q-pb-none">
        <div class="text-h6">{{ t('Search on HomeDocs...') }}</div>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup aria-label="Close modal" />
      </q-card-section>
      <q-card-section>
        <div class="row items-center q-gutter-sm">
          <div class="col-auto">
            <q-select v-model="searchOn" :options="searchOnOptions"
              :display-value="`${searchOn ? t(searchOn.label) : ''}`" dense options-dense outlined
              style="min-width: 8em;" :label="t('Search on')" @update:model-value="onSearch(text)"
              :disable="state.loading">
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
      </q-card-section>
      <q-separator />
      <q-card-section>
        <div v-if="state.loadingError">
          <CustomErrorBanner :text="state.errorMessage || 'Error loading data'" :api-error="state.apiError">
          </CustomErrorBanner>
        </div>
        <q-virtual-scroll v-else component="q-list" :items="searchResults" @virtual-scroll="onVirtualScroll"
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
                <q-item-label caption v-if="item.matchedOnFragment">
                  <div v-html="item.matchedOnFragment"></div>
                </q-item-label>
              </q-item-section>
              <q-item-section side top>
                <q-item-label caption>{{ item.lastUpdate }}
                </q-item-label>
                <ViewDocumentDetailsButton size="md" square class="min-width-7em" :count="item.fileCount"
                  :label="'Total files'" :tool-tip="'View document attachments'" :disable="state.loading"
                  @click.stop.prevent="onShowDocumentFiles(item.id, item.label)">
                </ViewDocumentDetailsButton>
                <ViewDocumentDetailsButton size="md" square class="min-width-7em" :count="item.noteCount"
                  :label="'Total notes'" :tool-tip="'View document notes'" :disable="state.loading"
                  @click.stop.prevent="onShowDocumentNotes(item.id, item.label)">
                </ViewDocumentDetailsButton>
              </q-item-section>
            </q-item>
            <!--
            <q-separator v-if="index !== searchResults.length - 1" class="q-my-md" />
            -->
          </template>
          <template v-slot:before>
            <q-item v-show="showNoSearchResults">
              <CustomBanner warning :text="noResultsMessage"></CustomBanner>
            </q-item>
          </template>
        </q-virtual-scroll>
      </q-card-section>
      <q-separator />
    </q-card>
  </q-dialog>
</template>

<script setup>

import { ref, watch, reactive, computed, onMounted, onBeforeUnmount } from "vue";
import { useI18n } from "vue-i18n";
import { useRouter } from "vue-router";
import { useQuasar, date } from "quasar";

import { bus } from "src/boot/bus";
import { api } from "src/boot/axios";
import { useBusDialog } from "src/composables/busDialog";

import { default as CustomErrorBanner } from "src/components/Banners/CustomErrorBanner.vue";
import { default as CustomBanner } from "src/components/Banners/CustomBanner.vue";
import { default as ViewDocumentDetailsButton } from "src/components/Buttons/ViewDocumentDetailsButton.vue";

const props = defineProps({
  visible: {
    type: Boolean,
    required: true,
    default: false,
  }
});

const router = useRouter();

const $q = useQuasar();

const { t } = useI18n();

const { onShowDocumentFiles, onShowDocumentNotes } = useBusDialog();

const emit = defineEmits(['close']);

const isVisible = ref(props.visible);

watch(() => props.visible, val => isVisible.value = val);

const state = reactive({
  loading: false,
  loadingError: false,
  errorMessage: null,
  apiError: null
});

const searchOnOptions = computed(() => [
  { label: 'Title', value: 'title' },
  { label: 'Description', value: 'description' },
  { label: 'Notes', value: 'notes' },
]);

const searchOn = ref(searchOnOptions.value[0]);

const virtualListRef = ref(null);

const text = ref("");
const searchResults = reactive([]);
const currentSearchResultSelectedIndex = ref(-1);
const virtualListIndex = ref(0);

const showNoSearchResults = ref(false);

const noResultsMessage = "Unfortunately, your search didn't return any results. You might want to modify your filters or search terms";

const boldStringMatch = (str, matchWord) => {
  return str.replace(
    new RegExp(matchWord, "gi"),
    (match) => `<strong>${match}</strong>`
  );
};

const onSearch = (val) => {
  showNoSearchResults.value = false;
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
        notes: searchOn.value.value == "notes" ? val.trim() : null,
      }
    };
    api.document.search(1, 16, params, "lastUpdateTimestamp", "DESC")
      .then((successResponse) => {
        searchResults.length = 0;
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
              createdOn: date.formatDate(document.createdOnTimestamp, 'YYYY-MM-DD HH:mm:ss'),
              lastUpdate: date.formatDate(document.lastUpdateTimestamp, 'YYYY-MM-DD HH:mm:ss'),
              fileCount: document.fileCount,
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
  // this is required here because this modal dialog is persistent (from MainLayout.vue).
  // DOES NOT WORK with onMounted/onBeforeUnmount. WE ONLY WANT CAPTURE KEY EVENTS WHEN DIALOG IS VISIBLE
  window.addEventListener('keydown', onKeyDown);
}

const onClose = () => {
  isVisible.value = false;
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
