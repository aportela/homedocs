<template>
  <iframe id="pdf-container" :src="path" :class="innerContentClass"></iframe>
</template>

<script setup lang="ts">
interface PDFWrapperProps {
  path: string
  innerContentClass?: string;
};

defineProps<PDFWrapperProps>();
</script>

<style lang="css" scoped>
iframe#pdf-container {
  display: block;
  width: 100%;
  margin: 0px auto;
}
</style>
