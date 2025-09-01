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
        <q-btn :icon="iconDarkMode" @click="toggleDarkMode"></q-btn>
        <q-btn dense flat no-wrap>
          <q-avatar rounded size="24px" class="q-mr-sm">
            <q-icon name="language" />
          </q-avatar>
          {{ selectedLocale.shortLabel }}
          <q-icon name="arrow_drop_down" size="16px" />
          <q-menu auto-close>
            <q-list dense style="min-width: 200px">
              <q-item class="GL__menu-link-signed-in">
                <q-item-section>
                  <div>{{ t("Selected language") }}: <strong>{{ selectedLocale.label }}</strong></div>
                </q-item-section>
              </q-item>
              <q-separator />
              <q-item clickable :disable="selectedLocale.value == availableLanguage.value" v-close-popup
                v-for="availableLanguage in availableLocales" :key="availableLanguage.value"
                @click="onSelectLocale(availableLanguage, true)">
                <q-item-section>
                  <div>{{ availableLanguage.label }}</div>
                </q-item-section>
              </q-item>
            </q-list>
          </q-menu>
        </q-btn>
        <q-btn round dense flat :ripple="false" :icon="fabGithub" size="md" color="white" class="q-ml-sm" no-caps
          href="http://github.com/aportela/homedocs" target="_blank" />
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
import { ref, computed, watch } from "vue";
import { api } from "boot/axios";
import { useSessionStore } from "stores/session";
import { useInitialStateStore } from "stores/initialState";
import { useRouter } from "vue-router";
import { useI18n } from "vue-i18n";
import { LocalStorage, date, useQuasar } from "quasar";

import { i18n, defaultLocale } from "src/boot/i18n";
import { fabGithub } from "@quasar/extras/fontawesome-v6";

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

const availableLocales = [
  {
    shortLabel: 'EN',
    label: 'English',
    value: 'en-US'
  },
  {
    shortLabel: 'ES',
    label: 'Español',
    value: 'es-ES'
  },
  {
    shortLabel: 'GL',
    label: 'Galego',
    value: 'gl-GL'
  }
];

const menuItems = [
  { icon: 'storage', text: "Dashboard", routeName: 'index' },
  { icon: 'note_add', text: "Add", routeName: 'newDocument' },
  { icon: 'find_in_page', text: "Advanced search", routeName: 'advancedSearch' }
];

const iconDarkMode = computed(() => {
  return ($q.dark.isActive ? "dark_mode" : "light_mode");
});

const defaultBrowserLocale = availableLocales.find((lang) => lang.value == defaultLocale);
const selectedLocale = ref(defaultBrowserLocale || availableLocales[0]);

watch(
  () => $q.dark.isActive,
  val => LocalStorage.set('darkMode', val)
)

if (LocalStorage.has('darkMode')) {
  $q.dark.set(LocalStorage.getItem('darkMode'))
} else {
  $q.dark.set(false)
}

function toggleDarkMode() {
  $q.dark.toggle();
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

function onSelectLocale(locale, save) {
  selectedLocale.value = locale;
  i18n.global.locale.value = locale.value;
  if (save) {
    session.saveLocale(locale.value);
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
