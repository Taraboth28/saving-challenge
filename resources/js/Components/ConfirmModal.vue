<template>
  <teleport to="body">
    <transition name="modal">
      <div v-if="modelValue"
           class="fixed inset-0 z-50 flex items-center justify-center p-4 modal-backdrop"
           @click.self="$emit('update:modelValue', false)">
        <div class="glass rounded-2xl w-full max-w-sm animate-fade-in-scale p-6 text-center" @click.stop>
          <!-- Icon -->
          <div class="w-14 h-14 rounded-full bg-red-500/15 border border-red-500/30 flex items-center justify-center mx-auto mb-4">
            <svg class="w-7 h-7 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
          </div>
          <h3 class="font-bold text-white text-lg mb-2">{{ title }}</h3>
          <p class="text-slate-400 text-sm mb-6">{{ message }}</p>
          <div class="flex gap-3">
            <button @click="$emit('update:modelValue', false)" class="btn-secondary flex-1 justify-center">
              Cancel
            </button>
            <button @click="confirm" :disabled="loading" class="btn-danger flex-1 justify-center">
              <svg v-if="loading" class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
              </svg>
              {{ confirmText }}
            </button>
          </div>
        </div>
      </div>
    </transition>
  </teleport>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  modelValue: Boolean,
  title:       { type: String, default: 'Are you sure?' },
  message:     { type: String, default: 'This action cannot be undone.' },
  confirmText: { type: String, default: 'Delete' },
});
const emit = defineEmits(['update:modelValue', 'confirmed']);

const loading = ref(false);

async function confirm() {
  loading.value = true;
  try {
    emit('confirmed');
  } finally {
    loading.value = false;
  }
}
</script>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: all 0.2s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
</style>
