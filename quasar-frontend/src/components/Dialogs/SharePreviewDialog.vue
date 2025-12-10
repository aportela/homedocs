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
            <q-input dense outlined v-model="url" icon="delete">
              <template v-slot:append>
                <q-icon name="content_copy" class="cursor-pointer" @click="onCopyURLToClipboard" />
              </template>
            </q-input>
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

const visible = ref<boolean>(true);


const url = ref<string | null>(null);

watch(() => url.value, () => {
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
  if (url.value) {
    navigator.clipboard.writeText(url.value)
      .then(() => {
        console.log("Texto copiado al portapapeles: ", url.value);
      })
      .catch((error) => {
        console.error("Error al copiar al portapapeles: ", error);
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
