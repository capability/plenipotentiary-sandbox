import { shallowMount } from '@vue/test-utils'

import Hello from '@/components/Hello.vue'

describe('Hello', () => {
  it('renders prop', () => {
    const w = shallowMount(Hello, { propsData: { name: 'Sam' } })
    expect(w.text()).toContain('Hello Sam')
  })
})