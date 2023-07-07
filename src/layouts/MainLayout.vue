<template>
  <q-layout class="bg-grey-1">
    <q-header elevated class="text-white" style="background: #24292e" height-hint="61.59">
      <q-toolbar class="q-py-sm q-px-md">
        <q-btn class="mobile-only" flat dense round @click="leftDrawerOpen = !leftDrawerOpen" aria-label="Menu"
          icon="menu" v-if="session.isLogged" />
        <q-avatar square size="42px">
          <img src="icons/favicon-128x128.png" />
        </q-avatar>
        HomeDocs
        <q-select ref="search" dark dense standout use-input hide-selected class="q-mx-md" color="black"
          :stack-label="false" label="Search..." v-model="text" :options="filteredOptions" @filter="onFilter"
          style="width: 100%" v-if="session.isLogged">
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
        <q-btn dense flat no-wrap>
          <q-avatar rounded size="24px" class="q-mr-sm">
            <q-icon name="language" />
          </q-avatar>
          {{ selectedLanguage.shortLabel }}
          <q-icon name="arrow_drop_down" size="16px" />
          <q-menu auto-close>
            <q-list dense style="min-width: 200px">
              <q-item class="GL__menu-link-signed-in">
                <q-item-section>
                  <div>Selected language: <strong>{{ selectedLanguage.label }}</strong></div>
                </q-item-section>
              </q-item>
              <q-separator />
              <q-item clickable :disable="selectedLanguage.value == availableLanguage.value" v-close-popup
                v-for="availableLanguage in availableLanguages" :key="availableLanguage.value"
                @click="onSelectLanguage(availableLanguage)">
                <q-item-section>
                  <div>{{ availableLanguage.label }}</div>
                </q-item-section>
              </q-item>
            </q-list>
          </q-menu>
        </q-btn>
      </q-toolbar>
    </q-header>
    <q-drawer v-model="leftDrawerOpen" show-if-above bordered class="bg-grey-2" :width="240" v-if="session.isLogged">
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
              <q-item-label>{{ link.text }}</q-item-label>
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
import { api } from 'boot/axios'
import { useSessionStore } from "stores/session";
import { useRouter } from "vue-router";
import { useI18n } from 'vue-i18n'
import { date, useQuasar } from "quasar";

const { t } = useI18n();
const $q = useQuasar();

const leftDrawerOpen = ref($q.screen.gt.lg);
const text = ref("");
const filteredOptions = ref([]);

const searching = ref(false);

function onFilter(val, update) {
  if (val && val.trim().length > 0) {
    filteredOptions.value = [];
    searching.value = true;
    update(() => {
      api.document.search(1, 8, { title: val }, "title", "ASC")
        .then((success) => {
          filteredOptions.value = success.data.results.documents.map((document) => { return ({ id: document.id, label: document.title, caption: "Created on " + date.formatDate(document.createdOnTimestamp * 1000, 'YYYY-MM-DD HH:mm:ss') + ", " + document.fileCount + " attachment/s" }); });
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

function onFilter2(val, update) {
  if (val && val.trim().length > 0) {
    console.log("es: " + val);
    searching.value = true;
    api.document.search(1, 4, {}, "title", "ASC")
      .then((success) => {
        filteredOptions.value = success.data.results.documents.map((document) => document.title);
        //filteredOptions.value = ["uno", "dos", "tres"];
        console.log(filteredOptions.value[0]);
        searching.value = false;
        update();
        return;
      })
      .catch((error) => {
        searching.value = false;
        $q.notify({
          type: "negative",
          message: t("API Error: fatal error"),
          caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
        });
        update();
        return;
      });
  }
  else if (val === '') {
    update(() => {
      filteredOptions.value = [];
    })
    return
  }
}

const session = useSessionStore();
const router = useRouter();

const availableLanguages = ref([
  {
    shortLabel: 'EN',
    label: 'English',
    value: 'en'
  },
  {
    shortLabel: 'ES',
    label: 'Español',
    value: 'es'
  },
  {
    shortLabel: 'GL',
    label: 'Galego',
    value: 'gl'
  }
]);

const selectedLanguage = ref(availableLanguages.value[0]);

function onSelectLanguage(language) {
  selectedLanguage.value = language;
}

const menuItems = ref([
  { icon: 'storage', text: 'Dashboard', routeName: 'index' },
  { icon: 'note_add', text: 'Add', routeName: 'newDocument' },
  { icon: 'find_in_page', text: 'Advanced search', routeName: 'advancedSearch' }
]);

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
</script>
