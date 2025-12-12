<template>
  <q-page>
    <div v-if="hasSharedAttachments">
      <div class="q-ma-md flex flex-center" v-if="pager.totalPages > 1">
        <q-pagination v-model="pager.currentPageIndex" color="dark" :max="pager.totalPages" :max-pages="5"
          boundary-numbers direction-links boundary-links @update:model-value="onPaginationChanged"
          :disable="state.ajaxRunning" class="theme-default-q-pagination" />
      </div>
      <q-markup-table>
        <thead>
          <tr>
            <th class="text-left">{{ t('Created on') }}</th>
            <th class="text-left">{{ t('Enabled') }}</th>
            <th class="text-left">{{ t('Element') }}</th>
            <th class="text-left">{{ t('Expires on') }}</th>
            <th class="text-right">{{ t('Total access') }}</th>
            <th class="text-left">{{ t('Last access on') }}</th>
            <th class="text-center">{{ t('Actions') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="attachmentShare in results" :key="attachmentShare.id">
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
            <td>
              <p class="q-my-sm"><q-icon name="file_download" class="q-mr-sm" />
                <a :href="getAttachmentURL(attachmentShare.attachment.id, true)"
                  @click.stop.prevent="onDownload(attachmentShare.attachment.id, attachmentShare.attachment.name)">{{
                    attachmentShare.attachment.name
                  }}</a>
                ({{ format.humanStorageSize(attachmentShare.attachment.size) }})
              </p>
              <p class="q-my-sm"><q-icon name="work" class="q-mr-sm" />
                <router-link :to="{ name: 'document', params: { id: attachmentShare.document.id } }">{{
                  attachmentShare.document.title
                  }}</router-link>
              </p>
            </td>

            <td class="text-left">{{ attachmentShare.expiresAtTimestamp ?
              fullDateTimeHuman(attachmentShare.expiresAtTimestamp) : '' }}</td>
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
            <td class="text-center">
              <q-btn flat outline no-caps size="md" icon="settings" :label="t('Settings')"
                @click="onShareClick(attachmentShare.attachment.id)" />
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
    <CustomBanner v-else warning text="You haven't created any share yet" />
  </q-page>
</template>

<script setup lang="ts">
  import { computed, reactive, shallowRef, onMounted, onBeforeUnmount } from 'vue';
  import { format } from 'quasar';
  import { useI18n } from "vue-i18n";
  import { type AjaxState as AjaxStateInterface, defaultAjaxState } from "src/types/ajaxState";
  import { type SearchAttachmentShareResponse as SearchAttachmentShareResponseInterface } from 'src/types/apiResponses';
  import { type AttachmentShare as AttachmentShareInterface } from 'src/types/attachmentShare';
  import { bgDownload } from 'src/composables/axios';
  import { bus } from 'src/composables/bus';
  import { fullDateTimeHuman, timeAgo } from "src/composables/dateUtils";
  import { api } from "src/composables/api";
  import { getURL as getAttachmentURL } from 'src/composables/attachment';
  //import { type SortClass as SortClassInterface, SortClass } from 'src/types/sort';
  import { PagerClass } from 'src/types/pager';

  import { default as CustomBanner } from 'src/components/Banners/CustomBanner.vue';

  const { t } = useI18n();

  const state: AjaxStateInterface = reactive({ ...defaultAjaxState });

  const results = shallowRef<AttachmentShareInterface[]>([]);

  const hasSharedAttachments = computed(() => results.value.length > 0);

  const pager = new PagerClass(1, 16, 0, 0);

  const onPaginationChanged = (pageIndex: number) => {
    pager.currentPageIndex = pageIndex;
    onSubmitForm(false);
  }

  const onSubmitForm = (resetPager: boolean) => {
    if (resetPager) {
      pager.currentPageIndex = 1;
    }
    Object.assign(state, defaultAjaxState);
    state.ajaxRunning = true;
    api.sharedAttachment.search(pager, {
      field: "createdAtTimestamp",
      label: "",
      order: "DESC"
    })
      .then((successResponse: SearchAttachmentShareResponseInterface) => {
        if (successResponse.data.results) {
          console.log(successResponse.data.results);
          results.value = [];
          results.value = successResponse.data.results.sharedAttachments.map((result) => {
            return (result);
          });
          pager.currentPageIndex = successResponse.data.results.pagination.currentPage;
          pager.resultsPage = successResponse.data.results.pagination.resultsPage;
          pager.totalResults = successResponse.data.results.pagination.totalResults;
          pager.totalPages = successResponse.data.results.pagination.totalPages;
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
    bus.emit('showSharePreviewDialog', { attachmentId: attachmentId, create: false });
  };

  const onDownload = (attachmentId: string, fileName: string) => {
    bgDownload(getAttachmentURL(attachmentId), fileName)
      .then(() => {
        // TODO:
      })
      .catch(() => {
        // TODO:
      });
  }


  onMounted(() => {
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
    bus.off("attachmentShareChanged");
    bus.off("attachmentShareDeleted");
  });
</script>