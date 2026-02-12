// Toggle forms
const btnLogin = document.getElementById('btnLogin');
const btnRegister = document.getElementById('btnRegister');
const loginForm = document.getElementById('loginForm');
const registerForm = document.getElementById('registerForm');
const openRegister = document.getElementById('openRegister');

function showLogin() {
  btnLogin.classList.add('active'); 
  btnRegister.classList.remove('active');
  loginForm.classList.remove('d-none'); 
  registerForm.classList.add('d-none');
}

function showRegister() {
  btnRegister.classList.add('active'); 
  btnLogin.classList.remove('active');
  registerForm.classList.remove('d-none'); 
  loginForm.classList.add('d-none');
}

btnLogin.addEventListener('click', showLogin);
btnRegister.addEventListener('click', showRegister);
openRegister.addEventListener('click', (e)=>{
  e.preventDefault(); 
  showRegister(); 
  window.scrollTo({top:0, behavior:'smooth'});
});

// Login submit
loginForm.addEventListener('submit', async (e)=>{
  e.preventDefault();
  const user = document.getElementById('loginUser').value.trim();
  const pass = document.getElementById('loginPass').value;
  if(!user || !pass){ alert('กรุณากรอกข้อมูลให้ครบ'); return }

  try{
    const res = await fetch('login.php', {
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body: JSON.stringify({user, pass})
    });
    const data = await res.json();
    if(data.success){
  if(data.role === 'staff' || data.role === 'admin'){
    window.location.href = 'dashboard.php';
  } else {
    window.location.href = 'list_repair.php';
  }
  } else {
      alert(data.message || 'เข้าสู่ระบบไม่สำเร็จ');
    }
  } catch(err){
    console.error(err);
    alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
  }
});
// Register submit
registerForm.addEventListener('submit', async (e) => {
  e.preventDefault();
  const name = document.getElementById('regName').value.trim();
  const email = document.getElementById('regEmail').value.trim();
  const pass = document.getElementById('regPass').value;
  const pass2 = document.getElementById('regPass2').value;
  const role = document.getElementById('regRole').value;
  const office = document.getElementById('regOffice').value;
  const phone = document.getElementById('regPhone').value.trim();
  const line = document.getElementById('lineId').value.trim();

  if(!name || !email || !pass || !pass2 || !role || !office || !phone){
      alert('กรุณากรอกข้อมูลให้ครบ');
      return;
  }
  if(pass !== pass2){
      alert('รหัสผ่านไม่ตรงกัน');
      return;
  }

  try {
      const formData = new FormData();
      formData.append('name', name);
      formData.append('email', email);
      formData.append('pass', pass);
      formData.append('role', role);
      formData.append('office_id', office); // ต้องตรงกับ PHP
      formData.append('phone', phone);
      formData.append('line', line);

      const res = await fetch('register.php', {
          method: 'POST',
          body: formData
      });

      const data = await res.json();
      if(data.success){
          alert('ลงทะเบียนสำเร็จ! กรุณาเข้าสู่ระบบ');
          showLogin();
      } else {
          alert(data.message || 'ลงทะเบียนไม่สำเร็จ');
      }
  } catch(err){
      console.error(err);
      alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
  }
});

