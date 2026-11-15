/* El formulario funciona también si el slider no carga: los números son nativos. */
(function () {
  'use strict';
  function initPriceControls() {
    var minField = document.getElementById('precioMin');
    var maxField = document.getElementById('precioMax');
    var range = document.getElementById('rangoPrecio');
    if (!range || !minField || !maxField) return;
    var absoluteMax = Number(range.dataset.max || '100000');

    function values() {
      var low = Number(minField.value);
      var high = Number(maxField.value);
      low = Number.isFinite(low) ? Math.max(0, Math.min(absoluteMax, Math.round(low))) : 0;
      high = Number.isFinite(high) ? Math.max(0, Math.min(absoluteMax, Math.round(high))) : absoluteMax;
      if (low > high) high = low;
      return { from: low, to: high };
    }

    // El control doble está incluido en el proyecto base y no usa Internet.
    if (window.jQuery && jQuery.fn && jQuery.fn.ionRangeSlider) {
      var initial = values();
      jQuery(range).ionRangeSlider({
        type: 'double', min: 0, max: absoluteMax,
        from: initial.from, to: initial.to,
        step: 1, grid: false, hide_min_max: true,
        prettify_enabled: true, prefix: '$',
        onChange: function (data) {
          minField.value = data.from;
          maxField.value = data.to;
        }
      });
      var instance = jQuery(range).data('ionRangeSlider');
      function update() {
        var fixed = values();
        minField.value = fixed.from;
        maxField.value = fixed.to;
        if (instance) instance.update(fixed);
      }
      minField.addEventListener('change', update);
      maxField.addEventListener('change', update);
    } else {
      range.style.display = 'none';
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPriceControls);
  } else {
    initPriceControls();
  }
}());
