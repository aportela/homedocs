<template>
  <div class="fit">
    <q-expansion-item :header-class="{ 'bg-red': loadingError }" expand-separator :icon="loadingError ? 'error' : 'tag'"
      :label="t('Tag cloud')" :caption="t(loadingError ? 'Error loading data' : 'Click on tag to browse by tag')"
      :model-value="expanded" class="rounded-borders q-expansion-item-themed">
      <q-card class="q-ma-xs transparent-background" flat>
        <q-card-section class="q-pa-none">
          <p class="text-center" v-if="loading">
            <q-spinner-pie color="grey-5" size="md" />
          </p>
          <div v-else>
            <div v-if="hasTags">
              <q-chip square class="theme-default-q-chip" v-for="tag in tags" :key="tag.tag">
                <q-avatar class="theme-default-q-avatar">{{ tag.total }}</q-avatar>
                <router-link :to="{ name: 'advancedSearchByTag', params: { tag: tag.tag } }"
                  style="text-decoration: none; width: 10em; text-align: center">
                  <div class="ellipsis">
                    {{ tag.tag }}
                    <q-tooltip>{{ t("Browse by tag: ", { tag: tag.tag }) }}</q-tooltip>
                  </div>
                </router-link>
              </q-chip>
            </div>
            <q-banner v-else-if="!loadingError"><q-icon name="info" size="md" class="q-mr-sm" />
              {{ t("You haven't created any tags yet") }}
            </q-banner>
          </div>
        </q-card-section>
      </q-card>
    </q-expansion-item>
  </div>
</template>

<script setup>

import { ref, computed, onMounted } from "vue";
import { useI18n } from "vue-i18n";
import { useQuasar } from "quasar"
import { api } from "boot/axios";

const { t } = useI18n();
const $q = useQuasar();
const loadingError = ref(false);
const loading = ref(false);

const expanded = ref(!$q.screen.lt.md);
const tags = ref([]);
const hasTags = computed(() => tags.value.length > 0);

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
      const status = error.response?.status || 'N/A';
      const statusText = error.response?.statusText || 'Unknown error';
      $q.notify({
        type: "negative",
        message: t("API Error: fatal error"),
        caption: t("API Error: fatal error details", { status, statusText })
      });
    });
}

onMounted(() => {
  refresh();
});

</script>