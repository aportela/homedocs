<template>
  <q-layout class="bg-grey-1">
    <q-header elevated class="text-white" style="background: #24292e" height-hint="61.59">
      <q-toolbar class="q-py-sm q-px-md">
        <q-btn round2 dense flat :ripple="false" size="19px" color="white" class="q-mr-sm" no-caps>
          <q-avatar square size="42px">
            <img src="icons/favicon-128x128.png" />
          </q-avatar>
        </q-btn>
        HomeDocs

        <q-select ref="search" dark dense standout use-input hide-selected class="GL__toolbar-select q-mx-md"
          color="black" :stack-label="false" label="Search..." v-model="text" :options="filteredOptions" @filter="filter"
          style="width: 300px" v-if="session.isLogged">
          <template v-slot:append>
            <img src="https://cdn.quasar.dev/img/layout-gallery/img-github-search-key-slash.svg" />
          </template>

          <template v-slot:no-option>
            <q-item>
              <q-item-section>
                <div class="text-center">
                  <q-spinner-pie color="grey-5" size="24px" />
                </div>
              </q-item-section>
            </q-item>
          </template>
        </q-select>

        <div v-if="session.isLogged && $q.screen.gt.sm"
          class="GL__toolbar-link q-ml-xs q-gutter-md text-body2 text-weight-bold row items-center no-wrap">
          <router-link class="text-white text-weight-bold" style="text-decoration: none"
            :to="{ name: 'index' }">Dashboard</router-link>
          <router-link class="text-white text-weight-bold" style="text-decoration: none" :to="{ name: 'newDocument' }">New
            document</router-link>
          <router-link class="text-white text-weight-bold" style="text-decoration: none"
            :to="{ name: 'advancedSearch' }">Advanced
            search</router-link>
        </div>
        <q-space />

        <a v-if="session.isLogged" href="javascript:void(0)" class="text-white text-weight-bold"
          style="text-decoration: none" @click="signOut">Sign Out
        </a>
      </q-toolbar>
    </q-header>
    <q-page-container>
      <router-view />
    </q-page-container>
  </q-layout>
</template>

<script setup>
import { ref } from "vue";

import { useSessionStore } from "stores/session";
import { useRouter } from "vue-router";

const text = ref("");
const filteredOptions = ref([]);
const filter = ref(null);
const session = useSessionStore();
const router = useRouter();

function signOut() {
  session.signOut();
  router.push({
    name: "signIn",
  });
}
</script>
