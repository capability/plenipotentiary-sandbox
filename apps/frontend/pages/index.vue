<template>
  <div>
    <div id="heroCarousel" class="carousel slide mb-4" data-bs-ride="carousel" data-aos="fade">
      <div class="carousel-inner rounded-4 shadow-sm">
        <div class="carousel-item active">
          <img class="d-block w-100" src="https://picsum.photos/1200/320?1" alt="">
          <div class="carousel-caption text-start">
            <h5>Find the right parts</h5>
            <p>Search eBay listings by make/model/year.</p>
          </div>
        </div>
        <div class="carousel-item">
          <img class="d-block w-100" src="https://picsum.photos/1200/320?2" alt="">
        </div>
      </div>
    </div>

    <div class="row g-4">
      <div class="col-12 col-md-4">
        <CarPicker @search="doSearch" />
        <div v-if="error" class="alert alert-danger mt-3">{{ error }}</div>
      </div>

      <div class="col-12 col-md-8">
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border" role="status"></div>
        </div>

        <div v-else-if="!items.length" class="alert alert-info">No results.</div>

        <div v-else class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-3">
          <div v-for="it in items" :key="it.id" class="col">
            <ResultCard :item="it" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data: () => ({ items: [], loading: false, error: null }),
  methods: {
    async doSearch(params) {
      this.loading = true; this.error = null
      try {
        const data = await this.$axios.$get('/api/ebay/search', { params })
        this.items = data.items || []
      } catch (e) {
        this.error = e?.response?.data?.message || e.message || 'Search failed'
      } finally {
        this.loading = false
      }
    }
  },
  mounted() {
    this.doSearch({ q: 'brake pads', limit: 50, offset: 0 })
  }
}
</script>

