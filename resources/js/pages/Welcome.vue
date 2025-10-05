<template>
  <div class="min-h-screen bg-gray-50 flex flex-col justify-center items-center p-6 sm:p-10 font-serif">
    
    <div class="max-w-3xl w-full">
      
      <header class="text-center mb-12">
        <div class="flex justify-center mb-4">
          <div class="h-16 w-16 rounded-full flex items-center justify-center shadow-xl" style="background-color: #9A7AA0;">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
          </div>
        </div>
        <h1 class="text-5xl font-light tracking-wider uppercase" style="color: #524057;">
          Chapter Of You
        </h1>
        <p class="text-lg text-gray-500 mt-2 italic">
          Massage & Essential Wellness
        </p>
      </header>

      <main class="bg-white p-10 sm:p-16 rounded-xl shadow-2xl border-t-4 border-primary-accent">
        
        <div class="text-center">
          
          <p class="text-xl font-semibold mb-3" style="color: #9A7AA0;">
            A Moment for Yourself
          </p>

          <h2 class="text-4xl sm:text-5xl font-bold text-gray-900 mb-6 leading-snug">
            We are curating your path to inner peace.
          </h2>
          
          <p class="text-gray-600 text-lg mb-10">
            Launching soon to offer bespoke massage therapies and a curated selection of natural essential oils. Bookings will be effortless.
          </p>
        </div>

        <div class="flex justify-center">
          <form class="w-full max-w-xl">
            <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4 items-center">
              <input 
                type="email" 
                placeholder="Receive an email when our chapter opens"
                class="flex-1 w-full px-5 py-3 border border-gray-300 rounded-full shadow-md text-gray-700 placeholder:text-gray-400 transition duration-300 focus:ring-[#9A7AA0] focus:border-[#9A7AA0]"
                required
              >
              <button 
                type="submit"
                class="w-full sm:w-auto px-8 py-3 text-white font-semibold rounded-full shadow-lg transition duration-300 ease-in-out transform hover:scale-105 hover:opacity-90"
                style="background-color: #9A7AA0;"
              >
                Join the Waitlist
              </button>
            </div>
          </form>
        </div>
      </main>

      <footer class="mt-12 text-center text-gray-500 text-sm">
        <p>&copy; {{ new Date().getFullYear() }} Chapter Of You. All Rights Reserved.</p>
        <p class="mt-1">
          <a href="mailto:contact@chapterofyou.co.uk" class="hover:text-primary-accent transition duration-200">contact@chapterofyou.co.uk</a>
        </p>
      </footer>
      
    </div>
  </div>
</template>

<script setup>
// Minimal script
</script>

<style scoped>
/* Define a CSS variable and a custom class for the border/hover effects
   This helps keep the color consistent in the CSS block */
:root {
  --primary-color: #9A7AA0;
  --dark-primary-color: #8c6e94; /* A slightly darker shade for hover/shadows */
}

/* Custom class for the top card border */
.border-primary-accent {
  border-top-color: var(--primary-color) !important;
}

/* Custom color for the footer link hover */
.hover\:text-primary-accent:hover {
    color: var(--primary-color);
}
</style>