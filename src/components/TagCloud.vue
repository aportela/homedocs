<template>
  <q-card class="my-card fit" flat bordered>
    <q-card-section>
      <q-expansion-item :header-class="loadingError ? 'bg-red' : ''" expand-separator
        :icon="loadingError ? 'error' : 'bookmark'" :label="t('Tag cloud')"
        :caption="t(loadingError ? 'Error loading data' : 'Click on tag to browse by tag')" :model-value="expanded">
        <div v-if="!loading">
          <q-chip square outline text-color="dark" v-for=" tag  in  tags " :key="tag"
            :title="t('Click here to browse documents containing this tag')">
            <q-avatar color="grey-9" text-color="white">{{ tag.total }}</q-avatar>
            <router-link :to="{ name: 'advancedSearchByTag', params: { tag: tag.tag } }" style="text-decoration: none"
              class="text-dark">
              {{ tag.tag }}</router-link>
          </q-chip>
        </div>
        <p class="text-center" v-else>
          <q-spinner-pie color="grey-5" size="md" />
        </p>
      </q-expansion-item>
    </q-card-section>
  </q-card>
</template>

<script setup>

import { ref } from "vue";
import { useI18n } from 'vue-i18n'
import { useQuasar } from 'quasar'
import { api } from 'boot/axios'

const { t } = useI18n();
const $q = useQuasar();
const loadingError = ref(false);
const loading = ref(false);
const expanded = ref(!$q.screen.lt.md);
const tags = ref([]);

function refresh() {
  tags.value = [];
  loading.value = true;
  loadingError.value = false;
  api.tag.getCloud()
    .then((success) => {
      tags.value = success.data.tags;
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
