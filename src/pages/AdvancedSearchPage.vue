<template>
  <q-page class="flex bg-grey-2">
    <div class="q-pa-md">
      <h3>Advanced search</h3>
      <div class="q-gutter-y-md">
        <q-tabs v-model="tab">
          <q-tab name="conditions" icon="filter_alt" label="Conditions" />
          <q-tab name="results" icon="account_tree" label="Results" />
        </q-tabs>
        <q-separator />
        <q-tab-panels v-model="tab" animated>
          <q-tab-panel name="conditions">
            <div class="text-h6">Search conditions</div>


            <q-card>
              <q-card-section>
                <q-input dense outlined v-model="filter.title" type="text" name="title" clearable
                  :label="t('Document title')" :disable="searching" :autofocus="true">
                  <template v-slot:prepend>
                    <q-icon name="search" />
                  </template>
                </q-input>
                <q-input dense outlined v-model="filter.description" type="text" name="description" clearable
                  :label="t('Document description')" :disable="searching" :autofocus="true">
                  <template v-slot:prepend>
                    <q-icon name="search" />
                  </template>
                </q-input>
                <q-select dense outlined v-model="filter.dateFilterType" :options="dateFilterOptions"
                  label="Document date" />
                <!--
                <q-input dense outlined v-model="filter.dateRange" mask="date">
                  <template v-slot:append>
                    <q-icon name="event" class="cursor-pointer">
                      <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                        <q-date v-model="filter.dateRange" range>
                          <div class="row items-center justify-end">
                            <q-btn v-close-popup label="Close" color="primary" flat />
                          </div>
                        </q-date>
                      </q-popup-proxy>
                    </q-icon>
                  </template>
                </q-input>
                -->
              </q-card-section>
              <!--
              <q-card-section>
                <q-select label="Tags" outlined v-model="filter.tags" use-input use-chips multiple hide-dropdown-icon
                  :options="['aa', '12']" input-debounce="0" new-value-mode="add-unique" clearable />
              </q-card-section>
              -->
              <q-card-section>
                <TagSelector :selectedTags="[12]"></TagSelector>
              </q-card-section>
            </q-card>

          </q-tab-panel>
          <q-tab-panel name="results">
            <div class="text-h6">Search results</div>
            Lorem ipsum dolor sit amet consectetur adipisicing elit2.
          </q-tab-panel>
        </q-tab-panels>

      </div>
    </div>
  </q-page>
</template>

<script setup>

import { ref } from "vue";

import { useI18n } from 'vue-i18n'

import { default as TagSelector } from "components/TagSelector.vue";

const { t } = useI18n();

const searching = ref(false);

const tab = ref('conditions');

const dateFilterOptions = ref([
  { label: 'Any date', value: 0 },
  { label: 'today', value: 1 },
  { label: 'yesterday', value: 2 }
]);

const filter = ref({
  title: null,
  description: null,
  dateFilterType: null,
  dateRange: null,
  fromTimestamp: null,
  toTimestamp: null,
  tags: []
});

</script>
