<template>
  <div class="card shadow-sm" data-aos="fade-up">
    <div class="card-body">
      <h6 class="card-title text-uppercase text-muted mb-3">Search</h6>

      <div class="mb-3">
        <label class="form-label">Part (e.g. brake pads)</label>
        <input v-model="q" type="text" class="form-control" @keyup.enter="emitSearch" />
      </div>

      <div class="row g-3">
        <div class="col-12">
          <label class="form-label">Year</label>
          <select v-model="year" class="form-select">
            <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
          </select>
        </div>
        <div class="col-12">
          <label class="form-label">Make</label>
          <select v-model="make" class="form-select">
            <option v-for="m in makes" :key="m" :value="m">{{ m }}</option>
          </select>
        </div>
        <div class="col-12">
          <label class="form-label">Model</label>
          <select v-model="model" class="form-select">
            <option v-for="m in (models[make] || [])" :key="m" :value="m">{{ m }}</option>
          </select>
        </div>
      </div>

      <button class="btn btn-primary w-100 mt-3" @click="emitSearch">Search</button>
    </div>
  </div>
</template>

<script>
export default {
  data: () => ({
    q: 'brake pads',
    year: 2018,
    make: 'BMW',
    model: '3 Series',
    years: [...Array(30)].map((_, i) => new Date().getFullYear() - i),
    makes: ['Audi','BMW','Ford','Honda','Toyota','Volkswagen'],
    models: { BMW: ['1 Series','2 Series','3 Series','5 Series'] }
  }),
  methods: {
    emitSearch() {
      this.$emit('search', { q: this.q, year: this.year, make: this.make, model: this.model, limit: 50, offset: 0 })
    }
  }
}
</script>

