<template>
  <q-layout view="lHh lpR lFf">
    <q-header elevated height-hint="61.59">
      <q-toolbar class="q-py-sm q-px-md">
        <q-btn flat dense round @click="leftDrawerOpen = !leftDrawerOpen" aria-label="Menu" icon="menu"
          v-if="isLogged" />
        <q-select ref="search" dense standout use-input hide-selected class="q-mx-md" :placeholder="t('Search...')"
          v-model="text" :options="filteredOptions" @filter="onFilter" style="width: 100%" v-if="isLogged">
          <template v-slot:prepend>
            <q-icon name="search" />
          </template>
          <template v-slot:no-option v-if="searching">
            <q-item>
              <q-item-section>
                <div class="text-center">
                  <q-spinner-pie color="grey-5" size="24px" />
                </div>
              </q-item-section>
            </q-item>
          </template>
          <template v-slot:option="scope">
            <q-list>
              <q-item v-bind="scope.itemProps" :to="{ name: 'document', params: { id: scope.opt.id } }">
                <q-item-section side>
                  <q-icon name="collections_bookmark" />
                </q-item-section>
                <q-item-section>
                  <q-item-label>{{ scope.opt.label }}</q-item-label>
                  <q-item-label caption>{{ scope.opt.caption }}</q-item-label>
                </q-item-section>
              </q-item>
            </q-list>
          </template>
        </q-select>
        <q-space />
        <q-btn-group flat>
          <DarkModeButton dense />
          <SwitchLanguageButton :short-labels="true" />
          <GitHubButton dense :href="GITHUB_PROJECT_URL" />
        </q-btn-group>
      </q-toolbar>
    </q-header>
    <q-drawer v-model="leftDrawerOpen" show-if-above bordered class="_bg-grey-2" :width="240" v-if="isLogged"
      :mini="!leftDrawerOpen || miniState" @click.capture="drawerClick">
      <q-scroll-area class="fit">
        <q-list padding>
          <q-item class="cursor-pointer non-selectable">
            <q-item-section avatar>
              <q-avatar square size="24px">
                <img src="icons/favicon-128x128.png" />
              </q-avatar>
            </q-item-section>
            <q-item-section>
              <q-item-label class="text-weight-bold text-uppercase">HomeDocs</q-item-label>
            </q-item-section>
          </q-item>
          <q-item v-for="link in menuItems" :key="link.text" v-ripple clickable :to="{ name: link.routeName }">
            <q-item-section avatar>
              <q-icon color="grey" :name="link.icon" />
            </q-item-section>
            <q-item-section>
              <q-item-label>{{ t(link.text) }}</q-item-label>
            </q-item-section>
          </q-item>
          <q-item v-ripple clickable @click="signOut">
            <q-item-section avatar>
              <q-icon color="grey" name="logout" />
            </q-item-section>
            <q-item-section>
              <q-item-label>{{ t("Sign out") }}</q-item-label>
            </q-item-section>
          </q-item>
        </q-list>
      </q-scroll-area>
      <div class="q-mini-drawer-hide absolute" style="top: 15px; right: -17px">
        <q-btn dense round unelevated color="accent" icon="chevron_left" @click="miniState = true"
          style="background-color: rgb(105, 108, 255); color: white; border: 6px solid rgb(242, 242, 247);" />
      </div>
    </q-drawer>
    <q-page-container>
      <router-view class="q-pa-sm" />
    </q-page-container>
  </q-layout>
</template>

<script setup>
import { ref, computed } from "vue";
import { api } from "boot/axios";
import { useSessionStore } from "stores/session";
import { useInitialStateStore } from "stores/initialState";
import { useRouter } from "vue-router";
import { useI18n } from "vue-i18n";
import { date, LocalStorage, useQuasar } from "quasar";

import { default as DarkModeButton } from "components/DarkModeButton.vue"
import { default as SwitchLanguageButton } from "components/SwitchLanguageButton.vue"
import { default as GitHubButton } from "components/GitHubButton.vue"
import { GITHUB_PROJECT_URL } from "src/constants"

const { t } = useI18n();
const $q = useQuasar();
const session = useSessionStore();
if (!session.isLoaded) {
  session.load();
}
const initialState = useInitialStateStore();
const router = useRouter();
const isLogged = computed(() => session.isLogged);
const leftDrawerOpen = ref($q.screen.gt.lg);
const text = ref("");
const filteredOptions = ref([]);
const searching = ref(false);

const miniState = ref(false);

const menuItems = [
  { icon: 'storage', text: "Dashboard", routeName: 'index' },
  { icon: 'note_add', text: "Add", routeName: 'newDocument' },
  { icon: 'find_in_page', text: "Advanced search", routeName: 'advancedSearch' }
];


if (LocalStorage.has('darkMode')) {
  $q.dark.set(LocalStorage.getItem('darkMode'))
} else {
  $q.dark.set(false)
}

function drawerClick(e) {
  if (miniState.value) {
    miniState.value = false

    // notice we have registered an event with capture flag;
    // we need to stop further propagation as this click is
    // intended for switching drawer to "normal" mode only
    e.stopPropagation()
  }
}

function onFilter(val, update) {
  if (val && val.trim().length > 0) {
    filteredOptions.value = [];
    searching.value = true;
    update(() => {
      api.document.search(1, 8, { title: val }, "title", "ASC")
        .then((success) => {
          filteredOptions.value = success.data.results.documents.map((document) => {
            return ({ id: document.id, label: document.title, caption: t("Fast search caption", { creation: date.formatDate(document.createdOnTimestamp * 1000, 'YYYY-MM-DD HH:mm:ss'), attachmentCount: document.fileCount }) });
          });
          searching.value = false;
          return;
        })
        .catch((error) => {
          searching.value = false;
          $q.notify({
            type: "negative",
            message: t("API Error: fatal error"),
            caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
          });
          return;
        });
    });
  } else {
    update(() => {
      filteredOptions.value = [];
    });
    return;
  }
}

function signOut() {
  api.user
    .signOut()
    .then((success) => {
      session.signOut();
      router.push({
        name: "signIn",
      });
    })
    .catch((error) => {
      $q.notify({
        type: "negative",
        message: t("API Error: fatal error"),
        caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
      });
    });
}

initialState.load();

</script>