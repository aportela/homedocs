<template>
  <q-page>
    <div class="row q-col-gutter-sm">
      <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6">
        <RecentDocuments :expanded="expanded"></RecentDocuments>
      </div>
      <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6">
        <TagCloud :expanded="expanded"></TagCloud>
      </div>
    </div>
  </q-page>
</template>

<script setup>
import { ref } from "vue";
import { useQuasar } from "quasar";

import { default as RecentDocuments } from "components/RecentDocuments.vue";
import { default as TagCloud } from "components/TagCloud.vue";

const $q = useQuasar();

const expanded = ref(!$q.screen.lt.md);

</script>