<template>
  <q-page>
    <div>
      <q-markup-table v-if="hasSharedAttachments">
        <thead>
          <tr>
            <th class="text-left">{{ t('Enabled') }}</th>
            <th class="text-left">{{ t('Attachment') }}</th>
            <th class="text-left">{{ t('Document') }}</th>
            <th class="text-left">{{ t('Created on') }}</th>

            <th class="text-left">{{ t('Last access on') }}</th>
            <th class="text-left">{{ t('Expires on') }}</th>
            <th class="text-right">{{ t('Total access') }}</th>
            <th class="text-center">{{ t('Actions') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="attachmentShare in results" :key="attachmentShare.id">
            <td class="text-left"><q-icon class="text-weight-bold" size="xs"
                :name="attachmentShare.enabled ? 'done' : 'block'" :color="attachmentShare.enabled ? 'green' : 'red'" />
            </td>
            <td>
              <a :href="getAttachmentURL(attachmentShare.attachment.id, true)"
                @click.stop.prevent="onDownload(attachmentShare.attachment.id, attachmentShare.attachment.name)">{{
                  attachmentShare.attachment.name
                }}</a>
              ({{ format.humanStorageSize(attachmentShare.attachment.size) }})
            </td>
            <td>
              <router-link :to="{ name: 'document', params: { id: attachmentShare.document.id } }">{{
                attachmentShare.document.title
                }}</router-link>

            </td>
            <td class="text-left">{{ fullDateTimeHuman(attachmentShare.createdAtTimestamp) }} ({{
              t(timeAgo(attachmentShare.createdAtTimestamp).label, {
                count:
                  timeAgo(attachmentShare.createdAtTimestamp).count
              })
            }})
            </td>
            <td class="text-left"><span v-if="attachmentShare.lastAccessTimestamp">{{
              attachmentShare.lastAccessTimestamp }} ({{
                  attachmentShare.lastAccessTimestamp }})</span></td>
            <td class="text-left">{{ attachmentShare.expiresAtTimestamp ?
              fullDateTimeHuman(attachmentShare.expiresAtTimestamp) : '' }}</td>
            <td class="text-right">{{ attachmentShare.accessCount || 0 }}</td>
            <td class="text-center">
              <q-btn flat outline no-caps size="md" icon="settings" :label="t('Settings')"
                @click="onShareClick(attachmentShare.attachment.id)" />
            </td>
          </tr>
        </tbody>
      </q-markup-table>

      <CustomBanner v-else warning text="You haven't created any share yet" />
    </div>
  </q-page>
</template>

<script setup lang="ts">
  import { computed, reactive, shallowRef, onMounted } from 'vue';
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
  import { default as CustomBanner } from 'src/components/Banners/CustomBanner.vue';

  const { t } = useI18n();

  const state: AjaxStateInterface = reactive({ ...defaultAjaxState });

  const results = shallowRef<AttachmentShareInterface[]>([]);

  const hasSharedAttachments = computed(() => results.value.length > 0);

  const onSubmitForm = (resetPager: boolean) => {
    if (resetPager) {
      //store.pager.currentPageIndex = 1;
    }
    Object.assign(state, defaultAjaxState);
    state.ajaxRunning = true;
    api.sharedAttachment.search({
      currentPageIndex: 1,
      resultsPage: 16,
      totalResults: 0,
      totalPages: 0,
    }, {
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
          /*
          store.pager.currentPageIndex = successResponse.data.results.pagination.currentPage;
          store.pager.resultsPage = successResponse.data.results.pagination.resultsPage;
          store.pager.totalResults = successResponse.data.results.pagination.totalResults;
          store.pager.totalPages = successResponse.data.results.pagination.totalPages;
          results.value = successResponse.data.results.documents.map((document: SearchDocumentResponseItemInterface) =>
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
              "",
            )
          );
          searchLaunched.value = true;
          resultsWidgetRef.value?.expand();
          */
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
    onSubmitForm(true);
  });
</script>