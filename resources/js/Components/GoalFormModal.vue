<template>
  <!-- Modal backdrop -->
  <teleport to="body">
    <transition name="modal">
      <div v-if="modelValue"
           class="fixed inset-0 z-50 flex items-center justify-center p-4 modal-backdrop"
           @click.self="$emit('update:modelValue', false)">
        <div class="glass rounded-2xl w-full max-w-lg animate-fade-in-scale" @click.stop>
          <!-- Header -->
          <div class="flex items-center justify-between px-6 py-4 border-b border-white/5">
            <h2 class="font-bold text-white text-lg">
              {{ isEditing ? 'Edit Goal' : 'New Saving Goal' }}
            </h2>
            <button @click="$emit('update:modelValue', false)"
                    class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-white/5 transition-colors">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Form -->
          <form @submit.prevent="handleSubmit" class="px-6 py-5 space-y-4 max-h-[70vh] overflow-y-auto">
            <!-- Name -->
            <div>
              <label class="block text-xs font-semibold text-slate-300 mb-1.5 uppercase tracking-wider">
                Goal Name *
              </label>
              <input v-model="form.name" type="text" required placeholder="e.g. Emergency Fund"
                     class="input-dark" maxlength="100" />
              <p v-if="errors.name" class="text-red-400 text-xs mt-1">{{ errors.name[0] }}</p>
            </div>

            <!-- Description -->
            <div>
              <label class="block text-xs font-semibold text-slate-300 mb-1.5 uppercase tracking-wider">
                Description
              </label>
              <textarea v-model="form.description" rows="2" placeholder="What are you saving for?"
                        class="input-dark resize-none" maxlength="500"></textarea>
            </div>

            <!-- Target & Currency -->
            <div class="grid grid-cols-3 gap-3">
              <div class="col-span-2">
                <label class="block text-xs font-semibold text-slate-300 mb-1.5 uppercase tracking-wider">
                  Target Amount
                </label>
                <input v-model.number="form.target_amount" type="number" min="1" step="0.01"
                       placeholder="10000" class="input-dark" />
                <p v-if="errors.target_amount" class="text-red-400 text-xs mt-1">{{ errors.target_amount[0] }}</p>
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5 uppercase tracking-wider">
                  Currency
                </label>
                <select v-model="form.currency" class="input-dark">
                  <option v-for="currency in currencies" :key="currency.code" :value="currency.code">
                    {{ currency.label }}
                  </option>
                </select>
              </div>
            </div>

            <!-- Initial saved amount (only on create) -->
            <div v-if="!isEditing">
              <label class="block text-xs font-semibold text-slate-300 mb-1.5 uppercase tracking-wider">
                Already Saved
              </label>
              <input v-model.number="form.saved_amount" type="number" min="0" step="0.01"
                     placeholder="0" class="input-dark" />
            </div>

            <!-- Deadline -->
            <div>
              <label class="block text-xs font-semibold text-slate-300 mb-1.5 uppercase tracking-wider">
                  Deadline
              </label>
              <input v-model="form.deadline" type="date" :min="isEditing ? undefined : minDate" class="input-dark" />
              <p v-if="errors.deadline" class="text-red-400 text-xs mt-1">{{ errors.deadline[0] }}</p>
            </div>

            <!-- Color & Icon -->
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5 uppercase tracking-wider">
                  Color
                </label>
                <div class="flex gap-2 flex-wrap">
                  <button v-for="c in palette" :key="c" type="button"
                          @click="form.color = c"
                          class="w-7 h-7 rounded-lg transition-all"
                          :style="{ background: c, outline: form.color === c ? `2px solid white` : 'none', outlineOffset: '2px' }">
                  </button>
                </div>
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5 uppercase tracking-wider">
                  Icon
                </label>
                <div class="flex gap-2 flex-wrap">
                  <button v-for="key in iconOptions" :key="key" type="button"
                          @click="form.icon = key"
                          class="w-8 h-8 rounded-lg flex items-center justify-center text-base transition-all"
                          :class="form.icon === key ? 'bg-indigo-500/30 ring-1 ring-indigo-500' : 'bg-slate-800 hover:bg-slate-700'">
                    <GoalIcon :name="key" class-name="w-5 h-5" />
                  </button>
                </div>
              </div>
            </div>

            <!-- Submit -->
            <div class="flex gap-3 pt-2">
              <button type="button" @click="$emit('update:modelValue', false)" class="btn-secondary flex-1">
                Cancel
              </button>
              <button type="submit" :disabled="loading" class="btn-primary flex-1 justify-center">
                <svg v-if="loading" class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                </svg>
                <span>{{ isEditing ? 'Save Changes' : 'Create Goal' }}</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </transition>
  </teleport>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { goalsApi } from '@/composables/useApi.js';
import GoalIcon from '@/Components/GoalIcon.vue';

const props = defineProps({
  modelValue: Boolean,
  editGoal: { type: Object, default: null },
});
const emit = defineEmits(['update:modelValue', 'saved']);

const isEditing = computed(() => !!props.editGoal);

const defaultForm = () => ({
  name: '', description: '', target_amount: null, saved_amount: 0,
  deadline: '', currency: 'USD', color: '#6366f1', icon: 'piggy-bank',
});

const form    = ref(defaultForm());
const errors  = ref({});
const loading = ref(false);

const minDate = computed(() => {
  const d = new Date();
  d.setDate(d.getDate() + 1);
  const pad = (v) => String(v).padStart(2, '0');
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
});

watch(() => props.editGoal, (goal) => {
  if (goal) {
    form.value = {
      name: goal.name, description: goal.description ?? '',
      target_amount: goal.target_amount, saved_amount: goal.saved_amount,
      deadline: goal.deadline?.split('T')[0] ?? '',
      currency: goal.currency ?? 'USD',
      color: goal.color ?? '#6366f1',
      icon: goal.icon ?? 'piggy-bank',
    };
  } else {
    form.value = defaultForm();
  }
  errors.value = {};
}, { immediate: true });

async function handleSubmit() {
  loading.value = true;
  errors.value  = {};
  try {
    if (isEditing.value) {
      await goalsApi.update(props.editGoal.id, form.value);
    } else {
      await goalsApi.store(form.value);
    }
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

const currencies = [
  { code: 'USD', label: 'USD' },
  { code: 'KHR', label: 'Riel' },
];
const palette = [
  '#6366f1', '#8b5cf6', '#ec4899', '#ef4444',
  '#f97316', '#eab308', '#10b981', '#06b6d4',
];
const iconOptions = [
  'piggy-bank', 'home', 'car', 'vacation', 'education',
  'emergency', 'wedding', 'gadget', 'health', 'investment',
];
</script>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: all 0.25s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
</style>
