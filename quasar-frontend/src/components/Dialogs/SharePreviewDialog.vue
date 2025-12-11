<template>
  <BaseDialog v-model="visible" @close="onClose" width="1280px" max-width="80vw">
    <template v-slot:header-left>
      <div>{{ t("Share preview") }}</div>
    </template>
    <template v-slot:body>
      <div class="row q-py-md">
        <div class="col-6 q-col-gutter-sm">
          <div id="qr-container" ref="qrContainerRef" :class="{ 'visible_qr': enabled, 'hidden_qr': !enabled }">
          </div>
        </div>
        <div class="col-6 q-col-gutter-sm">
          <p>
            <q-input dense outlined v-model="url" :disabled="!enabled" icon="delete" :hint="copiedToClipboardMessage">
              <template v-slot:append>
                <q-icon name="content_copy" class="cursor-pointer" @click="onCopyURLToClipboard" />
              </template>
            </q-input>
          </p>
          <p><q-toggle size="xl" v-model="enabled" color="green" icon="share" label="Enabled" /></p>
          <p><q-toggle size="xl" v-model="hasExpiration" :disabled="!enabled" color="green" icon="lock_clock"
              label="Has expiration" /></p>
          <p v-if="hasExpiration">
            <q-btn-toggle v-model="expiresOn" :disabled="!enabled" toggle-color="primary" :options="expiresOnOptions" />
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
import { ref, watch, onMounted } from "vue";
import { useI18n } from "vue-i18n";
import { api } from "src/composables/api";
import { default as BaseDialog } from "src/components/Dialogs/BaseDialog.vue";
import { default as QRCodeStyling } from "qr-code-styling";
import { type attachmentShareResponse as attachmentShareResponseInterface } from "src/types/apiResponses";
const { t } = useI18n();

const emit = defineEmits(['close']);

interface SharePreviewDialogProps {
  create: boolean;
  attachmentId: string;
};

const props = defineProps<SharePreviewDialogProps>();

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
  if (props.create) {
    api.sharedAttachment.create(props.attachmentId).then((successResponse: attachmentShareResponseInterface) => {
      url.value = `${window.location.protocol}//${window.location.hostname}${window.location.port ? `:${window.location.port}` : ""}/shared/file/?id=${successResponse.data.share.id}`;
      enabled.value = successResponse.data.share.enabled;
      hasExpiration.value = successResponse.data.share.expiresAtTimestamp > 0;
    }).catch(() => {
      //
    }).finally(() => {
      //
    });
  } else {
    api.sharedAttachment.get(props.attachmentId).then((successResponse: attachmentShareResponseInterface) => {
      url.value = `${window.location.protocol}//${window.location.hostname}${window.location.port ? `:${window.location.port}` : ""}/shared/file/?id=${successResponse.data.share.id}`;
      enabled.value = successResponse.data.share.enabled;
      hasExpiration.value = successResponse.data.share.expiresAtTimestamp > 0;
    }).catch(() => {
      //
    }).finally(() => {
      //
    });
  }
});
</script>

<style>
#qr-container svg {
  margin: 1em auto;
  display: block;
}

.visible_qr svg {
  filter: blur(0);

}

.hidden_qr svg {
  filter: blur(1.5rem);
}

.blur {
  filter: blur(1.5rem);
}
</style>