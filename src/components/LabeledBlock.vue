<template>
  <div class="wrap">
    <div class="label" v-if="label">{{ label }}</div>
    <slot name="default"></slot>
  </div>
</template>

<style scoped>
.wrap {
  padding: 3px;
  border-radius: 5px;
  border: 1px solid #aaa;
  position: relative;
}
.label {
  position: absolute;
  top: -18px;
  left: 8px;
  padding: 4px 6px;
  color: #aaa;
  background: #fff;
}
</style>

<script>
export default {
  name: 'LabeledBlock',
  props: {
    label: String,
  },
};
</script>
