<template>
  <q-dialog v-model="visible" @hide="onClose">
    <q-card>
      <q-card-section class="q-p-none">
        <slot name="header"></slot>
      </q-card-section>
      <q-card-section class="q-p-none">
        <slot name="body">
          <q-input filled type="textarea" v-model="body"></q-input>
        </slot>
      </q-card-section>
      <q-card-actions align="right">
        <q-btn outline @click.stop="onCancel"><q-icon left name="close" />{{ t("Cancel")
        }}</q-btn>
        <q-btn outline @click.stop="onSave"><q-icon left name="done" />{{ t("Save")
        }}</q-btn>
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref } from "vue";
import { useI18n } from "vue-i18n";

const { t } = useI18n();

const props = defineProps({
  note: Object,
});

console.log(props.note);

const emit = defineEmits(['close', 'cancel', 'add', 'update']);

const visible = ref(true);

const body = ref(props.note ? props.note.body : null);

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
  emit('add');
  emit('update');
}

</script>