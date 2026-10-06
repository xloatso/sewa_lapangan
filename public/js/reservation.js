document.querySelectorAll('[data-confirm-delete]').forEach(form => form.addEventListener('submit', event => {
    if (!confirm('Hapus lapangan ini? Tindakan ini tidak dapat dibatalkan.')) event.preventDefault();
}));

const form = document.querySelector('#reservation-form');
if (form) {
    const money = value => new Intl.NumberFormat('id-ID', {style: 'currency', currency: 'IDR', maximumFractionDigits: 0}).format(value);
    const occupied = JSON.parse(document.querySelector('#occupied-data').textContent);
    const clock = JSON.parse(document.querySelector('#clock-data').textContent);
    const start = form.elements.start_time;
    const duration = form.elements.duration_hours;
    const modal = document.querySelector('#confirmation');
    const selected = () => form.querySelector('[name="court_id"]:checked');
    function update() {
        const court = selected();
        const hours = Number(duration.value);
        document.querySelector('#selected-court').textContent = court?.dataset.name || 'Belum ada lapangan dipilih';
        document.querySelector('#summary-total').textContent = money(Number(court?.dataset.price || 0) * hours);
        let available = 0;
        for (const option of start.options) {
            if (!option.value) continue;
            const hour = Number(option.value.slice(0, 2));
            const booked = (occupied[court?.value] || []).some(slot => {
                const existing = Number(slot.start_time.slice(0, 2));
                return hour < existing + Number(slot.duration_hours) && hour + hours > existing;
            });
            const past = form.elements.booking_date.value === clock.date && hour <= clock.hour;
            option.disabled = !court || booked || hour + hours > 22 || past;
            option.textContent = option.value + (booked ? ' · Sudah dipesan' : (past ? ' · Sudah lewat' : (hour + hours > 22 ? ' · Melewati jam tutup' : '')));
            if (!option.disabled) available++;
        }
        if (start.selectedOptions[0]?.disabled) start.value = '';
        document.querySelector('#availability-note').textContent = court ? `${available} pilihan jam tersedia untuk ${court.dataset.name} dengan durasi ${hours} jam.` : 'Pilih lapangan untuk melihat ketersediaan jam.';
    }
    document.querySelectorAll('[data-filter]').forEach(button => button.addEventListener('click', () => {
        document.querySelectorAll('[data-filter]').forEach(item => {
            item.classList.toggle('selected', item === button);
            item.setAttribute('aria-pressed', String(item === button));
        });
        let visible = 0;
        document.querySelectorAll('.court-card').forEach(card => {
            card.hidden = button.dataset.filter !== 'all' && card.dataset.category !== button.dataset.filter;
            if (!card.hidden) visible++;
        });
        document.querySelector('#empty-filter').hidden = visible > 0;
    }));
    form.querySelectorAll('[name="court_id"]').forEach(input => input.addEventListener('change', update));
    duration.addEventListener('change', update);
    function review() {
        if (!selected()) {
            document.querySelector('[data-filter="all"]').click();
            document.querySelector('[name="court_id"]')?.focus();
        }
        if (!form.reportValidity()) return;
        const court = selected();
        if (!court) return;
        const list = document.querySelector('#confirmation-details');
        list.replaceChildren();
        const end = Number(start.value.slice(0, 2)) + Number(duration.value);
        Object.entries({Nama: form.elements.customer_name.value, Telepon: form.elements.customer_phone.value, Lapangan: court.dataset.name, Tanggal: form.elements.booking_date.value, Jadwal: `${start.value}–${String(end).padStart(2, '0')}:00 (${duration.value} jam)`, Total: money(Number(court.dataset.price) * Number(duration.value)), Status: 'Pending — menunggu pembayaran'}).forEach(([label, value]) => {
            const row = document.createElement('div');
            const key = document.createElement('span');
            const text = document.createElement('strong');
            key.textContent = label;
            text.textContent = value;
            row.append(key, text);
            list.append(row);
        });
        modal.showModal();
    }
    document.querySelector('#review-booking').addEventListener('click', review);
    document.querySelector('#close-confirmation').addEventListener('click', () => modal.close());
    let submitting = false;
    form.addEventListener('submit', event => {
        if (!modal.open) { event.preventDefault(); review(); return; }
        if (submitting) { event.preventDefault(); return; }
        submitting = true;
        modal.querySelector('[type="submit"]').disabled = true;
        modal.querySelector('[type="submit"]').textContent = 'Mengirim…';
    });
    update();
}
