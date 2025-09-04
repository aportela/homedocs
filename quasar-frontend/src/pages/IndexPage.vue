<template>
  <q-page>
    <div class="row">
      <div class="col">
        <SystemStats></SystemStats>
      </div>
    </div>
    <div class="row">
      <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 flex">
        <RecentDocuments></RecentDocuments>
      </div>
      <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 flex">
        <TagCloud></TagCloud>
      </div>
    </div>
  </q-page>
</template>

<script setup>

import { default as SystemStats } from "src/components/SystemStats.vue";
import { default as RecentDocuments } from "components/RecentDocuments.vue";
import { default as TagCloud } from "components/TagCloud.vue";

</script>
