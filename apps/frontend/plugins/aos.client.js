import AOS from 'aos'
import 'aos/dist/aos.css'

export default () => {
  AOS.init({
    duration: 600,
    easing: 'ease-out',
    once: true,            // animate only once
    offset: 80,
  })
}

