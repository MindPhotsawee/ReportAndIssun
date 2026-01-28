// ✅ วางตรงนี้เลย (บนสุดไฟล์)
if ('scrollRestoration' in history) {
    history.scrollRestoration = 'manual';
}
window.onload = () => window.scrollTo(0, 0);
document.addEventListener('DOMContentLoaded', () => {

    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('sidebarToggle');
    const closeBtn  = document.getElementById('sidebarClose');

    /* ================= Click Sidebar ================= */
    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', e => {
            e.preventDefault();
            sidebar.classList.toggle('show');
        });
    }
    if (closeBtn && sidebar) {
        closeBtn.addEventListener('click', () => sidebar.classList.remove('show'));
    }

   /* ================= STATUS CHANGE (AJAX) ================= */
// วางโค้ดนี้ใน footer.php หรือในไฟล์เดิมที่มี status-select event
document.querySelectorAll('.status-select').forEach(select => {
    select.addEventListener('change', function() {
        const ticketId = this.dataset.ticket;
        const statusId = this.value;
        
        // ดึงค่า office_id และ search จาก URL
        const urlParams = new URLSearchParams(window.location.search);
        const officeId = urlParams.get('office_id') || '';
        const searchQuery = urlParams.get('q') || '';

        // สร้าง FormData
        const formData = new FormData();
        formData.append('ticket_id', ticketId);
        formData.append('status_id', statusId);
        
        if (officeId) {
            formData.append('office_id', officeId);
        }
        
        // 🔥 ส่ง search parameter
        if (searchQuery) {
            formData.append('search', searchQuery);
        }

        // ส่ง AJAX
        fetch('dashboard.php', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // อัปเดต status cards
                document.querySelectorAll('.status-card').forEach(card => {
                    const sid = parseInt(card.dataset.statusId);
                    const countEl = card.querySelector('.count');
                    if (countEl) {
                        countEl.textContent = data.counts[sid] || 0;
                    }
                });

                // แสดง toast/alert (ถ้าต้องการ)
                console.log('Status updated successfully');
            }
        })
        .catch(err => {
            console.error('Error:', err);
            alert('เกิดข้อผิดพลาดในการอัปเดตสถานะ');
        });
    });
});

    /* ================= Description Modal ================= */
    const descriptionModal = document.getElementById('descriptionModal');
    if (descriptionModal) {
        descriptionModal.addEventListener('show.bs.modal', e => {
            const text = e.relatedTarget?.dataset.description || '';
            document.getElementById('descriptionModalBody').textContent = text;
        });
    }

    /* ================= Images Modal ================= */
    document.addEventListener('click', e => {
        const img = e.target.closest('.clickable-image');
        if (!img) return;

        let images = [];
        try { images = JSON.parse(img.dataset.images || '[]'); } 
        catch (e) { return console.error('data-images ไม่ใช่ JSON'); }

        const carouselInner = document.getElementById('carouselInner');
        if (!carouselInner) return;

        carouselInner.innerHTML = images.map((src,i)=>`
            <div class="carousel-item ${i===0?'active':''}">
                <img src="${src}" class="d-block w-100" style="max-height:70vh;object-fit:contain;margin:0 auto;">
            </div>
        `).join('');

        new bootstrap.Modal(document.getElementById('imageModal')).show();
    });
    /* ================= Force scroll to top on page load ================= */
    window.scrollTo({
        top: 0,
        left: 0,
        behavior: 'instant' // เปลี่ยนเป็น 'smooth' ได้
    });

});
