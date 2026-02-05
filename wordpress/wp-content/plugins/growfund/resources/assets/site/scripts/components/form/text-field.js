(function () {
  function dispatchTextField(input) {
    input.dispatchEvent(
      new CustomEvent('growfund:text-field', {
        detail: { name: input.name, value: input.value },
      }),
    );
  }

  function getWrapper(input) {
    return input.closest('.growfund-text-field-wrapper');
  }

  function getClearIcon(input) {
    return getWrapper(input)?.querySelector('.growfund-text-field-clear-icon');
  }

  function handleInput(event) {
    const input = event.target;

    const value = input.value;
    const clearIcon = getClearIcon(input);

    // toggle clear icon
    if (value.trim() !== '') {
      clearIcon?.classList.add('active');
    } else {
      clearIcon?.classList.remove('active');
    }

    dispatchTextField(input);
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.growfund-text-field').forEach((input) => {
      input.addEventListener('input', handleInput);
      input.addEventListener('change', handleInput);
    });

    document.querySelectorAll('.growfund-text-field-clear-icon').forEach((clearIcon) => {
      clearIcon.addEventListener('click', function () {
        const wrapper = clearIcon.closest('.growfund-text-field-wrapper');
        const input = wrapper?.querySelector('.growfund-text-field');

        if (!input) return;

        input.value = '';
        clearIcon.classList.remove('active');
        input.focus();

        dispatchTextField(input);
      });
    });
  });
})();
