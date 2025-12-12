<template>
  <q-page>
    <div>
      <q-markup-table v-if="hasShares">
        <thead>
          <tr>
            <th class="text-left">{{ t('Created on') }}</th>
            <th class="text-left">Last activity on</th>
            <th class="text-left">Expires on</th>
            <th class="text-right">Total clicks</th>
            <th class="text-center">{{ t('Actions') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="share, shareIndex in shares" :key="shareIndex">
            <td class="text-left">{{ share.createdAt.dateTime }} ({{ share.createdAt.timeAgo }})</td>
            <td class="text-left">{{ share.lastActivity.dateTime }} ({{ share.lastActivity.timeAgo }})</td>
            <td class="text-left">{{ t('never') }}</td>
            <td class="text-right">{{ share.totalClicks }}</td>
            <td class="text-center">
              <q-btn-group flat>
                <q-btn outline no-caps size="md" icon="settings" label="settings" />
                <q-btn outline no-caps size="md" icon="delete" label="delete" />
              </q-btn-group>
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
  import { useI18n } from "vue-i18n";
  import { type AjaxState as AjaxStateInterface, defaultAjaxState } from "src/types/ajaxState";
  import { DateTimeClass } from "src/types/dateTime";
  import { bus } from 'src/composables/bus';
  import { currentTimestamp } from "src/composables/dateUtils";
  import { api } from "src/composables/api";
  import { default as CustomBanner } from 'src/components/Banners/CustomBanner.vue';

  const { t } = useI18n();

  const state: AjaxStateInterface = reactive({ ...defaultAjaxState });

  const results = shallowRef([]);

  const shares = [
    {
      createdAt: new DateTimeClass(t, currentTimestamp() - Math.random() * 1000000000),
      lastActivity: new DateTimeClass(t, currentTimestamp() - Math.random() * 1000000000),
      totalClicks: Math.floor(Math.random() * 101),
    },
    {
      createdAt: new DateTimeClass(t, currentTimestamp() - Math.random() * 1000000000),
      lastActivity: new DateTimeClass(t, currentTimestamp() - Math.random() * 1000000000),
      totalClicks: Math.floor(Math.random() * 101),
    },
    {
      createdAt: new DateTimeClass(t, currentTimestamp() - Math.random() * 1000000000),
      lastActivity: new DateTimeClass(t, currentTimestamp() - Math.random() * 1000000000),
      totalClicks: Math.floor(Math.random() * 101),
    },
    {
      createdAt: new DateTimeClass(t, currentTimestamp() - Math.random() * 1000000000),
      lastActivity: new DateTimeClass(t, currentTimestamp() - Math.random() * 1000000000),
      totalClicks: Math.floor(Math.random() * 101),
    }
  ];

  const hasShares = computed(() => shares.length > 0);

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
      .then((successResponse) => {
        if (successResponse.data.results) {
          console.log(successResponse.data.results);
          results.value = [];
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

  onMounted(() => {
    onSubmitForm(true);
  });
</script>