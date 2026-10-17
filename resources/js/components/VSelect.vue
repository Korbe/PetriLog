<template>
  <div>
    <!-- Label -->
    <label v-if="label" :for="id" class="block text-sm font-medium mb-1.5 text-(--app-ink)">
      {{ label }}
      <span v-if="mandatory" class="text-(--app-float)">*</span>
    </label>

    <!-- Select Input -->
    <select :id="id" class="form-select w-full" :class="selectClass" :disabled="disabled" :value="modelValue"
      @change="$emit('update:modelValue', $event.target.value)">
      <option v-if="placeholder" value="" disabled>{{ placeholder }}</option>
      <option v-for="(option, index) in options" :key="index" :value="option.value">
        {{ option.label }}
      </option>
    </select>

    <!-- Supporting Text / Error / Success Messages -->
    <div v-if="error" class="text-xs mt-1.5 font-medium text-(--app-danger)">{{ error }}</div>
    <div v-if="success" class="text-xs mt-1.5 font-medium text-(--app-success)">{{ success }}</div>
  </div>
</template>

<script>
export default {
  props: {
    id: { type: String, required: false },
    label: { type: String, default: '' },
    options: { type: Array, required: true }, // Array von Objekten { value, label }
    placeholder: { type: String, default: '' },
    mandatory: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
    error: { type: String, default: '' },
    success: { type: String, default: '' },
    modelValue: { type: [String, Number], default: '' }
  },
  computed: {
    selectClass() {
      return {
        'border-(--app-danger)!': this.error,
        'border-(--app-success)!': this.success,
        'cursor-not-allowed': this.disabled
      };
    }
  }
};
</script>
