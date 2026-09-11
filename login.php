<?php
require_once __DIR__ . '/config/database.php';
if (!empty($_SESSION['role'])) {
    redirect($_SESSION['role']==='admin' ? 'controller/c_dashboard.php' : 'controller/c_user_dashboard.php');
}
$error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $login=trim($_POST['login']??''); $password=$_POST['password']??'';
    if ($login==='' || $password==='') $error='Username/NIS dan password wajib diisi.';
    else {
        $st=mysqli_prepare($conn,'SELECT id_admin,username,nama,password FROM admin WHERE username=? LIMIT 1');
        mysqli_stmt_bind_param($st,'s',$login); mysqli_stmt_execute($st); $r=mysqli_stmt_get_result($st); $admin=mysqli_fetch_assoc($r); mysqli_stmt_close($st);
        if ($admin && hash_equals((string)$admin['password'],(string)$password)) {
            session_regenerate_id(true); unset($_SESSION['nis']); $_SESSION['role']='admin'; $_SESSION['id_admin']=(int)$admin['id_admin']; $_SESSION['nama']=$admin['nama'];
            redirect('controller/c_dashboard.php');
        }
        $st=mysqli_prepare($conn,'SELECT nis,nama,kelas,password FROM siswa WHERE nis=? LIMIT 1');
        mysqli_stmt_bind_param($st,'s',$login); mysqli_stmt_execute($st); $r=mysqli_stmt_get_result($st); $siswa=mysqli_fetch_assoc($r); mysqli_stmt_close($st);
        if ($siswa && hash_equals((string)$siswa['password'],(string)$password)) {
            session_regenerate_id(true); unset($_SESSION['id_admin']); $_SESSION['role']='siswa'; $_SESSION['nis']=$siswa['nis']; $_SESSION['nama']=$siswa['nama'];
            redirect('controller/c_user_dashboard.php');
        }
        $error='Akun tidak ditemukan atau password salah.';
    }
}
$justRegistered = !empty($_GET['registered']);
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login - Pengaduan Sarpras</title>
<link rel="stylesheet" href="assets/auth.css">
</head>
<body>
<main class="auth-wrap">
  <section class="auth-card">

    <div class="auth-side">
      <div class="side-top">
        <div class="side-logo">PS</div>
        <h1>Pengaduan Sarana &amp; Prasarana</h1>
        <p>Laporkan kerusakan fasilitas sekolah</p>
      </div>
      <div class="side-features">
        <div class="side-feature">
          <span class="ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4.5 8-11V5l-8-3-8 3v6c0 6.5 8 11 8 11Z"/></svg></span>
          <span>Data aman &amp; login berbasis peran (Admin / Siswa)</span>
        </div>
        <div class="side-feature">
          <span class="ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/></svg></span>
          <span>Status pengaduan real-time: Menunggu, Proses, Selesai</span>
        </div>
        <div class="side-feature">
          <span class="ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20V10M12 20V4M20 20v-7"/></svg></span>
          <span>Riwayat &amp; histori tindak lanjut tercatat rapi</span>
        </div>
      </div>
    </div>

    <div class="auth-form-panel">
      <div class="form-head">
        <h2>Selamat Datang 👋</h2>
        <p>Masuk untuk melanjutkan ke akun kamu. Belum punya akun? <a href="register.php">Daftar sebagai siswa</a></p>
      </div>

      <?php if ($justRegistered): ?>
        <div class="alert success">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4 12 14.01l-3-3"/></svg>
          <span>Registrasi berhasil! Silakan login dengan NIS dan password kamu.</span>
        </div>
      <?php endif; ?>

      <?php if ($error): ?>
        <div class="alert error">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v5M12 16h.01"/></svg>
          <span><?=e($error)?></span>
        </div>
      <?php endif; ?>

      <form method="post" class="auth-form">
        <div class="field">
          <label for="login">Username / NIS</label>
          <div class="input-group">
            <span class="icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span>
            <input id="login" name="login" required autocomplete="username" placeholder="Admin: username · Siswa: NIS">
          </div>
        </div>
        <div class="field">
          <label for="password">Password</label>
          <div class="input-group has-toggle">
            <span class="icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="10" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></span>
            <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="Masukkan password">
            <button type="button" class="toggle-eye" data-target="password" aria-label="Tampilkan password">
              <svg class="eye-on" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
          </div>
        </div>
        <button type="submit" class="btn-primary">Masuk</button>
      </form>
  </section>
</main>

<script>
document.querySelectorAll('.toggle-eye').forEach(function(btn){
  btn.addEventListener('click', function(){
    var input = document.getElementById(btn.getAttribute('data-target'));
    if (!input) return;
    input.type = input.type === 'password' ? 'text' : 'password';
  });
});
</script>
</body>
</html>
