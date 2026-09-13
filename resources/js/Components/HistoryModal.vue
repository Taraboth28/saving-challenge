<template>
  <!-- Add history entry modal -->
  <teleport to="body">
    <transition name="modal">
      <div v-if="modelValue"
           class="fixed inset-0 z-50 flex items-center justify-center p-4 modal-backdrop"
           @click.self="$emit('update:modelValue', false)">
        <div class="glass rounded-2xl w-full max-w-md animate-fade-in-scale" @click.stop>
          <div class="flex items-center justify-between px-6 py-4 border-b border-white/5">
            <h2 class="font-bold text-white">Add Saving Entry</h2>
            <button @click="$emit('update:modelValue', false)"
                    class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-white/5 transition-colors">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <form @submit.prevent="handleSubmit" class="px-6 py-5 space-y-4">
            <!-- Type toggle -->
            <div>
              <label class="block text-xs font-semibold text-slate-300 mb-2 uppercase tracking-wider">Type</label>
              <div class="flex rounded-xl overflow-hidden border border-white/10">
                <button type="button" @click="form.type = 'deposit'"
                        class="flex-1 py-2.5 text-sm font-semibold transition-all"
                        :class="form.type === 'deposit'
                          ? 'bg-emerald-500/20 text-emerald-300 border-r border-emerald-500/30'
                          : 'bg-slate-800/50 text-slate-400 hover:text-slate-300'">
                  ↑ Deposit
                </button>
                <button type="button" @click="form.type = 'withdrawal'"
                        class="flex-1 py-2.5 text-sm font-semibold transition-all"
                        :class="form.type === 'withdrawal'
                          ? 'bg-red-500/20 text-red-300'
                          : 'bg-slate-800/50 text-slate-400 hover:text-slate-300'">
                  ↓ Withdrawal
                </button>
              </div>
            </div>

            <!-- Amount -->
            <div>
              <label class="block text-xs font-semibold text-slate-300 mb-1.5 uppercase tracking-wider">
                Amount *
              </label>
              <input v-model.number="form.amount" type="number" required min="0.01" step="0.01"
                     placeholder="500.00" class="input-dark" />
              <p v-if="errors.amount" class="text-red-400 text-xs mt-1">{{ errors.amount[0] }}</p>
            </div>

            <!-- Date and time -->
            <div>
              <label class="block text-xs font-semibold text-slate-300 mb-1.5 uppercase tracking-wider">
                Date and time *
              </label>
              <input v-model="form.date" type="datetime-local" required :max="currentDateTime" class="input-dark" />
              <p v-if="errors.date" class="text-red-400 text-xs mt-1">{{ errors.date[0] }}</p>
            </div>

            <!-- Note -->
            <div>
              <label class="block text-xs font-semibold text-slate-300 mb-1.5 uppercase tracking-wider">
                Note
              </label>
              <input v-model="form.note" type="text" placeholder="e.g. September paycheck savings"
                     class="input-dark" maxlength="255" />
            </div>

            <div class="flex gap-3 pt-1">
              <button type="button" @click="$emit('update:modelValue', false)" class="btn-secondary flex-1">
                Cancel
              </button>
              <button type="submit" :disabled="loading"
                      :class="form.type === 'deposit' ? 'btn-success' : 'btn-danger'"
                      class="flex-1 justify-center">
                <svg v-if="loading" class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                </svg>
                {{ form.type === 'deposit' ? 'Add Deposit' : 'Record Withdrawal' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </transition>
  </teleport>
</template>

<script setup>
import { ref, computed } from 'vue';
import { historyApi } from '@/composables/useApi.js';

const props = defineProps({
  modelValue: Boolean,
  goalId: { type: String, required: true },
});
const emit = defineEmits(['update:modelValue', 'saved']);

function toDateTimeLocal(date = new Date()) {
  const pad = value => String(value).padStart(2, '0');
  return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
}

const currentDateTime = computed(() => toDateTimeLocal());

const defaultForm = () => ({ amount: null, type: 'deposit', note: '', date: currentDateTime.value });
const form    = ref(defaultForm());
const errors  = ref({});
const loading = ref(false);

async function handleSubmit() {
  loading.value = true;
  errors.value  = {};
  try {
    await historyApi.store(props.goalId, form.value);
    emit('saved');
    emit('update:modelValue', false);
    form.value = defaultForm();
  } catch (err) {
    if (err.response?.status === 422) {
      errors.value = err.response.data.errors ?? {};
    }
  } finally {
    loading.value = false;
  }
}
</script>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: all 0.2s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
</style>
