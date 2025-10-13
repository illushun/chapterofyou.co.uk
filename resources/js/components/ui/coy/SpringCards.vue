<script setup lang="ts">
import Card from './Card.vue';
</script>

<template>
  <section class="bg-white px-8 py-24">
    <div class="mx-auto grid max-w-3xl grid-cols-1 gap-6 sm:grid-cols-2">
      <Card
        title="Dynamic"
        subtitle="Lorem ipsum dolor sit, amet consectetur adipisicing elit. Exercitationem doloremque vitae minima."
      />
      <Card
        title="Data Driven"
        subtitle="Lorem ipsum dolor sit, amet consectetur adipisicing elit. Exercitationem doloremque vitae minima."
        class="bg-indigo-300 sm:-translate-y-6"
      />
      <Card
        title="Dutiful"
        subtitle="Lorem ipsum dolor sit, amet consectetur adipisicing elit. Exercitationem doloremque vitae minima."
        class="bg-red-300"
      />
      <Card
        title="Demure"
        subtitle="Lorem ipsum dolor sit, amet consectetur adipisicing elit. Exercitationem doloremque vitae minima."
        class="bg-yellow-300 sm:-translate-y-6"
      />
    </div>
  </section>
</template>
