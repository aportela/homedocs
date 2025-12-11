<template>
  <BaseDialog v-model="visible" @close="onClose" width="1280px" max-width="80vw">
    <template v-slot:header-left>
      <div v-if="documentTitle">{{ t("Document title") }}:
        <router-link :to="{ name: 'document', params: { id: documentId } }" class="text-decoration-hover">{{
          documentTitle }}</router-link>
      </div>
      <div v-else>{{ t("Share preview") }}</div>
    </template>
    <template v-slot:body>
      <div class="row q-py-md">
        <div class="col-6 q-col-gutter-sm">
          <div id="qr-container" ref="qrContainerRef">
          </div>
        </div>
        <div class="col-6 q-col-gutter-sm">
          <p>
            <q-input dense outlined v-model="url" icon="delete" :hint="copiedToClipboardMessage">
              <template v-slot:append>
                <q-icon name="content_copy" class="cursor-pointer" @click="onCopyURLToClipboard" />
              </template>
            </q-input>
          </p>
          <p><q-toggle size="xl" v-model="enabled" color="green" icon="share" label="Enabled" /></p>
          <p><q-toggle size="xl" v-model="hasExpiration" color="green" icon="lock_clock" label="Has expiration" /></p>
          <p v-if="hasExpiration">
            <q-btn-toggle v-model="expiresOn" toggle-color="primary" :options="expiresOnOptions" />
          </p>
          <p>
            <q-btn @click="onClick" icon="note" label="regenerate" class="full-width" />
          </p>
        </div>
      </div>
    </template>
  </BaseDialog>
</template>

<script setup lang="ts">
import { ref, watch, onMounted, nextTick } from "vue";
import { useI18n } from "vue-i18n";
import { default as BaseDialog } from "src/components/Dialogs/BaseDialog.vue";
import { default as QRCodeStyling } from "qr-code-styling";
const { t } = useI18n();

const emit = defineEmits(['close']);

interface SharePreviewDialogProps {
  documentId: string | null;
  documentTitle: string | null;
};

withDefaults(defineProps<SharePreviewDialogProps>(), {
});

const expiresOnOptions = [
  { label: '1 minute', value: 'oneMinute' },
  { label: '1 hour', value: 'oneHour' },
  { label: '1 day', value: 'oneDay' },
  { label: '1 week', value: 'oneWeek' },
  { label: '1 month', value: 'oneMonth' },
  { label: '1 year', value: 'oneYear' }
];
const visible = ref<boolean>(true);

const copiedToClipboardMessage = ref<string | undefined>(undefined);

const url = ref<string | null>(null);

const enabled = ref<boolean>(true);

const hasExpiration = ref<boolean>(true);
const expiresOn = ref(expiresOnOptions[0]?.value);

watch(() => url.value, () => {
  copiedToClipboardMessage.value = undefined;
  onUpdate();
});

const qrContainerRef = ref<HTMLElement | null>(null);

const generateRandomBytes = (length: number): Uint8Array => {
  const randomValues = new Uint8Array(length);
  crypto.getRandomValues(randomValues);
  return randomValues;
};

const base64UrlEncode = (array: Uint8Array): string => {
  const base64 = btoa(String.fromCharCode(...array));
  return base64.replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
};

const randomURL = (): string => {
  const id = base64UrlEncode(generateRandomBytes(32));
  return (`${window.location.protocol}//${window.location.hostname}${window.location.port ? `:${window.location.port}` : ""}/shared/file/?id=${id}`);
};


const onUpdate = () => {
  const qrCode = new QRCodeStyling({
    width: 500,
    height: 500,
    margin: 8,
    type: "svg",
    data: url.value!,
    dotsOptions: {
      color: "#000000",
      type: "square",
    },
    backgroundOptions: {
      color: "#ffffff",
    },
  });

  // Renderizar el código QR en un contenedor
  if (qrContainerRef.value) {

    QRCodeStyling._clearContainer(qrContainerRef.value);
    qrCode.append(qrContainerRef.value); // Esto colocará el QR en un contenedor
  } else {
    console.error("container not found");
  }
};

const onClose = () => {
  emit('close');
};

const onClick = () => {
  url.value = randomURL();
  onUpdate();
};


const onCopyURLToClipboard = () => {
  copiedToClipboardMessage.value = undefined;
  if (url.value) {
    navigator.clipboard.writeText(url.value)
      .then(() => {
        copiedToClipboardMessage.value = "Copied to clipboard!";
      })
      .catch((error) => {
        console.error("Error copying url to clipboard", error);
      });
  }
};
onMounted(() => {
  nextTick(
    () => { onClick(); }
  ).catch((e) => {
    console.error(e);
  });
});
</script>

<style>
#qr-container svg {
  margin: 1em auto;
  display: block;
}
</style>