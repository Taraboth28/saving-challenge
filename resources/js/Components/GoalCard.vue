<template>
  <div class="glass rounded-2xl p-6 card-hover animate-fade-in">
    <!-- Header -->
    <div class="flex items-start justify-between mb-4">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg"
             :style="{ background: `${goal.color}22`, border: `1px solid ${goal.color}44` }">
          <GoalIcon :name="goal.icon" class-name="w-6 h-6" />
        </div>
        <div>
          <h3 class="font-semibold text-white text-sm leading-tight">{{ goal.name }}</h3>
          <p v-if="goal.description" class="text-xs text-slate-400 mt-0.5 line-clamp-1">{{ goal.description }}</p>
        </div>
      </div>
      <div class="flex items-center gap-1.5">
        <span v-if="goal.is_completed" class="badge badge-emerald">✓ Done</span>
        <span v-else-if="isUrgent" class="badge badge-rose">Urgent</span>
        <span v-else class="badge badge-indigo">Active</span>
      </div>
    </div>

    <!-- Progress bar -->
    <div class="mb-4">
      <div class="flex justify-between items-center mb-1.5">
        <span class="text-xs text-slate-400">Progress</span>
        <span class="text-xs font-bold" :style="{ color: goal.color }">
          {{ goal.percentage }}%
        </span>
      </div>
      <div class="h-2 bg-slate-800 rounded-full overflow-hidden">
        <div class="h-full rounded-full progress-bar-fill"
             :style="{
               width: `${Math.min(goal.percentage, 100)}%`,
               background: `linear-gradient(90deg, ${goal.color}, ${goal.color}cc)`,
               boxShadow: `0 0 8px ${goal.color}55`,
             }"></div>
      </div>
    </div>

    <!-- Amounts -->
    <div class="grid grid-cols-2 gap-3 mb-4">
      <div class="bg-slate-800/50 rounded-xl p-3">
        <p class="text-xs text-slate-400 mb-1">Saved</p>
        <p class="font-bold text-white text-sm">{{ formatCurrency(goal.saved_amount, goal.currency) }}</p>
      </div>
      <div class="bg-slate-800/50 rounded-xl p-3">
        <p class="text-xs text-slate-400 mb-1">Target</p>
        <p class="font-bold text-slate-300 text-sm">{{ formatCurrency(goal.target_amount, goal.currency) }}</p>
      </div>
    </div>

    <!-- Footer info -->
    <div class="flex items-center justify-between text-xs text-slate-500">
      <div class="flex items-center gap-1">
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <span v-if="goal.remaining_days !== null">
          {{ goal.remaining_days > 0 ? `${goal.remaining_days} days left` : 'Deadline passed' }}
        </span>
        <span v-else>No deadline</span>
      </div>
      <div class="flex items-center gap-1.5">
        <!-- Add saving -->
        <button @click.stop="$emit('save', goal)"
                class="p-1.5 rounded-lg hover:bg-emerald-500/15 hover:text-emerald-400 transition-colors"
                title="Add Saving">
          <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
          </svg>
        </button>
        <!-- View -->
        <router-link :to="`/goals/${goal.id}`"
                     class="p-1.5 rounded-lg hover:bg-indigo-500/15 hover:text-indigo-400 transition-colors"
                     title="View Details">
          <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
          </svg>
        </router-link>
        <!-- Edit -->
        <button @click.stop="$emit('edit', goal)"
                class="p-1.5 rounded-lg hover:bg-amber-500/15 hover:text-amber-400 transition-colors"
                title="Edit">
          <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
          </svg>
        </button>
        <!-- Delete -->
        <button @click.stop="$emit('delete', goal)"
                class="p-1.5 rounded-lg hover:bg-red-500/15 hover:text-red-400 transition-colors"
                title="Delete">
          <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import GoalIcon from '@/Components/GoalIcon.vue';

const props = defineProps({
  goal: { type: Object, required: true },
});

defineEmits(['edit', 'delete', 'save']);

const isUrgent = computed(() =>
  props.goal.remaining_days !== null && props.goal.remaining_days <= 30 && !props.goal.is_completed
);

function formatCurrency(amount, currency = 'USD') {
  return new Intl.NumberFormat('en-US', {
    style: 'currency', currency: currency ?? 'USD', minimumFractionDigits: 0, maximumFractionDigits: 0,
  }).format(amount ?? 0);
}
</script>
