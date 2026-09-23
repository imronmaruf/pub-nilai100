document.querySelectorAll("form[data-confirm]").forEach((form) => {
  form.addEventListener("submit", (event) => {
    if (!window.confirm(form.dataset.confirm)) event.preventDefault();
  });
});
document.querySelectorAll("[data-student-picker]").forEach((picker) => {
  const form = picker.closest("form");
  const search = picker.querySelector("#student_search");
  const select = picker.querySelector("#student_id");
  const fieldset = form.querySelector("[data-publication-fields]");
  const message = form.querySelector("[data-eligibility-message]");
  const status = picker.querySelector("[data-search-status]");
  let rows = new Map(),
    timer,
    controller,
    sequence = 0;
  function eligible(allowed) {
    if (fieldset) fieldset.disabled = !allowed;
    if (message)
      message.textContent = allowed
        ? "Testimoni SUDAH. Publikasi dapat diisi."
        : "Form terkunci: pilih siswa dengan Nilai 100 dan testimoni SUDAH.";
  }
  function display(row) {
    picker.querySelectorAll("[data-display]").forEach((input) => {
      input.value = row?.[input.dataset.display] ?? "";
    });
    eligible(row?.eligible === true);
  }
  eligible(picker.dataset.eligible === "1");
  select?.addEventListener("change", () => display(rows.get(select.value)));
  search?.addEventListener("input", () => {
    clearTimeout(timer);
    controller?.abort();
    const current = ++sequence;
    select.replaceChildren(new Option("Pilih hasil pencarian", ""));
    rows.clear();
    display(null);
    const q = search.value.trim();
    if (q.length < 2) {
      status.textContent = "";
      return;
    }
    status.textContent = "Mencari…";
    timer = setTimeout(async () => {
      controller = new AbortController();
      try {
        const url = new URL(picker.dataset.url, window.location.origin);
        url.searchParams.set("q", q);
        const response = await fetch(url, {
          headers: { Accept: "application/json" },
          signal: controller.signal,
        });
        if (!response.ok) throw new Error("Lookup failed");
        const data = await response.json();
        if (current !== sequence) return;
        data.forEach((row) => {
          rows.set(String(row.id), row);
          select.add(new Option(`${row.noreg} — ${row.nama_siswa}`, row.id));
        });
        status.textContent = data.length
          ? `${data.length} hasil. Pilih siswa.`
          : "Siswa tidak ditemukan.";
      } catch (error) {
        if (error.name !== "AbortError" && current === sequence)
          status.textContent = "Pencarian gagal. Coba lagi atau masuk ulang.";
      }
    }, 250);
  });
});
document.querySelectorAll("[data-ig-status]").forEach((select) => {
  const form = select.closest("form");
  const update = () => {
    const draft = select.value === "BELUM";
    ["tanggal_posting", "link_postingan"].forEach((name) => {
      const input = form.elements.namedItem(name);
      input.disabled = draft;
      input.required = !draft;
      if (draft) input.value = "";
    });
    ["jumlah_postingan", "view", "like", "komen"].forEach((name) => {
      const input = form.elements.namedItem(name);
      input.readOnly = draft;
      if (draft) input.value = "0";
      input.min = name === "jumlah_postingan" && !draft ? "1" : "0";
      if (!draft && name === "jumlah_postingan" && input.value === "0")
        input.value = "1";
    });
  };
  select.addEventListener("change", update);
  update();
});
const sidebar = document.querySelector("[data-sidebar]");
document
  .querySelector("[data-menu-toggle]")
  ?.addEventListener("click", () => sidebar?.classList.toggle("open"));
document.querySelectorAll("table[data-table]").forEach((table) => {
  table.querySelectorAll("thead th").forEach((header, index) => {
    header.classList.add("cursor-pointer");
    header.setAttribute("title", "Urutkan kolom");
    header.addEventListener("click", () => {
      const body = table.tBodies[0];
      const rows = [...body.rows];
      const direction = header.dataset.direction === "asc" ? -1 : 1;
      table
        .querySelectorAll("thead th")
        .forEach((cell) => delete cell.dataset.direction);
      header.dataset.direction = direction === 1 ? "asc" : "desc";
      rows.sort((left, right) => {
        const a = left.cells[index]?.textContent.trim() ?? "";
        const b = right.cells[index]?.textContent.trim() ?? "";
        const numericA = Number(a.replaceAll(".", "").replace(",", "."));
        const numericB = Number(b.replaceAll(".", "").replace(",", "."));
        if (!Number.isNaN(numericA) && !Number.isNaN(numericB))
          return (numericA - numericB) * direction;
        return a.localeCompare(b, "id", { numeric: true }) * direction;
      });
      rows.forEach((row) => body.appendChild(row));
    });
  });
});
if (window.jQuery && window.jQuery.fn.select2) {
  window.jQuery(".filter-panel select").select2({
    width: "100%",
    minimumResultsForSearch: 0,
    placeholder: "Pilih atau ketik untuk mencari",
    allowClear: true,
  });
}
