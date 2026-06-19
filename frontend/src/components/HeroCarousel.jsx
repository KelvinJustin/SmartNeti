import { useEffect, useRef, useState } from 'react';

export default function HeroCarousel({ slides, interval = 4000 }) {
  const containerRef = useRef(null);
  const [current, setCurrent] = useState(0);
  const timerRef = useRef(null);
  const isDraggingRef = useRef(false);
  const startXRef = useRef(0);
  const scrollLeftRef = useRef(0);

  const goTo = (index) => {
    const el = containerRef.current;
    if (!el || !slides.length) return;
    const safeIndex = ((index % slides.length) + slides.length) % slides.length;
    const slide = el.children[safeIndex];
    if (slide) {
      el.scrollTo({ left: slide.offsetLeft, behavior: 'smooth' });
    }
    setCurrent(safeIndex);
  };

  const next = () => goTo(current + 1);

  const startTimer = () => {
    if (timerRef.current) clearInterval(timerRef.current);
    timerRef.current = setInterval(() => {
      setCurrent((prev) => {
        const nextIndex = (prev + 1) % slides.length;
        const el = containerRef.current;
        if (el) {
          const slide = el.children[nextIndex];
          if (slide) el.scrollTo({ left: slide.offsetLeft, behavior: 'smooth' });
        }
        return nextIndex;
      });
    }, interval);
  };

  const stopTimer = () => {
    if (timerRef.current) clearInterval(timerRef.current);
  };

  useEffect(() => {
    startTimer();
    return () => stopTimer();
  }, [slides.length, interval]);

  const handlePointerDown = (e) => {
    isDraggingRef.current = true;
    startXRef.current = e.clientX || e.touches?.[0]?.clientX;
    scrollLeftRef.current = containerRef.current.scrollLeft;
    stopTimer();
  };

  const handlePointerMove = (e) => {
    if (!isDraggingRef.current) return;
    const x = e.clientX || e.touches?.[0]?.clientX;
    const walk = (startXRef.current - x) * 1.5;
    containerRef.current.scrollLeft = scrollLeftRef.current + walk;
  };

  const handlePointerUp = () => {
    if (!isDraggingRef.current) return;
    isDraggingRef.current = false;
    const el = containerRef.current;
    const slideWidth = el.children[0]?.offsetWidth || 0;
    const newIndex = Math.round(el.scrollLeft / slideWidth);
    goTo(newIndex);
    startTimer();
  };

  return (
    <div
      className="relative w-full"
      onMouseEnter={stopTimer}
      onMouseLeave={startTimer}
      onTouchStart={stopTimer}
      onTouchEnd={startTimer}
    >
      <div
        ref={containerRef}
        className="flex overflow-x-auto no-scrollbar snap-x snap-mandatory scroll-smooth cursor-grab active:cursor-grabbing select-none"
        onMouseDown={handlePointerDown}
        onMouseMove={handlePointerMove}
        onMouseUp={handlePointerUp}
        onMouseLeave={() => {
          handlePointerUp();
          startTimer();
        }}
        onTouchStart={handlePointerDown}
        onTouchMove={handlePointerMove}
        onTouchEnd={handlePointerUp}
      >
        {slides.map((slide, i) => (
          <div
            key={i}
            className="snap-center shrink-0 w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 overflow-hidden shadow-sm"
          >
            {slide.image && (
              <div className="h-32 bg-slate-100 dark:bg-slate-800 overflow-hidden">
                <img
                  src={slide.image}
                  alt={slide.title}
                  className="w-full h-full object-cover"
                  draggable={false}
                />
              </div>
            )}
            <div className="p-4">
              <h3 className="font-semibold text-slate-900 dark:text-slate-100 text-sm">
                {slide.title}
              </h3>
              <p className="text-xs text-slate-500 dark:text-slate-400 mt-1 line-clamp-2">
                {slide.subtitle}
              </p>
            </div>
          </div>
        ))}
      </div>

      {/* Dots */}
      <div className="flex justify-center gap-1.5 mt-3">
        {slides.map((_, i) => (
          <button
            key={i}
            onClick={() => goTo(i)}
            className={`w-2 h-2 rounded-full transition-colors ${
              i === current
                ? 'bg-brand-600 dark:bg-brand-400'
                : 'bg-slate-300 dark:bg-slate-700'
            }`}
            aria-label={`Go to slide ${i + 1}`}
          />
        ))}
      </div>
    </div>
  );
}
