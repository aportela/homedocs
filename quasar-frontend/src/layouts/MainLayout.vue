<template>
  <q-layout class="bg-grey-1">
    <q-header elevated class="text-white" style="background: #24292e" height-hint="61.59">
      <q-toolbar class="q-py-sm q-px-md">
        <q-btn class="mobile-only" flat dense round @click="leftDrawerOpen = !leftDrawerOpen" aria-label="Menu"
          icon="menu" v-if="isLogged" />
        <q-avatar square size="42px">
          <img src="icons/favicon-128x128.png" />
        </q-avatar>
        HomeDocs
        <q-select ref="search" dark dense standout use-input hide-selected class="q-mx-md" color="black"
          :stack-label="false" :label="t('Search...')" v-model="text" :options="filteredOptions" @filter="onFilter"
          style="width: 100%" v-if="isLogged">
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
            <q-list class="bg-grey-9 text-white">
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
        <!--
        <q-btn :icon="iconDarkMode" @click="toggleDarkMode"></q-btn>
        -->
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
        <q-btn round dense flat :ripple="false" :icon="fabGithub" size="md" color="white" class="q-ml-sm" no-caps href="http://github.com/aportela/homedocs" target="_blank" />
      </q-toolbar>
    </q-header>
    <q-drawer v-model="leftDrawerOpen" show-if-above bordered class="bg-grey-2" :width="240" v-if="isLogged">
      <q-scroll-area class="fit">
        <q-list padding>
          <q-item-label header class="text-weight-bold text-uppercase">
            Menu
          </q-item-label>
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
    </q-drawer>
    <q-page-container>
      <router-view />
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
import { date, useQuasar } from "quasar";
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
  return($q.dark.isActive ? "dark_mode": "light_mode");
});

const defaultBrowserLocale = availableLocales.find((lang) => lang.value == defaultLocale);
const selectedLocale = ref(defaultBrowserLocale || availableLocales[0]);

function toggleDarkMode() {
  $q.dark.toggle();
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
