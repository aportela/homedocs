<template>
  <q-card class="my-card fit" flat bordered>
    <q-card-section>
      <q-expansion-item expand-separator icon="bookmark" label="Tag cloud" caption="Click on tag to browse by tag"
        :model-value="!$q.screen.lt.md">
        <div v-if="!loading">
          <q-chip square outline text-color="dark" v-for="tag in tags" :key="tag"
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
import { api } from 'boot/axios'
import { useQuasar } from 'quasar'
import { useI18n } from 'vue-i18n'

const $q = useQuasar();
const { t } = useI18n();
const loading = ref(false);
const tags = ref([]);

function onRefreshTagCloud() {
  loading.value = true;
  api.tag.getCloud()
    .then((success) => {
      tags.value = success.data.tags;
      loading.value = false;
    })
    .catch((error) => {
      switch (error.response.status) {
        // TODO
      }
      loading.value = false;
      $q.notify({
        type: "negative",
        message: t("API Error: fatal error"),
      });
    });
}

onRefreshTagCloud();
</script>
