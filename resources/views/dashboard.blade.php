<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ParkirPintar</title>
    <style>
        :root {
            --bg: #f3f4f6; --card: #ffffff; --text: #1f2937; --muted: #6b7280;
            --primary: #2563eb; --ok: #16a34a; --low: #d97706; --full: #dc2626; --border: #e5e7eb;
        }
        * { box-sizing: border-box; }
        [hidden] { display: none !important; }
        body { margin: 0; font-family: system-ui, Arial, sans-serif; background: var(--bg); color: var(--text); line-height: 1.5; }

        header { background: var(--primary); color: #fff; padding: 16px 24px 0; }
        .header-top { display: flex; justify-content: space-between; align-items: baseline; flex-wrap: wrap; gap: 8px; margin-bottom: 12px; }
        header h1 { margin: 0; font-size: 1.25rem; }
        header small { opacity: .85; }
        .tabs { display: flex; gap: 4px; flex-wrap: wrap; }
        .tab { background: transparent; color: #dbeafe; border: 0; border-radius: 8px 8px 0 0; padding: 10px 16px; font: inherit; font-size: .95rem; cursor: pointer; }
        .tab:hover { background: rgba(255, 255, 255, .12); }
        .tab[aria-current="page"] { background: var(--bg); color: var(--text); font-weight: 600; }

        main { max-width: 1000px; margin: 24px auto; padding: 0 16px 48px; }
        h2 { font-size: 1.1rem; margin: 0 0 12px; }
        .block { margin-bottom: 24px; }

        .card { background: var(--card); border: 1px solid var(--border); border-radius: 10px; padding: 16px; }
        .summary { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; }
        .stat-label { color: var(--muted); font-size: .85rem; }
        .stat-value { font-size: 1.8rem; font-weight: 700; margin-top: 4px; }
        .stat-value.small { font-size: 1.1rem; }

        .areas { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 12px; }
        .area-name { font-weight: 700; font-size: 1.05rem; margin-bottom: 12px; }
        .slot { margin-bottom: 12px; }
        .slot-top { display: flex; justify-content: space-between; font-size: .9rem; margin-bottom: 4px; }
        .bar { height: 8px; background: var(--border); border-radius: 999px; overflow: hidden; }
        .bar > div { height: 100%; background: var(--ok); }
        .bar.low > div { background: var(--low); }
        .bar.full > div { background: var(--full); }
        .badge-full { color: var(--full); font-weight: 700; }
        .toolbar { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; color: var(--muted); font-size: .85rem; }
        .empty { color: var(--muted); text-align: center; padding: 24px; }

        .btn { background: var(--primary); color: #fff; border: 0; border-radius: 6px; padding: 8px 14px; cursor: pointer; font: inherit; font-size: .9rem; }
        .btn:hover { opacity: .9; }
        .btn:disabled { opacity: .5; cursor: not-allowed; }
        .btn-secondary { background: #e5e7eb; color: var(--text); }
        .btn-danger { background: var(--full); }
        .btn-small { padding: 4px 10px; font-size: .8rem; }

        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 12px; }
        .field label { display: block; font-size: .85rem; margin-bottom: 4px; }
        .field input, .field select { width: 100%; padding: 8px 10px; border: 1px solid #d1d5db; border-radius: 6px; font: inherit; background: #fff; color: var(--text); }
        .field-error { color: var(--full); font-size: .8rem; margin: 4px 0 0; }
        .actions { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 14px; }
        .two-col { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 12px; }
        .spaced { margin-top: 12px; }
        .muted { color: var(--muted); font-size: .85rem; }
        .active-head { display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px; margin-bottom: 12px; }
        .active-head h2 { margin-bottom: 2px; }
        .active-tools { display: flex; gap: 8px; flex-wrap: wrap; }
        .search { padding: 8px 10px; border: 1px solid #d1d5db; border-radius: 6px; font: inherit; min-width: 200px; background: #fff; color: var(--text); }

        .message { border-radius: 8px; padding: 10px 14px; margin-bottom: 12px; font-size: .9rem; }
        .message.success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .message.error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 10px 12px; border-bottom: 1px solid var(--border); font-size: .9rem; }
        th { color: var(--muted); font-weight: 600; }
        .row-actions { display: flex; gap: 6px; }

        a:focus-visible, button:focus-visible, input:focus-visible, select:focus-visible {
            outline: 3px solid #93c5fd; outline-offset: 2px;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-top">
            <h1>ParkirPintar</h1>
            <small>Pengelolaan parkir gedung perkantoran</small>
        </div>
        <nav class="tabs" aria-label="Menu utama">
            <button type="button" class="tab" data-view="availability">Ketersediaan</button>
            <button type="button" class="tab" data-view="areas">Area parkir</button>
            <button type="button" class="tab" data-view="parking">Check-in dan check-out</button>
            <button type="button" class="tab" data-view="revenue">Pendapatan</button>
        </nav>
    </header>

    <main>
        <!-- Ketersediaan -->
        <section id="view-availability">
            <div id="availability-message" class="message error" role="alert" hidden></div>

            <div class="block">
                <h2>Ringkasan</h2>
                <div class="summary">
                    <div class="card">
                        <div class="stat-label">Total sisa slot motor</div>
                        <div class="stat-value" id="total-motor">-</div>
                    </div>
                    <div class="card">
                        <div class="stat-label">Total sisa slot mobil</div>
                        <div class="stat-value" id="total-mobil">-</div>
                    </div>
                    <div class="card">
                        <div class="stat-label">Jumlah area</div>
                        <div class="stat-value" id="total-area">-</div>
                    </div>
                </div>
            </div>

            <div class="block">
                <h2>Ketersediaan per area</h2>
                <div class="toolbar">
                    <button id="btn-refresh" type="button" class="btn">Muat ulang</button>
                    <span id="last-updated"></span>
                </div>
                <div id="areas" class="areas"></div>
            </div>
        </section>

        <!-- Area parkir (CRUD) -->
        <section id="view-areas" hidden>
            <div id="areas-message" class="message" role="status" hidden></div>

            <div class="block card">
                <h2 id="area-form-title">Tambah area</h2>
                <form id="area-form" novalidate>
                    <div class="form-grid">
                        <div class="field">
                            <label for="area-name">Nama area</label>
                            <input id="area-name" type="text" maxlength="255" autocomplete="off">
                            <p class="field-error" data-error-for="name"></p>
                        </div>
                        <div class="field">
                            <label for="capacity-motor">Kapasitas motor</label>
                            <input id="capacity-motor" type="number" min="0" max="10000" step="1">
                            <p class="field-error" data-error-for="capacity_motor"></p>
                        </div>
                        <div class="field">
                            <label for="capacity-mobil">Kapasitas mobil</label>
                            <input id="capacity-mobil" type="number" min="0" max="10000" step="1">
                            <p class="field-error" data-error-for="capacity_mobil"></p>
                        </div>
                    </div>
                    <div class="actions">
                        <button type="submit" class="btn">Simpan area</button>
                        <button type="button" id="area-cancel" class="btn btn-secondary" hidden>Batal ubah</button>
                    </div>
                </form>
            </div>

            <div class="block card">
                <h2>Daftar area</h2>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Nama area</th>
                                <th>Kapasitas motor</th>
                                <th>Kapasitas mobil</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="area-rows"></tbody>
                    </table>
                </div>
                <p id="area-empty" class="empty" hidden>Belum ada area parkir. Tambahkan area pertama lewat formulir di atas.</p>
            </div>
        </section>

        <!-- Check-in dan check-out -->
        <section id="view-parking" hidden>
            <div class="two-col">
                <div class="card">
                    <h2>Check-in</h2>
                    <div id="checkin-message" class="message" role="status" hidden></div>
                    <form id="checkin-form" novalidate>
                        <div class="field">
                            <label for="checkin-plate">Plat nomor</label>
                            <input id="checkin-plate" type="text" maxlength="20" placeholder="B 1234 XYZ" autocomplete="off">
                            <p class="field-error" data-error-for="plate_number"></p>
                        </div>
                        <div class="field" style="margin-top:12px">
                            <label for="checkin-type">Jenis kendaraan</label>
                            <select id="checkin-type">
                                <option value="">Pilih jenis</option>
                                <option value="motor">Motor</option>
                                <option value="mobil">Mobil</option>
                            </select>
                            <p class="field-error" data-error-for="vehicle_type"></p>
                        </div>
                        <div class="field" style="margin-top:12px">
                            <label for="checkin-area">Area parkir</label>
                            <select id="checkin-area"></select>
                            <p class="field-error" data-error-for="parking_area_id"></p>
                        </div>
                        <div class="actions">
                            <button type="submit" class="btn">Catat check-in</button>
                        </div>
                    </form>
                </div>

                <div class="card">
                    <h2>Check-out</h2>
                    <div id="checkout-message" class="message" role="status" hidden></div>
                    <form id="checkout-form" novalidate>
                        <div class="field">
                            <label for="checkout-plate">Plat nomor</label>
                            <input id="checkout-plate" type="text" maxlength="20" placeholder="B 1234 XYZ" autocomplete="off">
                            <p class="field-error" data-error-for="plate_number"></p>
                        </div>
                        <div class="actions">
                            <button type="submit" class="btn">Catat check-out</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card spaced">
                <div class="active-head">
                    <div>
                        <h2>Kendaraan yang sedang parkir</h2>
                        <span id="active-count" class="muted"></span>
                    </div>
                    <div class="active-tools">
                        <input id="active-search" class="search" type="search" placeholder="Cari plat nomor" aria-label="Cari plat nomor">
                        <button type="button" id="btn-active-refresh" class="btn btn-secondary">Muat ulang daftar</button>
                    </div>
                </div>
                <div id="active-message" class="message error" role="alert" hidden></div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Plat nomor</th>
                                <th>Jenis</th>
                                <th>Area</th>
                                <th>Masuk</th>
                                <th>Durasi</th>
                                <th>Perkiraan tarif</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="active-rows"></tbody>
                    </table>
                </div>
                <p id="active-empty" class="empty" hidden></p>
            </div>
        </section>

        <!-- Pendapatan -->
        <section id="view-revenue" hidden>
            <div id="revenue-message" class="message error" role="alert" hidden></div>

            <div class="block card">
                <h2>Pendapatan per hari</h2>
                <div class="form-grid">
                    <div class="field">
                        <label for="revenue-date">Tanggal</label>
                        <input id="revenue-date" type="date">
                    </div>
                </div>
                <div class="actions">
                    <button type="button" id="btn-revenue" class="btn">Tampilkan pendapatan</button>
                </div>
            </div>

            <div class="block">
                <h2 id="rev-date-label">Pendapatan</h2>
                <div class="summary">
                    <div class="card">
                        <div class="stat-label">Total pendapatan</div>
                        <div class="stat-value" id="rev-total">-</div>
                    </div>
                    <div class="card">
                        <div class="stat-label">Jumlah transaksi</div>
                        <div class="stat-value" id="rev-count">-</div>
                    </div>
                    <div class="card">
                        <div class="stat-label">Motor</div>
                        <div class="stat-value small" id="rev-motor">-</div>
                    </div>
                    <div class="card">
                        <div class="stat-label">Mobil</div>
                        <div class="stat-value small" id="rev-mobil">-</div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script>
        const API = '/api';
        const VIEWS = ['availability', 'areas', 'parking', 'revenue'];
        const state = { areas: [], editingId: null, active: [] };
        const $ = (id) => document.getElementById(id);

        /* ---------- Util ---------- */

        async function api(path, options = {}) {
            const response = await fetch(API + path, {
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
                ...options,
            });
            const body = await response.json().catch(() => ({}));

            if (!response.ok) {
                const error = new Error(body.message || 'Terjadi kesalahan pada server.');
                error.status = response.status;
                error.errors = body.errors || null;
                throw error;
            }
            return body;
        }

        function setMessage(id, text, kind) {
            const box = $(id);
            box.textContent = text || '';
            box.className = 'message ' + (kind || 'success');
            box.hidden = !text;
        }

        function clearErrors(form) {
            form.querySelectorAll('[data-error-for]').forEach((el) => { el.textContent = ''; });
        }

        function showFieldErrors(form, errors) {
            Object.entries(errors || {}).forEach(([field, messages]) => {
                const el = form.querySelector('[data-error-for="' + field + '"]');
                if (el) el.textContent = messages[0];
            });
        }

        async function handleForm(form, messageId, action) {
            const submit = form.querySelector('button[type="submit"]');
            clearErrors(form);
            setMessage(messageId, '');
            submit.disabled = true;
            try {
                await action();
            } catch (error) {
                if (error.status === 422) {
                    showFieldErrors(form, error.errors);
                    setMessage(messageId, 'Periksa kembali isian Anda.', 'error');
                } else {
                    setMessage(messageId, error.message, 'error');
                }
            } finally {
                submit.disabled = false;
            }
        }

        function numberOrNull(value) {
            return value === '' ? null : Number(value);
        }

        function rupiah(value) {
            return 'Rp ' + Number(value).toLocaleString('id-ID');
        }

        function formatTime(iso) {
            return new Date(iso).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', timeZone: 'Asia/Jakarta' });
        }

        function formatDuration(totalMinutes) {
            const hours = Math.floor(totalMinutes / 60);
            const minutes = totalMinutes % 60;
            if (hours === 0) return minutes + ' menit';
            return minutes === 0 ? hours + ' jam' : hours + ' jam ' + minutes + ' menit';
        }

        function makeButton(label, className, onClick, ariaLabel) {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = className;
            button.textContent = label;
            if (ariaLabel) button.setAttribute('aria-label', ariaLabel);
            button.addEventListener('click', onClick);
            return button;
        }

        /* ---------- Ketersediaan ---------- */

        function slotRow(label, slot) {
            const row = document.createElement('div');
            row.className = 'slot';

            const top = document.createElement('div');
            top.className = 'slot-top';

            const name = document.createElement('span');
            name.textContent = label;

            const value = document.createElement('strong');
            if (slot.capacity === 0) {
                value.textContent = 'Tidak tersedia';
            } else if (slot.available === 0) {
                value.textContent = 'Penuh';
                value.className = 'badge-full';
            } else {
                value.textContent = 'Sisa ' + slot.available + ' dari ' + slot.capacity;
            }
            top.append(name, value);

            const bar = document.createElement('div');
            bar.className = 'bar';
            if (slot.capacity > 0 && slot.available === 0) {
                bar.classList.add('full');
            } else if (slot.capacity > 0 && slot.available / slot.capacity <= 0.25) {
                bar.classList.add('low');
            }
            const fill = document.createElement('div');
            fill.style.width = Math.min(100, slot.capacity > 0 ? (slot.occupied / slot.capacity) * 100 : 0) + '%';
            bar.append(fill);

            row.append(top, bar);
            return row;
        }

        function renderOccupancy(areas) {
            const container = $('areas');
            container.replaceChildren();

            if (areas.length === 0) {
                const empty = document.createElement('div');
                empty.className = 'empty card';
                empty.textContent = 'Belum ada area parkir. Tambahkan lewat menu Area parkir.';
                container.append(empty);
            }

            areas.forEach((area) => {
                const card = document.createElement('div');
                card.className = 'card';
                const title = document.createElement('div');
                title.className = 'area-name';
                title.textContent = area.name;
                card.append(title, slotRow('Motor', area.motor), slotRow('Mobil', area.mobil));
                container.append(card);
            });

            const sum = (type) => areas.reduce((total, area) => total + area[type].available, 0);
            $('total-motor').textContent = sum('motor');
            $('total-mobil').textContent = sum('mobil');
            $('total-area').textContent = areas.length;
        }

        async function loadOccupancy() {
            try {
                const result = await api('/reports/occupancy');
                renderOccupancy(result.data);
                setMessage('availability-message', '');
                $('last-updated').textContent = 'Terakhir diperbarui: ' + new Date().toLocaleTimeString('id-ID');
            } catch (error) {
                setMessage('availability-message', 'Gagal memuat data: ' + error.message, 'error');
            }
        }

        /* ---------- Area parkir (CRUD) ---------- */

        async function loadAreas() {
            try {
                const result = await api('/areas');
                state.areas = result.data;
                renderAreaTable();
                renderAreaSelect();
            } catch (error) {
                setMessage('areas-message', 'Gagal memuat area: ' + error.message, 'error');
            }
        }

        function renderAreaTable() {
            const body = $('area-rows');
            body.replaceChildren();
            $('area-empty').hidden = state.areas.length > 0;

            state.areas.forEach((area) => {
                const row = document.createElement('tr');
                [area.name, area.capacity_motor, area.capacity_mobil].forEach((value) => {
                    const cell = document.createElement('td');
                    cell.textContent = value;
                    row.append(cell);
                });

                const actions = document.createElement('td');
                actions.className = 'row-actions';
                actions.append(
                    makeButton('Ubah', 'btn btn-secondary btn-small', () => startEdit(area), 'Ubah area ' + area.name),
                    makeButton('Hapus', 'btn btn-danger btn-small', () => deleteArea(area), 'Hapus area ' + area.name)
                );
                row.append(actions);
                body.append(row);
            });
        }

        function renderAreaSelect() {
            const select = $('checkin-area');
            const current = select.value;
            select.replaceChildren();
            select.append(new Option(state.areas.length ? 'Pilih area' : 'Belum ada area parkir', ''));
            state.areas.forEach((area) => select.append(new Option(area.name, String(area.id))));
            if (Array.from(select.options).some((option) => option.value === current)) {
                select.value = current;
            }
        }

        function startEdit(area) {
            state.editingId = area.id;
            $('area-name').value = area.name;
            $('capacity-motor').value = area.capacity_motor;
            $('capacity-mobil').value = area.capacity_mobil;
            $('area-form-title').textContent = 'Ubah area: ' + area.name;
            $('area-cancel').hidden = false;
            clearErrors($('area-form'));
            setMessage('areas-message', '');
            $('area-form').scrollIntoView({ block: 'center' });
            $('area-name').focus();
        }

        function resetAreaForm() {
            state.editingId = null;
            $('area-form').reset();
            $('area-form-title').textContent = 'Tambah area';
            $('area-cancel').hidden = true;
            clearErrors($('area-form'));
        }

        async function deleteArea(area) {
            if (!confirm('Hapus area "' + area.name + '"? Tindakan ini tidak bisa dibatalkan.')) return;

            try {
                const result = await api('/areas/' + area.id, { method: 'DELETE' });
                if (state.editingId === area.id) resetAreaForm();
                setMessage('areas-message', result.message, 'success');
                await loadAreas();
            } catch (error) {
                setMessage('areas-message', error.message, 'error');
            }
        }

        $('area-form').addEventListener('submit', (event) => {
            event.preventDefault();
            handleForm(event.currentTarget, 'areas-message', async () => {
                const payload = {
                    name: $('area-name').value.trim(),
                    capacity_motor: numberOrNull($('capacity-motor').value),
                    capacity_mobil: numberOrNull($('capacity-mobil').value),
                };
                const editing = state.editingId !== null;
                const result = await api(editing ? '/areas/' + state.editingId : '/areas', {
                    method: editing ? 'PUT' : 'POST',
                    body: JSON.stringify(payload),
                });
                resetAreaForm();
                setMessage('areas-message', result.message, 'success');
                await loadAreas();
            });
        });

        $('area-cancel').addEventListener('click', resetAreaForm);

        /* ---------- Check-in dan check-out ---------- */

        $('checkin-form').addEventListener('submit', (event) => {
            event.preventDefault();
            handleForm(event.currentTarget, 'checkin-message', async () => {
                const result = await api('/check-in', {
                    method: 'POST',
                    body: JSON.stringify({
                        plate_number: $('checkin-plate').value.trim(),
                        vehicle_type: $('checkin-type').value,
                        parking_area_id: numberOrNull($('checkin-area').value),
                    }),
                });
                const area = state.areas.find((item) => item.id === result.data.parking_area_id);
                setMessage(
                    'checkin-message',
                    result.message + ' ' + result.data.plate_number + ' masuk ke ' + (area ? area.name : 'area parkir') + '.',
                    'success'
                );
                $('checkin-plate').value = '';
                await loadActive();
            });
        });

        async function doCheckout(plate) {
            const result = await api('/check-out', {
                method: 'POST',
                body: JSON.stringify({ plate_number: plate }),
            });
            const record = result.data;
            const minutes = Math.round((new Date(record.check_out_at) - new Date(record.check_in_at)) / 60000);
            setMessage(
                'checkout-message',
                result.message + ' ' + record.plate_number + ': masuk ' + formatTime(record.check_in_at) +
                    ', keluar ' + formatTime(record.check_out_at) + ' (' + formatDuration(minutes) + '). Tarif: ' + rupiah(record.fee) + '.',
                'success'
            );
            await loadActive();
        }

        $('checkout-form').addEventListener('submit', (event) => {
            event.preventDefault();
            handleForm(event.currentTarget, 'checkout-message', async () => {
                await doCheckout($('checkout-plate').value.trim());
                $('checkout-plate').value = '';
            });
        });

        /* ---------- Kendaraan yang sedang parkir ---------- */

        async function loadActive() {
            try {
                const result = await api('/parking/active');
                state.active = result.data;
                renderActive();
                setMessage('active-message', '');
            } catch (error) {
                setMessage('active-message', 'Gagal memuat daftar kendaraan: ' + error.message, 'error');
            }
        }

        function renderActive() {
            const keyword = $('active-search').value.toUpperCase().replace(/\s+/g, '');
            const list = state.active.filter((vehicle) => vehicle.plate_number.replace(/\s+/g, '').includes(keyword));
            const body = $('active-rows');
            body.replaceChildren();

            $('active-count').textContent = state.active.length + ' kendaraan sedang parkir';
            $('active-empty').hidden = list.length > 0;
            $('active-empty').textContent = state.active.length === 0
                ? 'Belum ada kendaraan yang sedang parkir. Kendaraan yang check-in akan muncul di sini.'
                : 'Tidak ada kendaraan dengan plat yang cocok.';

            list.forEach((vehicle) => {
                const row = document.createElement('tr');
                [
                    vehicle.plate_number,
                    vehicle.vehicle_type === 'motor' ? 'Motor' : 'Mobil',
                    vehicle.area_name,
                    formatTime(vehicle.check_in_at),
                    formatDuration(vehicle.duration_minutes),
                    rupiah(vehicle.estimated_fee),
                ].forEach((value) => {
                    const cell = document.createElement('td');
                    cell.textContent = value;
                    row.append(cell);
                });

                const actions = document.createElement('td');
                actions.append(
                    makeButton('Check-out', 'btn btn-small', () => checkoutFromList(vehicle), 'Check-out ' + vehicle.plate_number)
                );
                row.append(actions);
                body.append(row);
            });
        }

        async function checkoutFromList(vehicle) {
            const question = 'Check-out ' + vehicle.plate_number + '? Perkiraan tarif saat ini ' + rupiah(vehicle.estimated_fee) + '.';
            if (!confirm(question)) return;

            setMessage('checkout-message', '');
            try {
                await doCheckout(vehicle.plate_number);
            } catch (error) {
                setMessage('checkout-message', error.message, 'error');
            }
        }

        $('active-search').addEventListener('input', renderActive);
        $('btn-active-refresh').addEventListener('click', loadActive);

        /* ---------- Pendapatan ---------- */

        async function loadRevenue() {
            const date = $('revenue-date').value;
            const query = date ? '?date=' + encodeURIComponent(date) : '';

            try {
                const result = await api('/reports/revenue' + query);
                const data = result.data;
                const motor = data.by_vehicle_type.motor;
                const mobil = data.by_vehicle_type.mobil;

                $('rev-date-label').textContent = 'Pendapatan ' +
                    new Date(data.date + 'T00:00:00').toLocaleDateString('id-ID', { dateStyle: 'long' });
                $('rev-total').textContent = rupiah(data.total_revenue);
                $('rev-count').textContent = data.total_transactions;
                $('rev-motor').textContent = rupiah(motor.revenue) + ' (' + motor.transactions + ' transaksi)';
                $('rev-mobil').textContent = rupiah(mobil.revenue) + ' (' + mobil.transactions + ' transaksi)';
                setMessage('revenue-message', '');
            } catch (error) {
                setMessage(
                    'revenue-message',
                    error.status === 422 ? 'Tanggal tidak valid. Pilih tanggal dari kalender.' : 'Gagal memuat pendapatan: ' + error.message,
                    'error'
                );
            }
        }

        /* ---------- Navigasi ---------- */

        function showView(name) {
            VIEWS.forEach((view) => {
                $('view-' + view).hidden = view !== name;
                document.querySelector('[data-view="' + view + '"]').setAttribute('aria-current', view === name ? 'page' : 'false');
            });

            if (name === 'availability') loadOccupancy();
            if (name === 'areas' || name === 'parking') loadAreas();
            if (name === 'parking') loadActive();
            if (name === 'revenue') loadRevenue();
        }

        document.querySelectorAll('.tab').forEach((tab) => {
            tab.addEventListener('click', () => showView(tab.dataset.view));
        });
        $('btn-refresh').addEventListener('click', loadOccupancy);
        $('btn-revenue').addEventListener('click', loadRevenue);

        $('revenue-date').value = new Date().toLocaleDateString('en-CA');
        showView('availability');
        setInterval(() => {
            if (!$('view-availability').hidden) loadOccupancy();
        }, 15000);
        setInterval(() => {
            if (!$('view-parking').hidden) loadActive();
        }, 30000);
    </script>
</body>
</html>
