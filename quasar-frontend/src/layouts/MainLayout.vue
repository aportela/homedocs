<template>
  <q-layout view="lHh lpR lFf">
    <q-header elevated height-hint="61.59">
      <q-toolbar class="q-py-sm q-px-md">
        <q-btn flat dense round @click="visibleSidebar = !visibleSidebar" aria-label="Toggle drawer" icon="menu"
          class="q-mr-md" />
        <FastSearchSelector dense class="full-width"></FastSearchSelector>
        <q-btn-group flat class="q-ml-md">
          <DarkModeButton dense />
          <SwitchLanguageButton :short-labels="true" style="min-width: 9em" />
          <GitHubButton dense :href="GITHUB_PROJECT_URL" />
        </q-btn-group>
      </q-toolbar>
    </q-header>
    <SidebarDrawer v-model="visibleSidebar"></SidebarDrawer>
    <q-page-container>
      <router-view class="q-pa-sm" />
    </q-page-container>
  </q-layout>
</template>

<script setup>
import { ref } from "vue";
import { useQuasar } from "quasar";
import { useSessionStore } from "stores/session";

import { default as SidebarDrawer } from "components/SidebarDrawer.vue"
import { default as FastSearchSelector } from "components/FastSearchSelector.vue"
import { default as DarkModeButton } from "components/DarkModeButton.vue"
import { default as SwitchLanguageButton } from "components/SwitchLanguageButton.vue"
import { default as GitHubButton } from "components/GitHubButton.vue"
import { GITHUB_PROJECT_URL } from "src/constants"

const $q = useQuasar();
const session = useSessionStore();
if (!session.isLoaded) {
  session.load();
}
const visibleSidebar = ref($q.screen.gt.lg);

</script>