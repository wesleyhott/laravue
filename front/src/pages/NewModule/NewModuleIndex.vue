<template>
  <ModuleLayout title="New Modules" :menus="moduleMenus" />
</template>

<script setup lang="ts">
import ModuleLayout, { ModuleMenuProps } from "../../layouts/ModuleLayout.vue";

let moduleMenus: ModuleMenuProps[] = [
  // {{ laravue-insert:route }}
];
</script>

<style scoped></style>
