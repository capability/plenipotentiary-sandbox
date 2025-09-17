<template>
  <div class="min-h-full">
    <!-- Top nav -->
    <nav class="bg-gray-800">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
          <div class="flex items-center">
            <div class="shrink-0">
              <img class="h-8 w-8" src="https://tailwindcss.com/plus-assets/img/logos/mark.svg?color=indigo&shade=500" alt="Your Company" />
            </div>

            <div class="hidden md:block">
              <div class="ml-10 flex items-baseline space-x-4">
                <a
                  v-for="(item, i) in navigation"
                  :key="i"
                  :href="item.href"
                  :class="[item.current ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-white/5 hover:text-white', 'rounded-md px-3 py-2 text-sm font-medium']"
                  :aria-current="item.current ? 'page' : undefined">
                  {{ item.name }}
                </a>
              </div>
            </div>
          </div>

          <!-- Right side -->
          <div class="hidden md:block">
            <div class="ml-4 flex items-center md:ml-6">
              <button
                type="button"
                class="relative rounded-full p-1 text-gray-400 hover:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-gray-800">
                <span class="sr-only">View notifications</span>
                <Icon icon="heroicons-outline:bell" class="w-6 h-6" />
              </button>

              <!-- Profile dropdown -->
              <div class="relative ml-3">
                <button
                  @click="userMenuOpen = !userMenuOpen"
                  class="relative flex max-w-xs items-center rounded-full focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-gray-800">
                  <span class="sr-only">Open user menu</span>
                  <img class="h-8 w-8 rounded-full ring-1 ring-white/10" :src="user.imageUrl" alt="" />
                </button>

                <transition
                  enter-active-class="transition ease-out duration-100"
                  enter-from-class="transform opacity-0 scale-95"
                  enter-to-class="transform opacity-100 scale-100"
                  leave-active-class="transition ease-in duration-75"
                  leave-from-class="transform opacity-100 scale-100"
                  leave-to-class="transform opacity-0 scale-95">
                  <div
                    v-show="userMenuOpen"
                    @click.outside="userMenuOpen = false"
                    class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black/5">
                    <a v-for="(item, i) in userNavigation" :key="i"
                       :href="item.href"
                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                      {{ item.name }}
                    </a>
                  </div>
                </transition>
              </div>
            </div>
          </div>

          <!-- Mobile menu button -->
          <div class="-mr-2 flex md:hidden">
            <button
              @click="mobileOpen = !mobileOpen"
              class="relative inline-flex items-center justify-center rounded-md p-2 text-gray-400 hover:bg-white/5 hover:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
              <span class="sr-only">Open main menu</span>
              <Icon v-if="!mobileOpen" icon="heroicons-outline:bars-3" class="w-6 h-6" />
              <Icon v-else icon="heroicons-outline:x-mark" class="w-6 h-6" />
            </button>
          </div>
        </div>
      </div>

      <!-- Mobile panel -->
      <div class="md:hidden" v-show="mobileOpen">
        <div class="space-y-1 px-2 pt-2 pb-3 sm:px-3">
          <a v-for="(item, i) in navigation" :key="i" :href="item.href"
             :class="[item.current ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-white/5 hover:text-white', 'block rounded-md px-3 py-2 text-base font-medium']">
            {{ item.name }}
          </a>
        </div>
        <div class="border-t border-white/10 pt-4 pb-3">
          <div class="flex items-center px-5">
            <div class="shrink-0">
              <img class="h-10 w-10 rounded-full ring-1 ring-white/10" :src="user.imageUrl" alt="" />
            </div>
            <div class="ml-3">
              <div class="text-base font-medium text-white">{{ user.name }}</div>
              <div class="text-sm font-medium text-gray-400">{{ user.email }}</div>
            </div>
            <button type="button" class="relative ml-auto shrink-0 rounded-full p-1 text-gray-400 hover:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
              <span class="sr-only">View notifications</span>
              <Icon icon="heroicons-outline:bell" class="w-6 h-6" />
            </button>
          </div>
          <div class="mt-3 space-y-1 px-2">
            <a v-for="(item, i) in userNavigation" :key="i" :href="item.href"
               class="block rounded-md px-3 py-2 text-base font-medium text-gray-400 hover:bg-white/5 hover:text-white">
              {{ item.name }}
            </a>
          </div>
        </div>
      </div>
    </nav>

    <!-- Page header -->
    <header class="relative bg-white shadow-sm">
      <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold tracking-tight text-gray-900">{{ pageTitle }}</h1>
      </div>
    </header>

    <!-- Main content -->
    <main>
      <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <!-- Slot your page content here -->



