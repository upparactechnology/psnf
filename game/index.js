  (function () {
  'use strict';

  document.addEventListener("DOMContentLoaded", () => {
    // Bind card navigation
    const gameCards = document.querySelectorAll(".game-card");
    gameCards.forEach((card) => {
      card.addEventListener("click", () => {
        const target = card.dataset.gameTarget;
        if (target) {
          window.location.href = target;
        }
      });
    });

    // Reset button
    const homeResetBtn = document.getElementById("homeResetBtn");
    if (homeResetBtn) {
      homeResetBtn.addEventListener("click", () => {
        window.location.reload();
      });
    }
  });
})();
