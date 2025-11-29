<template>
  <CustomExpansionWidget title="Most recent activity" icon="work_history" icon-tool-tip="Click to refresh data"
    :on-header-icon-click="onRefresh" :loading="state.ajaxRunning" :error="state.ajaxErrors" :expanded="isExpanded"
    @expand="isExpanded = true" @collapse="isExpanded = false">
    <template v-slot:header-extra>
      <q-chip square size="sm" color="grey-7" text-color="white">{{ t("Total document count", {
        count:
          recentDocuments.length
      }) }}</q-chip>
    </template>
    <template v-slot:content>
      <q-list v-if="state.ajaxRunning">
        <div v-for="i in 4" :key="i" class="border-bottom-except-last-item">
          <q-item class="transparent-background text-color-primary q-pa-sm">
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
        </div>
      </q-list>
      <CustomErrorBanner v-else-if="state.ajaxErrors" :text="state.ajaxErrorMessage || 'Error loading data'"
        :api-error="state.ajaxAPIErrorDetails">
      </CustomErrorBanner>
      <q-list v-else-if="hasRecentDocuments">
        <div v-for="recentDocument in recentDocuments" :key="recentDocument.id" class="border-bottom-except-last-item">
          <q-item class="transparent-background text-color-primary q-pa-sm " clickable
            :to="{ name: 'document', params: { id: recentDocument.id } }">
            <q-item-section top avatar class="gt-xs">
              <q-avatar square icon="work" size="64px" aria-label="Document avatar" />
            </q-item-section>
            <q-item-section top>
              <q-item-label>
                <span class="text-weight-bold">{{ t("Title") }}:</span> {{ recentDocument.title }}
              </q-item-label>
              <q-item-label caption lines="2" v-if="recentDocument.description">
                <span class="text-weight-bold">{{ t("Description") }}:</span> {{ recentDocument.description
                }}</q-item-label>
              <q-item-label v-if="recentDocument.tags?.length > 0">
                <BrowseByTagButton v-for="tag in recentDocument.tags" :key="tag" :tag="tag" icon="tag" />
              </q-item-label>
            </q-item-section>
            <q-item-section side top>
              <q-item-label caption>{{ recentDocument.updatedAt?.timeAgo }}</q-item-label>
              <ViewDocumentDetailsButton size="md" square class="min-width-9em" :count="recentDocument.attachmentCount"
                :label="'Total attachments count'" :tool-tip="'View document attachments'" :disable="state.ajaxRunning"
                @click.stop.prevent="onShowDocumentFiles(recentDocument.id, recentDocument.title)">
              </ViewDocumentDetailsButton>
              <ViewDocumentDetailsButton size="md" square class="min-width-9em" :count="recentDocument.noteCount"
                :label="'Total notes'" :tool-tip="'View document notes'" :disable="state.ajaxRunning"
                @click.stop.prevent="onShowDocumentNotes(recentDocument.id, recentDocument.title)">
              </ViewDocumentDetailsButton>
            </q-item-section>
          </q-item>
        </div>
      </q-list>
      <CustomBanner v-else warning text="You haven't created any documents yet"></CustomBanner>
    </template>
  </CustomExpansionWidget>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted, onBeforeUnmount } from "vue";
import { useI18n } from "vue-i18n";

import { bus, onShowDocumentFiles, onShowDocumentNotes } from "src/composables/bus";
import { api } from "src/composables/api";
import { type AjaxState as AjaxStateInterface, defaultAjaxState } from "src/types/ajax-state";
import { type RecentDocumentsResponse, type RecentDocumentResponseItem } from "src/types/api-responses";
import { type RecentDocumentItem, RecentDocumentItemClass } from "src/types/recent-document-item";
import { DateTimeClass } from "src/types/date-time";

import { default as CustomExpansionWidget } from "src/components/Widgets/CustomExpansionWidget.vue";
import { default as CustomErrorBanner } from "src/components/Banners/CustomErrorBanner.vue";
import { default as CustomBanner } from "src/components/Banners/CustomBanner.vue";
import { default as BrowseByTagButton } from "src/components/Buttons/BrowseByTagButton.vue";
import { default as ViewDocumentDetailsButton } from "src/components/Buttons/ViewDocumentDetailsButton.vue";

const { t } = useI18n();

interface RecentDocumentsWidgetProps {
  expanded?: boolean
};

const props = withDefaults(defineProps<RecentDocumentsWidgetProps>(), {
  expanded: true
});

const isExpanded = ref(props.expanded);

const state: AjaxStateInterface = reactive({ ...defaultAjaxState });

const recentDocuments = reactive<Array<RecentDocumentItem>>([]);

const hasRecentDocuments = computed(() => recentDocuments.length > 0);

const onRefresh = () => {
  if (!state.ajaxRunning) {
    Object.assign(state, defaultAjaxState);
    state.ajaxRunning = true;
    api.document.searchRecent(16)
      .then((successResponse: RecentDocumentsResponse) => {
        recentDocuments.length = 0;
        recentDocuments.push(...successResponse.data.documents.map((document: RecentDocumentResponseItem) =>
          new RecentDocumentItemClass(
            document.id,
            new DateTimeClass(t, document.updatedAtTimestamp),
            document.title,
            document.description,
            document.tags,
            document.attachmentCount,
            document.noteCount,
          )
        ));
      })
      .catch((errorResponse) => {
        state.ajaxErrors = true;
        if (errorResponse.isAPIError) {
          state.ajaxAPIErrorDetails = errorResponse.customAPIErrorDetails;
          switch (errorResponse.response.status) {
            case 401:
              state.ajaxErrorMessage = "Auth session expired, requesting new...";
              bus.emit("reAuthRequired", { emitter: "RecentDocumentsWidget" });
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
  }
};

onMounted(() => {
  onRefresh();
  bus.on("reAuthSucess", (msg) => {
    if (msg.to?.includes("RecentDocumentsWidget")) {
      onRefresh();
    }
  });
});

onBeforeUnmount(() => {
  bus.off("reAuthSucess");
});
</script>