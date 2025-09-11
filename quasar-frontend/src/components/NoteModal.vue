<template>
  <q-dialog v-model="visible" @hide="onClose">
    <q-card style="width: 80%; max-width: 80vw;">
      <q-card-section>
        <div class=" text-h6">{{ t(props.note.id ? "Document note (click on body to update)" : "Add document note") }}
        </div>
      </q-card-section>
      <q-separator />
      <q-card-section style="max-height: 50vh" class="scroll">
        <div class="cursor-pointer pre-line" v-if="readOnly" @click="readOnly = false">{{
          body }}
        </div>
        <q-input v-else filled type="textarea" v-model="body" maxlength="16384" autofocus></q-input>
      </q-card-section>
      <q-separator />
      <q-card-actions align="right">
        <q-btn flat @click.stop="onCancel"><q-icon left name="close" />{{ t("Cancel")
          }}</q-btn>
        <q-btn flat @click.stop="onSave"><q-icon left name="done" />{{ t("Save")
          }}</q-btn>
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref } from "vue";
import { useI18n } from "vue-i18n";
import { uid, date } from "quasar";

const { t } = useI18n();

const props = defineProps({
  note: Object,
});

const emit = defineEmits(['close', 'cancel', 'add', 'update']);

const visible = ref(true);

const body = ref(props.note ? props.note.body : null);

const readOnly = ref(props.note ? props.note.id != null : false);

function onClose() {
  visible.value = false;
  emit('close');
}

function onCancel() {
  visible.value = false;
  emit('cancel');
}

function onSave() {
  visible.value = false;
  if (props.note.id == null) {
    emit('add', { id: uid(), body: body, createdOnTimestamp: date.formatDate(new Date(), 'X'), createdOn: date.formatDate(new Date(), 'YYYY-MM-DD HH:mm:ss') });
  } else {
    emit('update', { id: props.note.id, body: body });
  }
}

</script>

<style scoped>
.pre-line {
  white-space: pre-line;
}
</style>