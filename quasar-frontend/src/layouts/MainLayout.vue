<template>
  <q-layout view="lHh lpR lFf">
    <q-header height-hint="61.59" class="bg-white text-grey-10" bordered>
      <q-toolbar class="my_toolbar bg-white">
        <q-btn flat dense round @click="visibleSidebar = !visibleSidebar;" aria-label="Toggle drawer" icon="menu"
          v-show="!visibleSidebar" class="q-mr-md" />
        <q-btn flat dense round @click="miniSidebar = !miniSidebar; miniSidebarChangedManually = true"
          aria-label="Toggle drawer" :icon="miniSidebar ? 'arrow_forward_ios' : 'arrow_back_ios_new'" class="q-mr-md"
          v-show="visibleSidebar" />
        <q-btn type="button" no-caps no-wrap align="left" flat :label="t('Search on HomeDocs...')" icon-right="search"
          class="full-width no-caps text-grey-8" @click.prevent="visibleFastSearch = true">
          <q-tooltip anchor="bottom middle" self="top middle">{{ t("Click to open fast search")
            }}</q-tooltip>
        </q-btn>
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
    <FastSearchModal v-model="visibleFastSearch" @close="visibleFastSearch = false"></FastSearchModal>
    <q-page-container>
      <router-view class="q-pa-sm" />
    </q-page-container>
  </q-layout>
</template>

<script setup>
import { ref, watch, computed } from "vue";
import { useQuasar } from "quasar";
import { useSessionStore } from "stores/session";
import { useI18n } from "vue-i18n";

import { default as SidebarDrawer } from "components/SidebarDrawer.vue"
//import { default as FastSearchSelector } from "components/FastSearchSelector.vue"
import { default as FastSearchModal } from "components/FastSearchModal.vue"
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

const visibleFastSearch = ref(false);

const miniSidebarChangedManually = ref(false);

const visibleSidebar = ref($q.screen.gt.sm);

const miniSidebar = ref($q.screen.md);

const currentScreenSize = computed(() => $q.screen.name);

watch(currentScreenSize, (newValue) => {
  if (!miniSidebarChangedManually.value) {
    miniSidebar.value = $q.screen.lt.lg;
  }
});

</script>