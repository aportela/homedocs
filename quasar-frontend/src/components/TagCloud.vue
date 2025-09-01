<template>
  <div class="fit">
    <q-card class="my-card q-ma-xs" flat bordered>
      <q-card-section>
        <q-expansion-item :header-class="loadingError ? 'bg-red' : ''" expand-separator
          :icon="loadingError ? 'error' : 'bookmark'" :label="t('Tag cloud')"
          :caption="t(loadingError ? 'Error loading data' : 'Click on tag to browse by tag')" :model-value="expanded">
          <p class="text-center" v-if="loading">
            <q-spinner-pie v-if="loading" color="grey-5" size="md" />
          </p>
          <div v-else>
            <div v-if="hasTags">
              <q-chip square outline text-color="dark" v-for="tag in tags" :key="tag.tag"
                :title="t('Click here to browse documents containing this tag')">
                <q-avatar color="grey-9" text-color="white">{{ tag.total }}</q-avatar>
                <router-link :to="{ name: 'advancedSearchByTag', params: { tag: tag.tag } }"
                  style="text-decoration: none">
                  {{ tag.tag }}</router-link>
              </q-chip>
            </div>
            <q-banner v-else-if="!loadingError"><q-icon name="info" size="md" class="q-mr-sm" />
              {{ t("You haven't created any tags yet") }}
            </q-banner>
          </div>
        </q-expansion-item>
      </q-card-section>
    </q-card>
  </div>
</template>

<script setup>

import { ref } from "vue";
import { useI18n } from "vue-i18n";
import { useQuasar } from "quasar"
import { api } from "boot/axios";

const { t } = useI18n();
const $q = useQuasar();
const loadingError = ref(false);
const loading = ref(false);

let expanded = !$q.screen.lt.md;
let tags = [];
let hasTags = false;

function refresh() {
  tags = [];
  hasTags = false;
  loading.value = true;
  loadingError.value = false;
  api.tag.getCloud()
    .then((success) => {
      tags = success.data.tags;
      hasTags = tags.length > 0;
      loading.value = false;
    })
    .catch((error) => {
      loading.value = false;
      loadingError.value = true;
      $q.notify({
        type: "negative",
        message: t("API Error: fatal error"),
        caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
      });
    });
}

refresh();
</script>
