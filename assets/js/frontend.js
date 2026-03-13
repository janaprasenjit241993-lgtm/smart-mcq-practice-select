(function () {
  function renderChapterDropdown(chapters) {
    const select = document.querySelector('#mcq-chapter-select');
    if (!select) {
      return;
    }

    select.innerHTML = '';

    const defaultOption = document.createElement('option');
    defaultOption.value = '';
    defaultOption.textContent = 'সব অধ্যায়';
    select.appendChild(defaultOption);

    const seen = new Set();
    const orderedUnique = [];

    for (const chapter of chapters || []) {
      const value = String(chapter || '').trim();
      if (!value || seen.has(value)) {
        continue;
      }
      seen.add(value);
      orderedUnique.push(value);
    }

    for (const chapter of orderedUnique) {
      const option = document.createElement('option');
      option.value = chapter;
      option.textContent = chapter;
      select.appendChild(option);
    }
  }

  window.smartMcqRenderChapterDropdown = renderChapterDropdown;
})();
