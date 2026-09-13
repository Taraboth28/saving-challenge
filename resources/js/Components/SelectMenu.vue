<template>
  <div ref="root" class="relative">
    <button type="button" class="input-dark flex items-center justify-between gap-3 text-left"
            :aria-expanded="open" @click="open = !open">
      <span class="truncate">{{ selectedLabel }}</span>
      <ChevronDownIcon class="w-4 h-4 shrink-0 text-slate-400 transition-transform" :class="open ? 'rotate-180' : ''" />
    </button>
    <div v-if="open" class="select-menu absolute z-30 mt-2 w-full overflow-hidden rounded-xl p-1">
      <button v-for="option in options" :key="option.value" type="button"
              class="select-option block w-full rounded-lg px-3 py-2 text-left text-sm"
              :class="option.value === modelValue ? 'select-option-selected' : ''"
              @click="select(option.value)">
        {{ option.label }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { ChevronDownIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  modelValue: { type: [String, Number], default: '' },
  options: { type: Array, required: true },
});
const emit = defineEmits(['update:modelValue']);
const root = ref(null);
const open = ref(false);
const selectedLabel = computed(() => props.options.find(option => option.value === props.modelValue)?.label ?? 'Select an option');

function select(value) {
  emit('update:modelValue', value);
  open.value = false;
}
function closeOnOutsideClick(event) {
  if (root.value && !root.value.contains(event.target)) open.value = false;
}

onMounted(() => document.addEventListener('click', closeOnOutsideClick));
onBeforeUnmount(() => document.removeEventListener('click', closeOnOutsideClick));
</script>
