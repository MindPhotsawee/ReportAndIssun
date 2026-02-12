
            // ฟังก์ชันเปิดกล้อง / แกลลอรี่
            function getActiveInput(){
                const userInput = document.getElementById("fileInputUser");
                const staffInput = document.getElementById("fileInputStaff");

                // ฟอร์มที่ถูก render คือฟอร์มที่มี input อยู่
                if (userInput) return userInput;
                if (staffInput) return staffInput;

                return null;
            }


////////////////////////////////////////////////////////////////////////////////////////////////
            // เลือกพื้นที่ preview ให้ตรงกับฟอร์มที่ใช้งาน
           function getPreviewArea(input) {
                if(input.id === 'fileInputUser') return document.getElementById('previewUser');
                if(input.id === 'fileInputStaff') return document.getElementById('previewStaff');
                return document.getElementById('previewUser'); // fallback
            }
////////////////////////////////////////////////////////////////////////////////////////////
    // ฟังก์ชันรวมไฟล์เก่า + ไฟล์ใหม่จากกล้อง
        function mergeFiles(oldFiles, newFiles) {
            // รวมไฟล์เก่า + ใหม่
            let merged = [...oldFiles, ...newFiles];

            // จำกัด 5 รูปเท่านั้น
            if (merged.length > 5) {
                merged = merged.slice(0, 5);
                alert("สามารถเลือกได้สูงสุด 5 รูปเท่านั้น");
            }

            // คืนค่า FileList ใหม่
            const dt = new DataTransfer();
            merged.forEach(f => dt.items.add(f));
            return dt.files;
        }

/////////////////////////////////////////////////////////////////////////////////////////
        function renderPreview(input) {
    const preview = getPreviewArea(input);
    preview.innerHTML = "";

    const files = input.savedFiles || [];

    files.forEach((file, realIndex) => {
        const reader = new FileReader();
        reader.onload = e => {

            // wrapper
            const wrapper = document.createElement("div");
            wrapper.className = "img-wrapper m-1";

            // รูปภาพ
            const img = document.createElement("img");
            img.src = e.target.result;

            // ปุ่มลบ
            const btn = document.createElement("button");
            btn.type = "button";
            btn.className = "img-remove-btn";
            btn.innerHTML = "×";

            // เมื่อกดลบ
            btn.addEventListener("click", () => {
                const dt = new DataTransfer();

                files.forEach((f, i) => {
                    if (i !== realIndex) dt.items.add(f);
                });

                input.files = dt.files;
                input.savedFiles = [...dt.files];
                renderPreview(input);
            });

            wrapper.appendChild(img);
            wrapper.appendChild(btn);
            preview.appendChild(wrapper);
        };
        reader.readAsDataURL(file);
    });
}

        const userInput = document.getElementById("fileInputUser");
        const staffInput = document.getElementById("fileInputStaff");

        [userInput, staffInput].forEach(input => {
            if(!input) return;

            input.addEventListener("change", function () {

            if (this.files.length === 0) return;

            const oldFiles = this.savedFiles ? [...this.savedFiles] : [];
            const newFiles = [...this.files];

            // รวมไฟล์เก่า + ใหม่
            let merged = [...oldFiles, ...newFiles];

            // จำกัดไม่เกิน 5 รูป
            if (merged.length > 5) {
                merged = merged.slice(0, 5);
                alert("เลือกได้สูงสุด 5 รูปเท่านั้น");
            }

            // สร้าง FileList ใหม่
            const dt = new DataTransfer();
            merged.forEach(f => dt.items.add(f));

            this.files = dt.files;
            this.savedFiles = [...dt.files];

            renderPreview(this);
        });

        });

        // ฟังก์ชันเปิดกล้อง
        function openCamera() {
            const input = getActiveInput();
            input.setAttribute("capture", "environment"); // เปิดกล้องหลัง
            input.click();
        }

        // ฟังก์ชันเปิดคลังภาพ
        function openGallery() {
            const input = getActiveInput();
            input.removeAttribute("capture"); // เอา capture ออกเพื่อเปิดคลังภาพ
            input.click();
        }