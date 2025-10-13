<script setup lang="ts">
import { ref } from 'vue';

interface ToastData {
  message: string;
  icon: string;
  color: string;
}

const isVisible = ref(false);
const toast = ref<ToastData>({ message: '', icon: '', color: '' });
let timeoutId: number | null = null;

/**
 * Shows the toast with dynamic content.
 * @param message - The main success message.
 * @param type - 'cart' or 'favourite' to determine icon and color.
 */
const show = (message: string, type: 'cart' | 'favourite') => {
  if (timeoutId) {
    clearTimeout(timeoutId);
  }

  if (type === 'cart') {
    toast.value = {
      message: message,
      icon: '🛒',
      color: 'bg-green-600',
    };
  } else if (type === 'favourite') {
    toast.value = {
      message: message,
      icon: '❤️',
      color: 'bg-red-600',
    };
  }

  isVisible.value = true;

  // Automatically hide after 3 seconds
  timeoutId = setTimeout(() => {
    isVisible.value = false;
    timeoutId = null;
  }, 3000);
};

defineExpose({ show });
</script>

<template>
  <Transition name="slide-up">
    <div
      v-if="isVisible"
      :class="['fixed bottom-5 right-5 z-[100] p-4 text-white rounded-lg shadow-2xl flex items-center gap-3 min-w-[250px] transition-colors', toast.color]"
      role="alert"
    >
      <span class="text-xl">{{ toast.icon }}</span>
      <p class="text-sm font-semibold">
        {{ toast.message }}
      </p>
    </div>
  </Transition>
</template>

<style scoped>
.slide-up-enter-active,
.slide-up-leave-active {
  transition: all 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

.slide-up-enter-from,
.slide-up-leave-to {
  opacity: 0;
  transform: translateY(100%);
}
</style>
