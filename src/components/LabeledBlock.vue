<template>
  <div class="wrap">
    <div class="label" v-if="label">{{ label }}</div>
    <slot name="default"></slot>
  </div>
</template>

<style scoped>
.wrap {
  padding: 12px 3px 0;
  border-radius: 5px;
  border: 1px solid #aaa;
  position: relative;
  margin-top: 4px;
  margin-bottom: 18px;
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
