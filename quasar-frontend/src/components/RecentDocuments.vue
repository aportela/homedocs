<template>
  <CustomExpansionWidget title="Most recent activity"
    :caption="state.loading ? 'Loading...' : 'Click on title to open document'" icon="work_history"
    iconToolTip="Click to refresh data" :onHeaderIconClick="onRefresh" :loading="state.loading"
    :error="state.loadingError" :expanded="expanded">
    <template v-slot:header-extra>
      <q-chip square size="sm" color="primary" text-color="white">{{ t("Total document count", {
        count:
          recentDocuments.length
      }) }}</q-chip>
    </template>
    <template v-slot:content>
      <q-list v-if="state.loading">
        <div v-for="i in 4" :key="i">
          <q-item class="transparent-background text-color-primary">
            <q-item-section top avatar class="gt-xs q-mt-lg">
              <q-skeleton type="QAvatar" square size="32px"></q-skeleton>
            </q-item-section>
            <q-item-section class="q-mx-md">
              <q-item-label>
                <q-skeleton type="text" />
              </q-item-label>
              <q-item-label caption lines="2">
                <q-skeleton type="text" height="2em" />
              </q-item-label>
              <q-item-label>
                <div class="row items-left q-gutter-sm">
                  <q-skeleton square width="6em" height="2em" class="" v-for="j in 5" :key="j"></q-skeleton>
                </div>
              </q-item-label>
            </q-item-section>
            <q-item-section side top>
              <q-item-label caption>
                <q-skeleton type="text" width="8em" height="2em"></q-skeleton>
                <q-skeleton square width="8em" height="2em" class="q-mt-sm"></q-skeleton>
                <q-skeleton square width="8em" height="2em" class="q-mt-sm"></q-skeleton>
              </q-item-label>
            </q-item-section>
          </q-item>
          <q-separator inset v-if="i < 3" class="q-my-md" />
        </div>
      </q-list>
      <CustomErrorBanner v-else-if="state.loadingError" text="Error loading data" :apiError="state.apiError">
      </CustomErrorBanner>
      <q-list v-else-if="hasRecentDocuments">
        <div v-for="recentDocument, index in recentDocuments" :key="recentDocument.id">
          <q-item class="transparent-background text-color-primary">
            <q-item-section top avatar class="gt-xs">
              <q-avatar square icon="work" size="64px" aria-label="Document avatar" />
            </q-item-section>
            <q-item-section top>
              <q-item-label>
                <router-link :to="{ name: 'document', params: { id: recentDocument.id } }"
                  class="text-decoration-hover text-color-primary"><span class="text-weight-bold">{{ t("Title")
                    }}:</span> {{
                      recentDocument.title
                    }}
                </router-link>
              </q-item-label>
              <q-item-label caption lines="2">{{ recentDocument.description }}</q-item-label>
              <q-item-label>
                <router-link v-for="tag in recentDocument.tags" :key="tag"
                  :to="{ name: 'advancedSearchByTag', params: { tag: tag } }">
                  <q-chip square size="md" clickable icon="tag" class="q-chip-themed">
                    {{ tag }}
                    <q-tooltip>{{ t("Browse by tag: ", { tag: tag }) }}</q-tooltip>
                  </q-chip>
                </router-link>
              </q-item-label>
            </q-item-section>
            <q-item-section side top>
              <q-item-label caption>{{ timeAgo(recentDocument.timestamp) }}</q-item-label>
              <q-chip size="md" square class="full-width theme-default-q-chip">
                <q-avatar class="theme-default-q-avatar">{{ recentDocument.fileCount }}</q-avatar>
                {{ t("Files") }}
              </q-chip>
              <q-chip size="md" square class="full-width theme-default-q-chip">
                <q-avatar class="theme-default-q-avatar">{{ recentDocument.noteCount }}</q-avatar>
                {{ t("Total notes", { count: recentDocument.noteCount }) }}
              </q-chip>
            </q-item-section>
          </q-item>
          <q-separator inset v-if="index !== recentDocuments.length - 1" class="q-my-md" />
        </div>
      </q-list>
      <CustomBanner v-else warning text="You haven't created any documents yet"></CustomBanner>
    </template>
  </CustomExpansionWidget>
</template>

<script setup>

import { reactive, computed, onMounted } from "vue";
import { useI18n } from "vue-i18n";
import { api } from "boot/axios";
import { useFormatDates } from "src/composables/formatDate"

import { default as CustomExpansionWidget } from "components/CustomExpansionWidget.vue";
import { default as CustomErrorBanner } from "components/CustomErrorBanner.vue";
import { default as CustomBanner } from "components/CustomBanner.vue";

const { t } = useI18n();
const { timeAgo } = useFormatDates();

const props = defineProps({
  expanded: {
    type: Boolean,
    required: false,
    default: true
  }
});

const state = reactive({
  loading: false,
  loadingError: false,
  apiError: null
});

const recentDocuments = reactive([]);
const hasRecentDocuments = computed(() => recentDocuments.length > 0);

function onRefresh() {
  state.loading = true;
  state.loadingError = false;
  state.apiError = null;
  api.document.searchRecent(16)
    .then((successResponse) => {
      recentDocuments.length = 0;
      recentDocuments.push(...successResponse.data.recentDocuments.map((document) => {
        document.timestamp = document.lastUpdateTimestamp * 1000; // convert PHP timestamps (seconds) to JS (milliseconds)
        return document;
      }));
      state.loading = false;
    })
    .catch((errorResponse) => {
      state.loadingError = true;
      state.apiError = errorResponse.customAPIErrorDetails;
      state.loading = false;
    });
}

onMounted(() => {
  onRefresh();
});

</script>