;(function() {
    'use strict';
    let accordions = document.getElementsByClassName("accordion");

    // make accordions to save space and slide options in and out as needed
    for (let i = 0; i < accordions.length; i++) {
      accordions[i].addEventListener("click", function() {
        this.classList.toggle("active");
        let panel = this.nextElementSibling;
        if (panel.style.maxHeight) {
          panel.style.maxHeight = null;
        } else {
          panel.style.maxHeight = panel.scrollHeight + "px";
        }
      });
    }
}());
