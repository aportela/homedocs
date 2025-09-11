<template>
  <q-layout view="lHh lpR lFf">
    <q-header height-hint="61.59" class="q-mb-md q-mx-sm bg-white text-grey-10 q-pa-sm">
      <q-toolbar class="q-py-sm q-px-md my_toolbar bg-grey-2">
        <q-btn flat dense round @click="visibleSidebar = !visibleSidebar" aria-label="Toggle drawer" icon="menu"
          v-show="!visibleSidebar" class="q-mr-md" />
        <q-btn flat dense round @click="miniSidebar = !miniSidebar" aria-label="Toggle drawer"
          :icon="miniSidebar ? 'arrow_forward_ios' : 'arrow_back_ios_new'" class="q-mr-md" v-show="visibleSidebar" />
        <q-input type="text" standout dense :label="t('Search...')" class="force_cursor_pointer full-width"
          @click.prevent="console.log(1)">
          <template v-slot:prepend>
            <q-icon name="search" />
          </template>
        </q-input>
        <!--
        <FastSearchSelector dense class="full-width"></FastSearchSelector>
        -->
        <q-btn-group flat class="q-ml-md">
          <DarkModeButton dense />
          <SwitchLanguageButton :short-labels="true" style="min-width: 9em" />
          <GitHubButton dense :href="GITHUB_PROJECT_URL" />
        </q-btn-group>
      </q-toolbar>
    </q-header>
    <SidebarDrawer v-model="visibleSidebar" :mini="miniSidebar"></SidebarDrawer>
    <q-page-container>
      <router-view class="q-pa-sm" />
    </q-page-container>
  </q-layout>
</template>

<script setup>
import { ref } from "vue";
import { useQuasar } from "quasar";
import { useSessionStore } from "stores/session";
import { useI18n } from "vue-i18n";

import { default as SidebarDrawer } from "components/SidebarDrawer.vue"
//import { default as FastSearchSelector } from "components/FastSearchSelector.vue"
import { default as DarkModeButton } from "components/DarkModeButton.vue"
import { default as SwitchLanguageButton } from "components/SwitchLanguageButton.vue"
import { default as GitHubButton } from "components/GitHubButton.vue"
import { GITHUB_PROJECT_URL } from "src/constants"


const $q = useQuasar();
const { t } = useI18n();
const session = useSessionStore();
if (!session.isLoaded) {
  session.load();
}

const miniSidebar = ref($q.screen.gt.lg);

const visibleSidebar = ref($q.screen.gt.lg);
</script>

<style scoped>
.my_toolbar {
  border: 1px solid rgba(0, 0, 0, 0.12);
  border-radius: 4px;
}

.force_cursor_pointer * {
  cursor: pointer !important;
}
</style>