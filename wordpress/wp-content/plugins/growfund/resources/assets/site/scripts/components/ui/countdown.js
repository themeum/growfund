(function () {
  document.addEventListener('DOMContentLoaded', function () {
    const countdowns = document.querySelectorAll('.growfund-countdown');

    countdowns.forEach((countdown) => {
      const endDate = countdown.getAttribute('data-end-date');
      if (!endDate) return;

      const endTime = new Date(endDate).getTime();
      if (isNaN(endTime)) return;

      const numbers = {
        days: countdown.querySelector('[data-unit="days"]'),
        hours: countdown.querySelector('[data-unit="hours"]'),
        minutes: countdown.querySelector('[data-unit="minutes"]'),
        seconds: countdown.querySelector('[data-unit="seconds"]'),
      };

      function updateCountdown() {
        const now = Date.now();
        let diff = endTime - now;

        if (diff <= 0) {
          diff = 0;
          clearInterval(timer);
        }

        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diff / (1000 * 60 * 60)) % 24);
        const minutes = Math.floor((diff / (1000 * 60)) % 60);
        const seconds = Math.floor((diff / 1000) % 60);

        numbers.days.textContent = days.toString().padStart(2, '0');
        numbers.hours.textContent = hours.toString().padStart(2, '0');
        numbers.minutes.textContent = minutes.toString().padStart(2, '0');
        numbers.seconds.textContent = seconds.toString().padStart(2, '0');
      }

      updateCountdown();
      const timer = setInterval(updateCountdown, 1000);
    });
  });
})();
