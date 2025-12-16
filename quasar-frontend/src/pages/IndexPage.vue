<template>
  <q-page>
    <div class="row q-col-gutter-sm">
      <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6">
        <RecentDocuments :expanded="expanded" />
      </div>
      <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6">
        <TagCloudWidget :expanded="expanded" />
      </div>
    </div>
  </q-page>
</template>

<script setup lang="ts">
  import { ref } from "vue";
  import { useQuasar } from "quasar";

  import { default as RecentDocuments } from "src/components/Widgets/RecentDocumentsWidget.vue";
  import { default as TagCloudWidget } from "src/components/Widgets/TagCloudWidget.vue";

  const $q = useQuasar();

  const expanded = ref(!$q.screen.lt.md);

</script>