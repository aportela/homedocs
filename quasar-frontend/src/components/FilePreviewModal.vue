<template>
  <q-dialog v-model="visible" @hide="emit('close')">
    <q-card style="width: 700px; max-width: 80vw;">
      <q-card-section class="row items-center q-p-none">
        <div class="q-card-notes-dialog-header" v-if="title">{{ t("Document title") }}: <span>{{ title }}</span>
        </div>
        <div class="q-card-notes-dialog-header" v-else>{{ t("File preview") }}</div>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup />
      </q-card-section>
      <q-card-section class="q-pt-none">
        <p class="text-center"><strong>{{ currentFile.name }} ({{ currentFile.humanSize }})</strong></p>
        <q-pagination class="flex flex-center" v-if="totalFiles > 1" v-model="currentIndex" :max="totalFiles"
          color="dark" :max-pages="5" boundary-numbers direction-links icon-first="skip_previous" icon-last="skip_next"
          icon-prev="fast_rewind" icon-next="fast_forward" gutter="md"
          @update:model-value="previewLoadingError = false" />
      </q-card-section>
      <q-card-section class="q-pt-none">
        <div>
          <q-img v-if="isImage(currentFile.name)" :src="currentFile.url" loading="lazy" spinner-color="white"
            @error="previewLoadingError = true">
          </q-img>
          <div v-else-if="isAudio(currentFile.name)">
            <audio controls class="q-mt-md" style="width: 100%;">
              <source :src="currentFile.url" type="audio/mpeg" />
              {{ t("Your browser does not support the audio element") }}
            </audio>
          </div>
          <div v-else>
            <q-banner inline-actions>
              <q-icon name="error" size="sm" />
              {{ t("Preview not available") }}
            </q-banner>
          </div>
          <div class="text-subtitle1 text-center" v-if="previewLoadingError">
            <q-banner inline-actions>
              <q-icon name="error" size="sm" />
              {{ t("Error loading preview") }}
            </q-banner>
          </div>
        </div>
        <q-card-actions align="right">
          <q-btn color="primary" :href="currentFile.url" :label="t('Download')" icon="download"
            :disable="previewLoadingError" />
          <q-btn color="primary" v-close-popup :label="t('Close')" icon="close" />
        </q-card-actions>
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref, computed } from "vue";
import { useI18n } from 'vue-i18n'

const { t } = useI18n();

const props = defineProps({
  title: {
    type: String,
    required: false,
    default: ""
  },
  files: {
    type: Array,
    required: true
  },
  index: {
    type: Number,
    required: false,
    default: 0
  }
});

const currentIndex = ref(props.index + 1 || 1);
const totalFiles = ref(props.files?.length || 0);
if (currentIndex.value > totalFiles.value) {
  currentIndex.value = 1;
}

const currentFile = computed(() => props.files && totalFiles.value > 0 ? props.files[currentIndex.value - 1] : { id: null, name: null, humanSize: 0 });

const emit = defineEmits(['close']);

const visible = ref(true);

const previewLoadingError = ref(false);

function isImage(filename) {
  return (filename.match(/.(jpg|jpeg|png|gif)$/i));
}

function isAudio(filename) {
  return (filename.match(/.(mp3)$/i));
}

</script>

<style scoped>
.q-card-notes-dialog-header {
  font-size: 1.2em;
  font-weight: bold;
}

.q-card-notes-dialog-header span {
  font-weight: normal;
}
</style>