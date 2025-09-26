<template>
  <q-dialog v-model="dialogModel" persistent @hide="onHide">
    <q-card class="q-card-reauth-dialog">
      <q-card-section class="q-p-none">
        <div class="q-card-notes-dialog-header">
          {{ t("Session lost... re-auth required") }}
        </div>
      </q-card-section>
      <q-separator class="q-mb-md"></q-separator>
      <q-card-section class="q-p-none">
        <SignInForm :show-extra-bottom="false" @success="onSuccess">
          <template v-slot:slogan>
            <h4 class="q-mt-sm q-mb-md text-h4 text-weight-bolder">{{ t("Oooops") }}</h4>
            <div class="text-color-secondary">
              {{ t("Please enter again your credentials") }}</div>
          </template>
        </SignInForm>
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref } from "vue";
import { useI18n } from "vue-i18n";

import { default as SignInForm } from "src/components/Forms/SignInForm.vue"

const { t } = useI18n();

const emit = defineEmits(['close', 'success']);

const dialogModel = ref(true);

const onHide = () => {
  dialogModel.value = false;
  emit('close');
}

const onSuccess = () => {
  dialogModel.value = false;
  emit('success');
};

</script>

<style lang="css" scoped>
.q-card-reauth-dialog {
  width: 512px;
  max-width: 80vw;
}
</style>