<template>
  <div class="fit">
    <q-card class="q-ma-xs" flat bordered>
      <q-card-section>
        <q-expansion-item :header-class="loadingError ? 'bg-red' : ''" expand-separator
          :icon="loadingError ? 'error' : 'tag'" :label="t('Tag cloud')"
          :caption="t(loadingError ? 'Error loading data' : 'Click on tag to browse by tag')" :model-value="expanded">
          <p class="text-center" v-if="loading">
            <q-spinner-pie color="grey-5" size="md" />
          </p>
          <div v-else>
            <div v-if="hasTags">
              <q-chip square text-color="dark" v-for="tag in tags" :key="tag.tag"
                :title="t('Click here to browse documents containing this tag')">
                <q-avatar color="grey-9" text-color="white">{{ tag.total }}</q-avatar>
                <router-link :to="{ name: 'advancedSearchByTag', params: { tag: tag.tag } }"
                  style="text-decoration: none; width: 10em; text-align: center" class="text-dark ">
                  <div class="ellipsis">
                    {{ tag.tag }}
                    <q-tooltip>{{ tag.tag }}</q-tooltip>
                  </div>
                </router-link>
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