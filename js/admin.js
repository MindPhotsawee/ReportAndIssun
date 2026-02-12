const form = document.getElementById('adminRegisterForm');

if(form){
  form.addEventListener('submit', async (e)=>{
    e.preventDefault();

    const formData = new FormData(form);

    const res = await fetch('register.php', {
      method: 'POST',
      body: formData
    });

    const data = await res.json();
    if(data.success){
      alert('เพิ่มผู้ใช้สำเร็จ');
      form.reset();
    } else {
      alert(data.message || 'เพิ่มผู้ใช้ไม่สำเร็จ');
    }
  });
}
