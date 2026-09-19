import { useEffect } from 'react';

declare global {
  interface Window {
    particlesJS: any;
  }
}

export default function ParticlesBackground() {
  useEffect(() => {
    const initParticles = () => {
      if (typeof window !== 'undefined' && window.particlesJS) {
        window.particlesJS('particles-js', {
          particles: {
            number: { value: 50, density: { enable: true, value_area: 850 } },
            color: { value: ['#06b6d4', '#0ea5e9', '#3b82f6', '#10b981'] },
            shape: { type: 'circle' },
            opacity: { value: 0.28, random: true },
            size: { value: 2.5, random: true },
            line_linked: {
              enable: true,
              distance: 140,
              color: '#06b6d4',
              opacity: 0.16,
              width: 1
            },
            move: {
              enable: true,
              speed: 1.1,
              direction: 'none',
              random: false,
              straight: false,
              out_mode: 'out',
              bounce: false
            }
          },
          interactivity: {
            detect_on: 'canvas',
            events: {
              onhover: { enable: true, mode: 'grab' },
              onclick: { enable: true, mode: 'push' },
              resize: true
            },
            modes: {
              grab: { distance: 140, line_linked: { opacity: 0.45 } },
              push: { particles_nb: 3 }
            }
          },
          retina_detect: true
        });
      }
    };

    const timer = setTimeout(initParticles, 150);
    return () => clearTimeout(timer);
  }, []);

  return (
    <div
      id="particles-js"
      className="fixed inset-0 pointer-events-none z-0"
      style={{ width: '100%', height: '100%' }}
    />
  );
}