<!-- VEHICLE PICKER (Tailwind-only) -->
<form @submit.prevent="doSearch" class="grid grid-cols-1 gap-4 md:grid-cols-4">
  <!-- Part query -->
  <div class="md:col-span-2">
    <label class="block text-sm font-medium text-gray-700 mb-1">Part (e.g. brake pads)</label>
    <input v-model="q" type="text" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Brake pads, oil filter…" />
  </div>

  <!-- Year -->
  <div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Year</label>
    <select v-model="year" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
      <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
    </select>
  </div>

  <!-- Make -->
  <div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Make</label>
    <select v-model="make" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
      <option v-for="m in makes" :key="m" :value="m">{{ m }}</option>
    </select>
  </div>

  <!-- Model -->
  <div class="md:col-span-2">
    <label class="block text-sm font-medium text-gray-700 mb-1">Model</label>
    <select v-model="model" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
      <option v-for="m in (models[make] || [])" :key="m" :value="m">{{ m }}</option>
    </select>
  </div>

  <!-- Submit -->
  <div class="md:col-span-2 flex items-end">
    <button type="submit" class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-4 py-2 text-white font-medium shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
      Search parts
    </button>
  </div>
</form>








        <nuxt />
      </div>
    </main>
  </div>
</template>

<script>
import { Icon } from '@iconify/vue2'

export default {
  components: { Icon },
  data: () => ({
    mobileOpen: false,
    userMenuOpen: false,
    pageTitle: 'Dashboard',
    user: {
      name: 'Tom Cook',
      email: 'tom@example.com',
      imageUrl: 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?...'
    },
    navigation: [
      { name: 'Dashboard', href: '#', current: true },
      { name: 'Team', href: '#', current: false },
      { name: 'Projects', href: '#', current: false },
      { name: 'Calendar', href: '#', current: false },
      { name: 'Reports', href: '#', current: false },
    ],
    userNavigation: [
      { name: 'Your profile', href: '#' },
      { name: 'Settings', href: '#' },
      { name: 'Sign out', href: '#' },
    ]
  }),
  directives: {
    // click.outside helper
    outside: {
      bind (el, binding, vnode) {
        el.__clickOutside__ = e => { if (!(el === e.target || el.contains(e.target))) binding.value(e) }
        document.addEventListener('click', el.__clickOutside__)
      },
      unbind (el) { document.removeEventListener('click', el.__clickOutside__) }
    }
  }
}
</script>


<script>
export default {
  data() {
    const thisYear = new Date().getFullYear()
    return {
      // picker state
      q: 'brake pads',
      year: thisYear,
      make: 'BMW',
      model: '3 Series',
      years: Array.from({ length: 30 }, (_, i) => thisYear - i),
      makes: ['Audi','BMW','Ford','Honda','Toyota','Volkswagen'],
      models: {
        BMW: ['1 Series','2 Series','3 Series','5 Series'],
        Ford: ['Fiesta','Focus','Mondeo','Kuga'],
        Toyota: ['Corolla','Yaris','RAV4','Auris'],
        Audi: ['A1','A3','A4','Q3','Q5'],
        Volkswagen: ['Golf','Polo','Passat','Tiguan']
      },
      // existing layout state...
      mobileOpen: false,
      userMenuOpen: false,
      pageTitle: 'Dashboard',
      user: { name: 'Tom Cook', email: 'tom@example.com', imageUrl: 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?...' },
      navigation: [
        { name: 'Dashboard', href: '#', current: true },
        { name: 'Team', href: '#', current: false },
        { name: 'Projects', href: '#', current: false },
        { name: 'Calendar', href: '#', current: false },
        { name: 'Reports', href: '#', current: false },
      ],
      userNavigation: [
        { name: 'Your profile', href: '#' },
        { name: 'Settings', href: '#' },
        { name: 'Sign out', href: '#' },
      ]
    }
  },
  methods: {
    // Broadcast a search event app-wide; your page listens and does the API call.
    doSearch() {
      const payload = {
        q: this.q,
        year: this.year,
        make: this.make,
        model: this.model,
        limit: 50,
        offset: 0,
      }
      this.$nuxt.$emit('vehicle:search', payload)
    }
  },
  directives: {
    outside: {
      bind (el, binding) {
        el.__clickOutside__ = e => { if (!(el === e.target || el.contains(e.target))) binding.value(e) }
        document.addEventListener('click', el.__clickOutside__)
      },
      unbind (el) { document.removeEventListener('click', el.__clickOutside__) }
    }
  }
}
</script>
