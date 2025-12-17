<template>
  <q-page>
    <CustomErrorBanner v-if="showErrorBanner" :text="state.ajaxErrorMessage || 'Error loading data'"
      :api-error="state.ajaxAPIErrorDetails" class="q-ma-md">
    </CustomErrorBanner>
    <CustomBanner v-else-if="showNoResultsBanner" warning text="You haven't created any share yet" class="q-ma-md">
    </CustomBanner>
    <div v-else>
      <div class="q-ma-md flex flex-center" v-if="pager.totalPages > 1">
        <q-pagination v-model="pager.currentPageIndex" color="dark" :max="pager.totalPages" :max-pages="5"
          boundary-numbers direction-links boundary-links @update:model-value="onPaginationChanged"
          :disable="state.ajaxRunning" class="theme-default-q-pagination" />
      </div>
      <q-markup-table>
        <thead>
          <tr>
            <th v-for="(column, index) in columns" :key="index"
              :class="['text-left', column.defaultClass, { 'cursor-not-allowed': state.ajaxRunning, 'cursor-pointer': !state.ajaxRunning, 'action-primary': sort.field === column.field }]"
              @click="onToggleSort(column.field)">
              <q-icon :name="sort.field === column.field ? sortOrderIcon : 'sort'" size="sm"></q-icon>
              {{ t(column.title) }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="attachmentShare in results" :key="attachmentShare.id" class="cursor-pointer"
            @click="onShareClick(attachmentShare.attachment.id)">
            <td class="text-left">
              <p class="q-my-sm">{{ fullDateTimeHuman(attachmentShare.createdAtTimestamp) }}</p>
              <p class="q-my-sm">({{
                t(timeAgo(attachmentShare.createdAtTimestamp).label, {
                  count:
                    timeAgo(attachmentShare.createdAtTimestamp).count
                })
              }})
              </p>
            </td>
            <td class="text-left"><q-icon class="text-weight-bold" size="sm"
                :name="attachmentShare.enabled ? 'done' : 'block'" />
            </td>
            <td class="text-left">
              <p class="q-my-sm"><q-icon name="file_download" class="q-mr-sm" />{{ attachmentShare.attachment.name }}
                ({{ format.humanStorageSize(attachmentShare.attachment.size) }})</p>
              <p class="q-my-sm"><q-icon name="work" class="q-mr-sm" />{{ attachmentShare.document.title }}</p>
            </td>
            <td class="text-left">
              <div v-if="attachmentShare.expiresAtTimestamp">
                <p class="q-my-sm">{{ fullDateTimeHuman(attachmentShare.expiresAtTimestamp) }}</p>
                <p class="q-my-sm" v-if="attachmentShare.expiresAtTimestamp > currentTimestamp()">({{
                  t(timeUntil(attachmentShare.expiresAtTimestamp).label, {
                    count:
                      timeUntil(attachmentShare.expiresAtTimestamp).count
                  })
                }})
                </p>
                <p class="q-my-sm" v-else>
                  {{ t('(expired)') }}
                </p>
              </div>
            </td>
            <td class="text-right">{{ attachmentShare.accessCount || 0 }}<span v-if="attachmentShare.accessLimit">/{{
              attachmentShare.accessLimit }}</span></td>
            <td class="text-left">
              <div v-if="attachmentShare.lastAccessTimestamp">
                <p class="q-my-sm">{{ fullDateTimeHuman(attachmentShare.lastAccessTimestamp) }}</p>
                <p class="q-my-sm">({{ t(timeAgo(attachmentShare.lastAccessTimestamp).label, {
                  count:
                    timeAgo(attachmentShare.lastAccessTimestamp).count
                }) }})</p>
              </div>
            </td>
          </tr>
        </tbody>
      </q-markup-table>
      <div class="q-ma-md flex flex-center" v-if="pager.totalPages > 1">
        <q-pagination v-model="pager.currentPageIndex" color="dark" :max="pager.totalPages" :max-pages="5"
          boundary-numbers direction-links boundary-links @update:model-value="onPaginationChanged"
          :disable="state.ajaxRunning" class="theme-default-q-pagination" />
      </div>
    </div>
  </q-page>
</template>

<script setup lang="ts">
  import { ref, computed, reactive, shallowRef, onMounted, onBeforeUnmount } from 'vue';
  import { format } from 'quasar';
  import { useI18n } from "vue-i18n";
  import { type AjaxState as AjaxStateInterface, defaultAjaxState } from "src/types/ajaxState";
  import { type SearchAttachmentShareResponse as SearchAttachmentShareResponseInterface } from 'src/types/apiResponses';
  import { type AttachmentShare as AttachmentShareInterface } from 'src/types/attachmentShare';
  import { bus } from 'src/composables/bus';
  import { fullDateTimeHuman, timeAgo, timeUntil, currentTimestamp } from "src/composables/dateUtils";
  import { api } from "src/composables/api";
  import { PagerClass } from 'src/types/pager';
  import { type OrderType } from "src/types/orderType";
  import { SortClass } from "src/types/sort";
  import { default as CustomErrorBanner } from "src/components/Banners/CustomErrorBanner.vue";
  import { default as CustomBanner } from "src/components/Banners/CustomBanner.vue";


  const { t } = useI18n();


  const columns = [
    { field: 'createdAtTimestamp', title: 'Created on', defaultClass: "" },
    { field: 'enabled', title: 'Enabled', defaultClass: "" },
    { field: 'documentTitle', title: 'Shared element', defaultClass: "" },
    { field: 'expiresAtTimestamp', title: 'Expires on', defaultClass: "" },
    { field: 'accessCount', title: 'Access count', defaultClass: "text-right" },
    { field: 'lastAccessTimestamp', title: 'Most recent access', defaultClass: "" }
  ];


  const sort = reactive<SortClass>(new SortClass("createdAtTimestamp", "createdAtTimestamp", "DESC"));

  const sortOrderIcon = computed(() => sort.order === "ASC" ? "keyboard_double_arrow_up" : "keyboard_double_arrow_down");

  const state: AjaxStateInterface = reactive({ ...defaultAjaxState });

  const results = shallowRef<AttachmentShareInterface[]>([]);

  const searchLaunched = ref<boolean>(false);

  const hasResults = computed(() => results.value.length > 0);

  const showErrorBanner = computed(() => !state.ajaxRunning && state.ajaxErrors);
  const showNoResultsBanner = computed(() => !state.ajaxRunning && searchLaunched.value && !hasResults.value);

  const pager = reactive<PagerClass>(new PagerClass(1, 16, 0, 0));

  const skipCount = ref<boolean>(false);

  const onPaginationChanged = (pageIndex: number) => {
    pager.currentPageIndex = pageIndex;
    onSubmitForm(false);
  }

  const onToggleSort = (field: string, order?: OrderType) => {
    if (!state.ajaxRunning) {
      sort.toggle(field, order);
      onSubmitForm(false);
    }
  }

  const onSubmitForm = (resetPager: boolean) => {
    if (resetPager) {
      pager.currentPageIndex = 1;
    }
    Object.assign(state, defaultAjaxState);
    state.ajaxRunning = true;
    api.attachmentShare.search(pager, sort, skipCount.value)
      .then((successResponse: SearchAttachmentShareResponseInterface) => {
        if (successResponse.data.sharedAttachments) {
          results.value = [];
          results.value = successResponse.data.sharedAttachments.map((result) => {
            return (result);
          });
          if (successResponse.data.pager) {
            pager.currentPageIndex = successResponse.data.pager?.currentPageIndex;
            pager.resultsPage = successResponse.data.pager?.resultsPage;
            pager.totalResults = successResponse.data.pager?.totalResults;
            pager.totalPages = successResponse.data.pager?.totalPages;
            skipCount.value = true;
          }
          searchLaunched.value = true;
        }
      })
      .catch((errorResponse) => {
        state.ajaxErrors = true;
        if (errorResponse.isAPIError) {
          state.ajaxAPIErrorDetails = errorResponse.customAPIErrorDetails;
          switch (errorResponse.response.status) {
            case 400:
              state.ajaxErrorMessage = "API Error: invalid/missing param";
              break;
            case 401:
              state.ajaxErrors = false;
              bus.emit("reAuthRequired", { emitter: "AdvancedSearchPage.onSubmitForm" });
              break;
            default:
              state.ajaxErrorMessage = "API Error: fatal error";
              break;
          }
        } else {
          state.ajaxErrorMessage = `Uncaught exception: ${errorResponse}`;
          console.error(errorResponse);
        }
        //searchLaunched.value = true;
      }).finally(() => {
        state.ajaxRunning = false;
      });
  }

  const onShareClick = (attachmentId: string) => {
    bus.emit('showAttachmentShareDialog', { attachmentId: attachmentId, create: false });
  };


  onMounted(() => {
    bus.on("attachmentShareAdded", () => {
      onSubmitForm(true);
    });

    bus.on("attachmentShareChanged", () => {
      onSubmitForm(false);
    });

    bus.on("attachmentShareDeleted", () => {
      onSubmitForm(false);
    });

    onSubmitForm(true);
  });

  onBeforeUnmount(() => {
    bus.off("reAuthSucess");
    bus.off("attachmentShareAdded");
    bus.off("attachmentShareChanged");
    bus.off("attachmentShareDeleted");
  });
</script>
